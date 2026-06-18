<?php
session_start();

// Initialize variables
$targetDir = "files/";
$showResults = false;

if (!isset($_SESSION['error'])) {
    $_SESSION['error'] = '';
}
//------------------------------------------------------------------------
include_once 'classes/Measure.php';
include_once 'classes/LineItem.php';
include_once 'classes/UtilityBill.php';

include_once 'include/functions.php';
//------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['submit'])) {
        $showResults = true;
        handleFileUpload($_FILES['appExportData'], $targetDir, 'AbbyyExports', ['json']);
        handleFileUpload($_FILES['lineItemData'], $targetDir, 'LineItems', ['csv', 'xls', 'xlsx']);
        handleFileUpload($_FILES['measureData'], $targetDir, 'Measures', ['csv', 'xls', 'xlsx']);
    }
}


?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Validation Tool</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <main>
        <nav>
            <?php include 'include/navbar.php'; ?>
        </nav>

        <?php if (!$_SESSION['error'] == ''): ?>

            <div class="deniedMSG">
                <p class=denied-msg-alert><strong>ERROR</strong></p>
                <p class="error-msg"><?php echo $_SESSION['error'] ?></p>
            </div class="deniedMSG">

        <?php endif; ?>

        <div class="container">
            <?php if (!$showResults): ?>

                <form action="index.php" method="post" enctype="multipart/form-data" class="upload-form">

                    <label for="appExportData">ABBYY JSON Export</label>
                    <input type="file" id="appExportData" name="appExportData">

                    <label for="lineItemData">Line Item Data</label>
                    <input type="file" id="lineItemData" name="lineItemData">

                    <label for="measureData">Measure Data</label>
                    <input type="file" id="measureData" name="measureData">

                    <input type="submit" name="submit" value="Submit">
                    <input type="submit" name="reset" value="Reset">

                </form>

            <?php endif; ?>

            <?php if ($showResults): ?>
                <div class="results">
                    <h2>Results</h2>
                    <p class="error-msg"><?php echo $_SESSION['error']; ?></p>
                    <div class="uploaded-files-msg">
                        <h3>Uploaded Files:</h3>
                        <ul>
                            <li>ABBYY JSON Export: <?php echo $_FILES['appExportData']['name']; ?></li>
                            <li>Line Item Data: <?php echo $_FILES['lineItemData']['name']; ?></li>
                            <li>Measure Data: <?php echo $_FILES['measureData']['name']; ?></li>

                        </ul>

                    </div>
                    <h3>JSON Preview:</h3>
                    <div class="json-preview"> <!-- Hideable section for JSON preview -->
                        <pre>
                            <?php
                            echo isset($_SESSION['jsonData'])
                                ? htmlspecialchars(json_encode($_SESSION['jsonData'], JSON_PRETTY_PRINT))
                                : 'No JSON loaded';
                            ?>
                        </pre>

                    </div>
                    <h3>Simplified JSON:</h3>
                    <div class="json-preview"> <!-- Hideable section for JSON preview -->
                        <pre>
                            <?=
                            isset($_SESSION['simplified_json'])
                                ? htmlspecialchars(json_encode($_SESSION['simplified_json'], JSON_PRETTY_PRINT))
                                : 'No simplified JSON yet'
                            ?>
                        </pre>
                    </div>
                </div>

                <div class="bill-object-validation-container"> <!-- Divs inside this container should be side by side -->

                    <div class="uploaded-controlnumbers-navigation">
                        <h3>Uploaded Control Numbers:</h3>
                        <ul id="controlNumbersList">
                            <!-- Dynamically populated -->
                        </ul>
                    </div>
                    <div class="bill-object-details">
                        <h3>Bill Object Details:</h3>
                        <div id="billObjectDetails">
                            <?php
                            if (isset($_SESSION['simplified_json']) && is_array($_SESSION['simplified_json'])) {
                                // Build the object graph from simplified JSON
                                $bill = new UtilityBill($_SESSION['simplified_json']);
                                echo $bill->toHtml();
                            } else {
                                echo '<p>No bill loaded.</p>';
                            }
                            ?>

                        </div>
                        <div class="validation-flags">
                            <h4>Validation Flags:</h4>
                            <ul id="validationFlagsList">
                                <!-- Dynamically populated -->
                            </ul>
                        </div>
                    </div>
                    <div class="LIM-details"> <!-- LineItemDetails and MeasureDetails should be stacked vertically -->
                        <h3>Line Item Manager Data:</h3>
                        <div id="LineItemDetails">
                            <span>Line Items</span>
                            <?php if (!empty($_SESSION['LineItemsUploaded'])): ?>
                                <table border="0" cellpadding="6" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <?php foreach ($_SESSION['LineItemFields'] as $h): ?>
                                                <th><?= htmlspecialchars($h) ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($_SESSION['LineItemsUploaded'] as $row): ?>
                                            <tr>
                                                <?php foreach ($row as $cell): ?>
                                                    <td><?= htmlspecialchars((string)$cell) ?></td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>No line items uploaded.</p>
                            <?php endif; ?>

                        </div>
                        <div id="MeasureDetails">
                            <span>Measures</span>

                            <?php if (!empty($_SESSION['MeasuresUploaded'])): ?>
                                <table border="0" cellpadding="6" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <?php foreach ($_SESSION['MeasureFields'] as $h): ?>
                                                <th><?= htmlspecialchars($h) ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($_SESSION['MeasuresUploaded'] as $row): ?>
                                            <tr>
                                                <?php foreach ($row as $cell): ?>
                                                    <td><?= htmlspecialchars((string)$cell) ?></td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p>No measures uploaded.</p>
                            <?php endif; ?>


                        </div>
                    </div>
                </div>



            <?php endif; ?>

        </div>
    </main>

