<?php
/**
 * Load a JSON file, strip BOM, and remove trailing commas before } or ].
 * Returns [array|null $data, string $errorMsg]
 */
function load_json_file_clean(string $path): array {
    if (!is_file($path)) return [null, "File not found: $path"];
    $raw = file_get_contents($path);

    // If file is empty, json_decode => syntax error
    if ($raw === '' || $raw === false) return [null, "File is empty or unreadable"];

    // Strip UTF-8 BOM if present
    $raw = preg_replace('/^\xEF\xBB\xBF/', '', $raw);

    // Remove trailing commas before } or ]
    // e.g., "Value": 123, }  or  "x", ]
    $raw = preg_replace('/,\s*(?=[}\]])/', '', $raw);

    // Decode
    $data = json_decode($raw, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        // Provide a short preview to help locate issues
        $preview = substr($raw, 0, 300);
        return [null, 'Invalid JSON: ' . json_last_error_msg() . " (showing first 300 bytes)\n" . $preview];
    }
    return [$data, ""];
}


// Polyfill for PHP < 8.1
if (!function_exists('array_is_list')) {
    function array_is_list(array $array): bool {
        $i = 0; foreach ($array as $k => $_) if ($k !== $i++) return false;
        return true;
    }
}

/**
 * Entry point: return a rootless object (top-level starts at ProviderID, etc.)
 * If multiple documents exist, you can pass a different $docIndex.
 */
function simplifyAbbbyExportRootless(array $data, int $docIndex = 0): array {
    // Find the DocumentData node
    if (isset($data['Documents'][$docIndex]['DocumentData']) && is_array($data['Documents'][$docIndex]['DocumentData'])) {
        $docData = $data['Documents'][$docIndex]['DocumentData'];
    } elseif (isset($data['DocumentData']) && is_array($data['DocumentData'])) {
        $docData = $data['DocumentData'];
    } else {
        return []; // Unexpected structure
    }

    // IMPORTANT: drop the outer "Name" wrapper at the very top
    return simplifyAbbbyNode($docData, /*wrapWithName*/ false);
}

/**
 * Simplifies a single ABBYY export node (field or group) into a plain, JSON-ready structure.
 *
 * Transformation rules:
 *  1) Simple field:
 *     { "Name": "...", "Value": <scalar|null>, "$type": "..." }
 *       → ["<Name>" => <Value>]
 *
 *  2) Group with Items:
 *     { "Name": "...", "Items": [ { "Fields": [...] }, { "Fields": [...] }, ... ] }
 *       → One object per Items[] element (built from its Fields[]), returned as:
 *          - an array of row objects when there are multiple items, e.g.
 *              "<Name>": [ { ...row1 }, { ...row2 }, ... ]
 *          - a single object when there is exactly one item, e.g.
 *              "<Name>": { ...row }
 *
 *  3) Node with Fields (no Items):
 *       → Merges simplified Fields[] into a single object.
 *
 *  4) Wrapping:
 *     When $wrapWithName = true and the node has a "Name", the result is wrapped under that key.
 *     When false (typically at the root), the result is returned bare (no outer "Name" object).
 *
 *  5) Duplicate keys:
 *     If multiple fields with the same Name occur within the same object scope, duplicates are
 *     preserved by promoting the value to a list and appending (via mergeAssoc()).
 *     Example: "Description" appearing twice → "Description": ["foo", "bar"].
 *
 * Notes:
 *  - Non-array inputs are returned as-is.
 *  - To force groups with a single item to still return an array, change the
 *    `(count($rows) === 1) ? $rows[0] : $rows` behavior to always return `$rows`.
 *
 * @param array|scalar $node          An ABBYY node: either a field/group array or a scalar; scalars pass through.
 * @param bool         $wrapWithName  Whether to wrap the result under node["Name"] if present (default true).
 *                                    Pass false at the very top to drop the outer "DocumentData" name.
 *
 * @return array|scalar               A simplified, associative structure suitable for json_encode().
 *
 * @see mergeAssoc()                  Handles duplicate-key promotion to lists.
 * @example
 *   Input:
 *     {
 *       "Name": "LineItemCharge",
 *       "Items": [
 *         { "Fields": [
 *             { "Name": "Description", "Value": "Balance Forward" },
 *             { "Name": "Amount",      "Value": 584.47 }
 *           ]
 *         },
 *         { "Fields": [
 *             { "Name": "Description", "Value": "Current Charges" },
 *             { "Name": "Amount",      "Value": 46.35 }
 *           ]
 *         }
 *       ]
 *     }
 *
 *   Output (with $wrapWithName = true):
 *     {
 *       "LineItemCharge": [
 *         { "Description": "Balance Forward", "Amount": 584.47 },
 *         { "Description": "Current Charges", "Amount": 46.35 }
 *       ]
 *     }
 */
