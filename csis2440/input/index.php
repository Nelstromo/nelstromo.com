<?php
$host = "localhost";
$username = "eghbxxte_rootNelstromo"; // default for XAMPP
$password = "n7c/bB93g7-n7c/bB93g7";     // default is empty for XAMPP
$database = "eghbxxte_contact_form";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first = $_POST["firstName"];
    $last = $_POST["lastName"];
    $phone = $_POST["phone"];
    $email = $_POST["email"];

    $sql = "INSERT INTO contacts (first_name, last_name, phone, email)
            VALUES ('$first', '$last', '$phone', '$email')";

    if (!$conn->query($sql)) {
        echo "<p style='color:red;'>Error saving to database: " . $conn->error . "</p>";
    }
}
?>


<!doctype html>
<html lang="en">
<head>
    <title>Input Form</title>
    <link rel="stylesheet" href="css/style.css" type="text/css">
</head>
<body>
    <div class="container">
        <h1>Contact Information</h1>
		<h4> *Def not stealing or selling your info</h4>

        <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
            <div class="thanksMSG">
                <p>Thank you, <strong><?php echo ($_POST["firstName"] . " " . $_POST["lastName"]); ?></strong></p>
                <p>Your phone number is: <strong><?php echo ($_POST["phone"]); ?></strong></p>
                <p>Your email address is: <strong><?php echo ($_POST["email"]); ?></strong></p>
            </div>
        <?php else: ?>
            <form action="index.php" method="post" class="infoForm">
				
                <label for="firstName">First Name:</label>
                <input type="text" id="firstName" name="firstName">

                <label for="lastName">Last Name:</label>
                <input type="text" id="lastName" name="lastName">

                <label for="phone">Phone Number:</label>
                <input type="text" id="phone" name="phone">

                <label for="email">Email Address:</label>
                <input type="text" id="email" name="email">

                <input type="submit" value="Submit">
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
