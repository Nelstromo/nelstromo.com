<?php
$name = $_GET["name"] ?? '';
$phone = $_GET["phone"] ?? '';
$error = $_GET["error"] ?? '';
include'includes/accessdatabase.php';
?>


<!doctype html>
<html lang="en">
<head>
    <title>Valid Input Form</title>
    <link rel="stylesheet" href="css/style.css" type="text/css">
</head>
<body>
    <main>
        <div class="container">
            <h1>Contact Information v2</h1>
            <h4>*Def not stealing or selling your info again</h4>

            <?php

                
                #$error = $_GET["error"] ?? '';
    
                if (!empty($error)) {
                    echo '<div class="errorMSG">';
                    echo "<p><strong>Error:</strong>The Phone number you entered is wack man!</p>";
                    echo "<p>Please enter in the format: <strong>(xxx)xxx-xxxx</strong></p>";
                    echo '</div>';
                }
            ?>

            <form action="process.php" method="post" class="infoForm">
                <label for="name">First Name:</label>
                <input type="text" id="name" name="name" value="<?php echo $name; ?>">

                <label for="phone">Phone Number:</label>
                <input type="text" id="phone" name="phone"
                    placeholder="(xxx)xxx-xxxx" 
                    value="<?php echo $phone; ?>">

                <input type="submit" name="submit" value="Submit">
                <a href="index.php" class="resetBtn">Reset</a>
            </form>
        </div>
    </main>
</body>
</html>