function simplifyAbbbyNode($node, bool $wrapWithName = true) {
    if (!is_array($node)) return $node;

    // Simple field { Name, Value, $type? }
    if (isset($node['Name']) && array_key_exists('Value', $node)) {
        return [$node['Name'] => $node['Value']];
    }

    // Treat Items as rows: build one object per item
    if (isset($node['Items']) && is_array($node['Items'])) {
        $rows = [];
        foreach ($node['Items'] as $item) {
            if (isset($item['Fields']) && is_array($item['Fields'])) {
                $obj = [];
                foreach ($item['Fields'] as $field) {
                    // simplify field -> ["Name" => value], then merge into row
                    $obj = mergeAssoc($obj, simplifyAbbbyNode($field, true));
                }
                $rows[] = $obj;
            } else {
                // Nested groups inside Items
                $rows[] = simplifyAbbbyNode($item, true);
            }
        }
        // If there's only one item, return that object; otherwise the array of rows
        $result = (count($rows) === 1) ? $rows[0] : $rows;
        return ($wrapWithName && isset($node['Name'])) ? [$node['Name'] => $result] : $result;
    }

    // Node with Fields directly
    if (isset($node['Fields']) && is_array($node['Fields'])) {
        $obj = [];
        foreach ($node['Fields'] as $field) {
            $obj = mergeAssoc($obj, simplifyAbbbyNode($field, true));
        }
        return ($wrapWithName && isset($node['Name'])) ? [$node['Name'] => $obj] : $obj;
    }

    // Generic list fallback
    if (function_exists('array_is_list') ? array_is_list($node) : array_keys($node) === range(0, count($node)-1)) {
        $out = [];
        foreach ($node as $el) $out[] = simplifyAbbbyNode($el, true);
        return $out;
    }

    return $node;
}

/** Merge helper that preserves duplicates as arrays */
function mergeAssoc(array $a, $b): array {
    if (!is_array($b)) return $a;
    foreach ($b as $k => $v) {
        if (!array_key_exists($k, $a)) {
            $a[$k] = $v;
        } else {
            // Promote to list and append
            if (!is_array($a[$k]) || array_is_list($a[$k])) {
                $a[$k] = is_array($a[$k]) && array_is_list($a[$k]) ? $a[$k] : [$a[$k]];
            }
            $a[$k][] = $v;
        }
    }
    return $a;
}

//-------------------------------------------------------------------------------------------------------------------
// Utility functions
//-------------------------------------------------------------------------------------------------------------------
// Safe getter (returns null if key missing, even when value is null)
function pick(?array $a, string $k, $default = null) {
    return (is_array($a) && array_key_exists($k, $a)) ? $a[$k] : $default;
}

// Normalize a node to a list: object -> [object], list -> list, null -> []
function listify($node): array {
    if ($node === null) return [];
    if (is_array($node)) {
        $isList = function_exists('array_is_list') ? array_is_list($node)
                : (array_keys($node) === range(0, count($node)-1));
        return $isList ? $node : [$node];
    }
    return [$node];
}


// HTML escaping and rendering helpers

function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function kv(string $label, $value): string {
    if ($value === null || $value === '') return '';
    return "<dt>".h($label)."</dt><dd>".h($value)."</dd>";
}
function fmt($v): string {
    if ($v === null || $v === '') return '—';
    if (is_bool($v)) return $v ? 'true' : 'false';
    return h($v);
}

/**
 * Render a table for an array of line-item objects/arrays.
 * - Auto-picks columns with any non-empty data.
 * - Optional validation controls per row: IsNeg / IsSum / IsInfo radios, MatchedID text.
 *   Controls are namespaced with $contextKey + row index to keep names unique.
 *
 * @param array       $rows            list of objects/assoc-arrays
 * @param array       $preferredCols   label=>prop map (or a simple list of props)
 * @param string|null $caption         optional caption/title
 * @param bool        $withControls    when true, append control columns
 * @param string      $contextKey      namespace for input names/ids, e.g. "cg-1"
 */
