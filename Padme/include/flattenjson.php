<?php
/**
 * 1) Decode lenient JSON (reuse your existing decode_lenient_json() from earlier)
 *    — or swap in json_decode($raw, true) if you trust input.
 */

/**
 * 2) Flatten ABBYY-style JSON:
 *    Walk nested {Group -> Items -> Fields[]} and emit rows, preserving a breadcrumb “path”.
 *    A “row” = ['path' => 'AccountInvoice/ChargeGroup/LineItemCharge', 'fields' => ['Description'=>'x','Amount'=>42.86,...]]
 */
function flatten_abbyy(array $root): array
{
    $rows = [];
    // Most ABBYY exports hang off: Version, Documents[...]{ DocumentData{ Fields[...] } }
    if (!isset($root['Documents']) || !is_array($root['Documents'])) {
        return $rows;
    }

    foreach ($root['Documents'] as $doc) {
        if (empty($doc['DocumentData']['Fields'])) continue;
        foreach ($doc['DocumentData']['Fields'] as $field) {
            // Top-level fields may include Groups (by $type === 'Group')
            flatten_field($field, [], $rows);
        }
    }
    return $rows;
}

function flatten_field(array $node, array $path, array &$rows): void
{
    // Field schema: {"Name": "...", "$type": "Text|Group|Currency|DateTime|Checkmark|Number", "Value": ..., "Items":[...], "Fields":[...] }
    $name = $node['Name'] ?? 'UNKNOWN';
    $type = $node['$type'] ?? 'UNKNOWN';
    $currPath = array_merge($path, [$name]);

    if (strcasecmp($type, 'Group') === 0) {
        // A Group can have Items[], each item may have Fields[]
        if (!empty($node['Items']) && is_array($node['Items'])) {
            foreach ($node['Items'] as $item) {
                // Each item is usually {"Fields":[...]} or nested Groups again
                if (!empty($item['Fields']) && is_array($item['Fields'])) {
                    // Build a “row” from this item: collect its Fields as name=>value
                    $fieldsAssoc = [];
                    foreach ($item['Fields'] as $child) {
                        $ctype = $child['$type'] ?? '';
                        if (strcasecmp($ctype, 'Group') === 0) {
                            // Nested group inside this item: recurse
                            flatten_field($child, $currPath, $rows);
                        } else {
                            if (isset($child['Name'])) {
                                $fieldsAssoc[$child['Name']] = $child['Value'] ?? null;
                            }
                        }
                    }
                    if (!empty($fieldsAssoc)) {
                        $rows[] = [
                            'path'   => implode('/', $currPath),
                            'fields' => $fieldsAssoc
                        ];
                    }
                } else {
                    // Item may itself be a group-like node with Fields or Items again
                    foreach (['Fields','Items'] as $k) {
                        if (!empty($item[$k]) && is_array($item[$k])) {
                            foreach ($item[$k] as $child) {
                                flatten_field($child, $currPath, $rows);
                            }
                        }
                    }
                }
            }
        } else {
            // Some Groups show up as empty containers with embedded named groups; try to descend generically
            foreach (['Fields','Items'] as $k) {
                if (!empty($node[$k]) && is_array($node[$k])) {
                    foreach ($node[$k] as $child) {
                        flatten_field($child, $currPath, $rows);
                    }
                }
            }
        }
    } else {
        // A primitive field alone doesn’t form a row; caller collects them at the item level.
        // No action here.
    }
}

/**
 * 3) Extract the bits you care about into “line item” and “measure” buckets.
 *    Heuristics: use the breadcrumb path and presence of fields like Description/Amount.
 */
function extract_line_items_and_measures(array $rows): array
{
    $lineItems = [];
    $measures  = [];

    foreach ($rows as $r) {
        $path = $r['path'];
        $f    = $r['fields'];

        // Normalize common fields
        $desc   = $f['Description'] ?? null;
        $amount = $f['Amount'] ?? ($f['TotalCharge'] ?? null);
        $header = $f['Header'] ?? null;          // some parts use a Header
        $equation = $f['Equation'] ?? null;
        $serviceType = $f['ServicePeriod'] ?? ($f['ServicePeriodText'] ?? null);

        // Very rough routing: adjust to your data
        if (stripos($path, 'LineItem') !== false) {
            if ($desc !== null || $amount !== null) {
                $lineItems[] = [
                    'path'     => $path,
                    'Header'   => $header,
                    'Name'     => $desc,
                    'Equation' => $equation,
                    'Amount'   => $amount,
                ];
            }
        } elseif (stripos($path, 'Meter') !== false || stripos($path, 'Measure') !== false) {
            // Meter/Measure related sections
            $measures[] = [
                'path'        => $path,
                'Header'      => $header,
                'Name'        => $f['MeasureName'] ?? ($f['MeasurePurpose'] ?? $desc),
                'Equation'    => $equation,
                'BilledUsage' => $f['BilledUsage'] ?? null,
                'CurrentRead' => $f['CurrentRead'] ?? null,
                'PreviousRead'=> $f['PreviousRead'] ?? null,
            ];
        }
    }

    return [$lineItems, $measures];
}