</body>

</html>


<?php



/**
 * Generic file upload handler
 *
 * @param array $file The $_FILES entry
 * @param string $targetDir The base directory where files go
 * @param string $subFolder The subfolder for this file type
 * @param array $allowedExtensions Allowed file extensions
 * @return void
 */
function handleFileUpload($file, $targetDir, $subFolder, $allowedExtensions)
{
    $fileName = basename($file['name']);
    $fileTmp = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];

    $fullPath = rtrim($targetDir, "/") . "/" . $subFolder . "/";
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (in_array($fileExt, $allowedExtensions)) {
        if ($fileError === 0) {
            if ($fileSize < 250 * 1024 * 1024) { // 250MB
                $fileDestination = $fullPath . $fileName;

                if (move_uploaded_file($fileTmp, $fileDestination)) {
                    $_SESSION['uploaded'][] = $fileDestination;
                    $_SESSION['uploaded_map'][$subFolder] = $fileDestination;

                    if ($subFolder === 'AbbyyExports') {
                        [$jsonData, $err] = load_json_file_clean($fileDestination);

                        if ($jsonData === null) {
                            $_SESSION['error'] = $err;
                            $_SESSION['jsonData'] = null;
                            $_SESSION['simplified_json'] = null;
                        } else {
                            $_SESSION['jsonData'] = $jsonData;
                            $_SESSION['simplified_json'] = simplifyAbbbyExportRootless($_SESSION['jsonData']);
                        }
                    } elseif ($subFolder === 'LineItems') {
                        lineitemUpload($fileDestination);
                    } elseif ($subFolder === 'Measures') {
                        measureUpload($fileDestination);
                    }
                } else {
                    $_SESSION['error'] = "Failed to move uploaded file to $fileDestination";
                }
            } else {
                $_SESSION['error'] = "File too large.";
            }
        } else {
            $_SESSION['error'] = "Upload error: $fileError";
        }
    } else {
        $_SESSION['error'] = "Invalid file type. Allowed: " . implode(", ", $allowedExtensions);
    }
}
/**
 * Summary of measureUpload
 * @param mixed $measureFile 
 * @return void
 */
function measureUpload($measureFile)
{
    $fs = fopen($measureFile, 'r');
    $firstLine = fgets($fs);
    $_SESSION['MeasureFields'] = str_getcsv($firstLine);

    while (($line = fgets($fs)) !== false) {
        $data = str_getcsv($line);
        $measure = new Measure($data);
        $measures[] = $measure;
    }
    $_SESSION['MeasuresUploaded'] = $measures;
    fclose($fs);
}
/**
 * Summary of lineitemUpload
 * @param mixed $lineItemFile
 * @return void
 */
function lineitemUpload($lineItemFile)
{
    $fs = fopen($lineItemFile, 'r');
    $firstLine = fgets($fs);
    $_SESSION['LineItemFields'] = str_getcsv($firstLine);

    while (($line = fgets($fs)) !== false) {
        $data = str_getcsv($line);
        $lineItem = new LineItem($data);
        $lineItems[] = $lineItem;
    }
    $_SESSION['LineItemsUploaded'] = $lineItems;
    fclose($fs);
}


function simplifyAbbbyJson($node)
{
    // If node is an object with Name/Value
    if (is_array($node) && isset($node['Name'])) {
        $key = $node['Name'];

        // Groups contain Items (sub-fields)
        if (isset($node['Items'])) {
            $children = [];
            foreach ($node['Items'] as $item) {
                if (isset($item['Fields'])) {
                    foreach ($item['Fields'] as $field) {
                        $children = array_merge($children, simplifyAbbbyJson($field));
                    }
                }
            }
            return [$key => $children];
        }

        // Simple field with Value
        if (array_key_exists('Value', $node)) {
            return [$key => $node['Value']];
        }
    }

    // If node has Fields, recurse
    if (is_array($node) && isset($node['Fields'])) {
        $result = [];
        foreach ($node['Fields'] as $field) {
            $result = array_merge($result, simplifyAbbbyJson($field));
        }
        return $result;
    }

    return [];
}




?>