function renderItemsTable(
    array $rows,
    array $preferredCols = [],
    ?string $caption = null,
    bool $withControls = false,
    string $contextKey = 'ctx'
): string {
    if (empty($rows)) return '';

    // default logical column set
    if (empty($preferredCols)) {
        $preferredCols = [
            'Description'          => 'Description',
            'Amount'               => 'Amount',
            //'Is Negative'          => 'IsNegative',
            'Equation'             => 'Equation',
            //'Tier'                 => 'Tier',
            'Service Period'       => 'ServicePeriod',
            //'Service Period Text'  => 'ServicePeriodText',
            'Service Start At'     => 'ServiceStartAt',
            'Service End At'       => 'ServiceEndAt',
        ];
    } elseif (array_is_list($preferredCols)) {
        $preferredCols = array_combine($preferredCols, $preferredCols);
    }

    // choose columns that have any data
    $cols = [];
    foreach ($preferredCols as $label => $prop) {
        foreach ($rows as $r) {
            $val = is_array($r) ? ($r[$prop] ?? null) : ($r->$prop ?? null);
            if ($val !== null && $val !== '') { $cols[$label] = $prop; break; }
        }
    }
    if (empty($cols)) $cols = $preferredCols;

    $html = '<div class="table-wrap">';
    if ($caption) $html .= '<div class="table-caption">'.h($caption).'</div>';
    $html .= '<table class="items-table"><thead><tr>';

    foreach ($cols as $label => $_) $html .= '<th>'.h($label).'</th>';

    // control headers
    if ($withControls) {
        $html .= '<th>IsNeg</th><th>IsSum</th><th>IsInfo</th><th>MatchedID</th>';
    }

    $html .= '</tr></thead><tbody>';

    foreach ($rows as $i => $r) {
        $html .= '<tr>';

        // data cells
        foreach ($cols as $prop) {
            $val = is_array($r) ? ($r[$prop] ?? null) : ($r->$prop ?? null);
            $cell = ($prop === 'Amount' && is_numeric($val))
                  ? number_format((float)$val, 2)
                  : fmt($val);
            $html .= '<td>'.$cell.'</td>';
        }

        // control cells (checkboxes + text)
        if ($withControls) {
            $isNeg  = is_array($r) ? ($r['IsNegative'] ?? $r['IsNeg'] ?? null) : ($r->IsNegative ?? $r->IsNeg ?? null);
            $isSum  = is_array($r) ? ($r['IsSummary']  ?? $r['IsSum'] ?? null) : ($r->IsSummary  ?? $r->IsSum ?? null);
            $isInfo = is_array($r) ? ($r['IsInformational'] ?? $r['IsInfo'] ?? null) : ($r->IsInformational ?? $r->IsInfo ?? null);
            $match  = is_array($r) ? ($r['MatchedID'] ?? '') : ($r->MatchedID ?? '');

            $nameBase = $contextKey.'['.$i.']';

            // IsNeg checkbox (+ hidden 0 so POST always has a value)
            $id = $contextKey.'-'.$i.'-isNeg';
            $checked = !empty($isNeg) ? 'checked' : '';
            $html .= '<td class="controls">';
            $html .= '<input type="hidden" name="'.$nameBase.'[IsNeg]" value="0">';
            $html .= '<input type="checkbox" id="'.$id.'" name="'.$nameBase.'[IsNeg]" value="1" '.$checked.'>';
            $html .= '<label class="sr-only" for="'.$id.'">IsNeg</label>';
            $html .= '</td>';

            // IsSum checkbox
            $id = $contextKey.'-'.$i.'-isSum';
            $checked = !empty($isSum) ? 'checked' : '';
            $html .= '<td class="controls">';
            $html .= '<input type="hidden" name="'.$nameBase.'[IsSum]" value="0">';
            $html .= '<input type="checkbox" id="'.$id.'" name="'.$nameBase.'[IsSum]" value="1" '.$checked.'>';
            $html .= '<label class="sr-only" for="'.$id.'">IsSum</label>';
            $html .= '</td>';

            // IsInfo checkbox
            $id = $contextKey.'-'.$i.'-isInfo';
            $checked = !empty($isInfo) ? 'checked' : '';
            $html .= '<td class="controls">';
            $html .= '<input type="hidden" name="'.$nameBase.'[IsInfo]" value="0">';
            $html .= '<input type="checkbox" id="'.$id.'" name="'.$nameBase.'[IsInfo]" value="1" '.$checked.'>';
            $html .= '<label class="sr-only" for="'.$id.'">IsInfo</label>';
            $html .= '</td>';

            // MatchedID text
            $midId = $contextKey.'-'.$i.'-matched';
            $html .= '<td class="controls">';
            $html .= '<label class="sr-only" for="'.$midId.'">MatchedID</label>';
            $html .= '<input type="text" id="'.$midId.'" name="'.$nameBase.'[MatchedID]" class="matched-input" value="'.h($match).'">';
            $html .= '</td>';
        }

        $html .= '</tr>';
    }

    $html .= '</tbody></table></div>';
    return $html;
}















































































?>