/**
 * 4) Load CSV into an index.
 *    Your CSV header row looks like: ID,Header,Name,Equation,Charge Type,Utility Network,...
 *    We’ll index by (Header, Name) and also by Name alone as fallback.
 */
function load_csv_index(string $csvPath): array
{
    $fh = fopen($csvPath, 'r');
    if (!$fh) {
        throw new RuntimeException("Cannot open CSV: $csvPath");
    }
    $header = fgetcsv($fh);
    if ($header === false) {
        fclose($fh);
        throw new RuntimeException("CSV is empty: $csvPath");
    }

    // Map header to columns
    $cols = array_map('trim', $header);
    $idx  = ['byKey' => [], 'byName' => []];

    while (($row = fgetcsv($fh)) !== false) {
        if (count($row) === 1 && $row[0] === null) continue;
        $assoc = array_combine($cols, $row);

        // Some samples show "null // Water Service" in Header — keep a cleaned version
        $hdrRaw = trim((string)($assoc['Header'] ?? ''));
        $hdr    = normalize_key(preg_replace('/^null\s*\/\/\s*/i', '', $hdrRaw)); // strip "null //"
        $name   = normalize_key((string)($assoc['Name'] ?? ''));

        if ($hdr !== '' && $name !== '') {
            $idx['byKey'][$hdr . '|' . $name] = $assoc;
        }
        if ($name !== '') {
            $idx['byName'][$name][] = $assoc;
        }
    }
    fclose($fh);
    return $idx;
}

/** 5) Normalize keys for matching (case/space/punct insensitive). */
function normalize_key(string $s): string
{
    $s = mb_strtolower($s);
    $s = preg_replace('/\s+/', ' ', $s);
    $s = preg_replace('/[^\p{L}\p{N}\s]/u', '', $s); // drop punctuation
    return trim($s);
}

/**
 * 6) Match flattened JSON items to CSV rows.
 *    Strategy:
 *      a) Try Header+Name exact (normalized)
 *      b) Fallback to Name exact
 *      c) Optional fuzzy (similar_text) if needed
 */
function match_items_to_csv(array $items, array $csvIdx): array
{
    $matches = [];
    foreach ($items as $it) {
        $hdr  = normalize_key((string)($it['Header'] ?? ''));
        $name = normalize_key((string)($it['Name'] ?? ''));

        $hit = null;
        if ($hdr !== '' && $name !== '') {
            $key = $hdr . '|' . $name;
            if (isset($csvIdx['byKey'][$key])) {
                $hit = $csvIdx['byKey'][$key];
            }
        }
        if (!$hit && $name !== '' && !empty($csvIdx['byName'][$name])) {
            // Pick the first for now; you can disambiguate by other columns if needed
            $hit = $csvIdx['byName'][$name][0];
        }

        // (Optional) fuzzy fallback
        // if (!$hit && $name !== '') {
        //     $best = null; $bestScore = 0;
        //     foreach ($csvIdx['byName'] as $k => $rows) {
        //         similar_text($name, $k, $pct);
        //         if ($pct > $bestScore) { $bestScore = $pct; $best = $rows[0]; }
        //     }
        //     if ($bestScore > 85) $hit = $best;
        // }

        $matches[] = [
            'json'   => $it,
            'csv'    => $hit,
            'status' => $hit ? 'matched' : 'unmatched',
        ];
    }
    return $matches;
}

// 1) Get path to uploaded Abbyy JSON (from earlier step where you stored uploaded_map)
$abbyyPath = $_SESSION['uploaded_map']['AbbyyExports'] ?? null;
$csvLineItemsPath = 'files/LineItems/line_items.csv';  // your uploaded CSV path
$csvMeasuresPath  = 'files/Measures/measures.csv';     // (if you split measures)

// 2) Decode JSON (lenient if needed)
if ($abbyyPath && is_file($abbyyPath)) {
    $raw = file_get_contents($abbyyPath);
    $data = decode_lenient_json($raw, true); // or json_decode($raw, true);

    if ($data) {
        // 3) Flatten
        $rows = flatten_abbyy($data);

        // 4) Split into line items & measures
        list($lineItems, $measures) = extract_line_items_and_measures($rows);

        // 5) Load CSV indices
        $csvLI = load_csv_index($csvLineItemsPath);   // for your “Charge Type…” mapping
        $csvME = load_csv_index($csvMeasuresPath);    // if you also map measures via CSV

        // 6) Match
        $matchedLineItems = match_items_to_csv($lineItems, $csvLI);
        $matchedMeasures  = match_items_to_csv($measures,  $csvME);

        // (Optional) save to session for display
        $_SESSION['matched_line_items'] = $matchedLineItems;
        $_SESSION['matched_measures']   = $matchedMeasures;
    } else {
        $_SESSION['error'] = $_SESSION['error'] ?: 'Failed to decode JSON.';
    }
}











