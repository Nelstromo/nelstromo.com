<?php

// Retrieve POST data
$name = $_POST['name'] ?? '';
$phone = $_POST['phone'] ?? '';
include'includes/accessdatabase.php';

// Validation logic must be BEFORE any output
if (!empty($phone) && preg_match('/^\(\d{3}\)\d{3}-\d{4}$/', $phone)) {
    
    // Valid: continue to HTML to show thank-you message
} else {
    // Invalid: redirect back to form with error and old input
    $query = http_build_query([
        'error' => 'error',
        'name' => $name,
        'phone' => $phone
    ]);
    header("Location: index.php?$query");
    exit;
}
?>

<!doctype html>
<html lang="en">
<head>
    <title>Phone Number Processing</title>
    <link rel="stylesheet" href="css/style.css" type="text/css">
</head>
<body>
<main>
    <div class="container">
        <div class="thanksMSG">
            <p>Thank you, <strong><?php echo $name; ?></strong>!</p>
            <p>Your phone number is: <strong><?php echo $phone; ?></strong></p>
            <a href="index.php" class="resetBtn">Return</a>
        </div>
    </div>
</main>
</body>
</html>
