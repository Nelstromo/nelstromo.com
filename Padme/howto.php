<?php
session_start()





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

        <div class="container">
            <h1>How to use the Processing ABBYY Data Matching Engine (Padme)</h1>
            <p>
                The Export Validation Tool is designed to help users validate and analyze utility bill data exports. Follow the steps below to get started:
            </p>
            <h2>Step 1: Prepare Your Data</h2>
            <p>
                Ensure that your utility bill data is exported in a supported format (e.g., JSON). Make sure the data is clean and free from errors.
            </p>
            <h2>Step 2: Upload Your Data</h2>
            <p>
                Use the upload feature on the main page to select and upload your utility bill data file. The tool will process the file and display any validation results.
            </p>
            <h2>Step 3: Review Validation Results</h2>
            <p>
                After uploading, the tool will analyze the data and provide a summary of any issues found. Review the results carefully to identify any discrepancies or errors in the data.
            </p>
            <h2> Please Note:</h2>
            <ul>
                <li>This tool is a work in progress and may not cover all edge cases.</li>
                <li>For best results, ensure your data adheres to the expected schema.</li>
                <li>If anything renders blank in the Bill Object Details, it likely means that branch is absent in your sample JSON—structure-wise the renderers will only print sections that exist.</li>
            
    </main>
    <footer>
        <p>&copy; 2025 Export Validation Tool. All rights not reserved. (nelson is too lazy to register this or understand how copyrights work)</p>
    </footer>
</body>
</html>
        

           