// Other functions that might need to be used




/**
 * Try to clean up common JSON issues, then decode.
 * Returns array/object on success, null on failure (and sets $_SESSION['error']).
 */
function decode_lenient_json(string $raw, bool $assoc = true)
{
    $clean = sanitize_json($raw);
    // 1st try: strict decode
    $data = json_decode($clean, $assoc);
    if (json_last_error() === JSON_ERROR_NONE) {
        return $data;
    }

    // 2nd try: trim to the last complete root (handles extra trailing } ] garbage)
    $trimmed = trim_to_complete_root($clean);
    $data = json_decode($trimmed, $assoc);
    if (json_last_error() === JSON_ERROR_NONE) {
        return $data;
    }

    // 3rd try: remove trailing commas ( ,]  ,} )
    $noTrailingCommas = remove_trailing_commas($trimmed);
    $data = json_decode($noTrailingCommas, $assoc);
    if (json_last_error() === JSON_ERROR_NONE) {
        return $data;
    }

    $_SESSION['error'] = 'JSON decode error: ' . json_last_error_msg();
    return null;
}

/** Remove BOM, normalize encoding/whitespace/non-printables. */
function sanitize_json(string $s): string
{
    // Strip UTF-8 BOM
    if (strncmp($s, "\xEF\xBB\xBF", 3) === 0) {
        $s = substr($s, 3);
    }
    // Normalize to UTF-8, replace invalid bytes
    if (!mb_check_encoding($s, 'UTF-8')) {
        $s = mb_convert_encoding($s, 'UTF-8', 'UTF-8');
    }
    // Normalize line endings
    $s = str_replace(["\r\n", "\r"], "\n", $s);
    // Remove non-printable control chars except \t \n
    $s = preg_replace('/[^\P{C}\t\n]/u', '', $s);
    // Trim obvious junk around
    return trim($s);
}

/**
 * Remove trailing commas before ] or } e.g. {"a":1,} -> {"a":1}
 * and [1,2,] -> [1,2]
 */
function remove_trailing_commas(string $s): string
{
    $s = preg_replace('/,\s*([}\]])/', '$1', $s);
    return $s;
}

/**
 * Keep only the shortest prefix that forms a COMPLETE top-level JSON value
 * (object or array). Skips unmatched extra closing ] } and truncates any
 * junk after the root closes. Also respects string/escape context.
 */
function trim_to_complete_root(string $s): string
{
    $out = '';
    $depthObj = 0; // { }
    $depthArr = 0; // [ ]
    $inString = false;
    $escape = false;
    $rootStarted = false;
    $lastCompletePos = -1;

    $len = strlen($s);
    for ($i = 0; $i < $len; $i++) {
        $ch = $s[$i];
        $out .= $ch;

        if ($inString) {
            if ($escape) {
                $escape = false;
                continue;
            }
            if ($ch === '\\') {
                $escape = true;
                continue;
            }
            if ($ch === '"') {
                $inString = false;
            }
            // inside string, structure chars are ignored
            continue;
        } else {
            if ($ch === '"') {
                $inString = true;
                continue;
            }
        }

        // Not in string: track structure
        if ($ch === '{') {
            $depthObj++;
            $rootStarted = true;
        } elseif ($ch === '[') {
            $depthArr++;
            $rootStarted = true;
        } elseif ($ch === '}') {
            if ($depthObj > 0) {
                $depthObj--;
            } else {
                // skip unmatched extra closing, remove it from output
                $out = substr($out, 0, -1);
            }
        } elseif ($ch === ']') {
            if ($depthArr > 0) {
                $depthArr--;
            } else {
                // skip unmatched extra closing, remove it from output
                $out = substr($out, 0, -1);
            }
        }

        // If root started and all depths returned to zero, remember this as a complete cutoff
        if ($rootStarted && $depthObj === 0 && $depthArr === 0 && !$inString) {
            $lastCompletePos = strlen($out);
            // We keep scanning in case there is more junk; we’ll trim at the end.
        }
    }

    if ($lastCompletePos >= 0) {
        return substr($out, 0, $lastCompletePos);
    }
    // If we never found a balanced root, return original (decoding will still fail and report)
    return $s;
}


