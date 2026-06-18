<?php
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $accessGranted = 0; // 2 = Access granted, 1 = Access denied, 0 = No access attempt
    $fileChoice = $_POST['fileSelection'] ?? '';

    // Set Database Credentials
    if ($_SERVER['HTTP_HOST'] == 'localhost') {
        define('HOST', 'localhost');
        define('USER', 'root');
        define('PASS', '1550');
        define('DB', 'eghbxxte_insecure');
    } else {
        define('HOST', 'localhost');
        define('USER', 'eghbxxte_rootNelstromo');
        define('PASS', 'n7c/bB93g7-n7c/bB93g7');
        define('DB', 'eghbxxte_insecure');
    }

    // Connect to the database
    $conn = mysqli_connect(HOST, USER, PASS, DB);

    if (!$conn) {
        die("Database connection failed: " . mysqli_connect_error());
    }

    // ----- OLD TEXT FILE LOGIN (DEPRECATED) -----
    // $fs = fopen('includes/testing.txt', 'r');
    // $fbiContents = fread($fs, filesize('includes/testing.txt'));
    // fclose($fs);
    // $fbiData = explode('||>><<||', $fbiContents);

    // if ($username !== '' && $password !== '') {
    //     $userCreds = createUserCreds($username, $password);
    //     $accessGranted = checkCredentials($userCreds, $fbiData);
    // }

    // ----- NEW SQL DATABASE LOGIN -----
    if ($username !== '' && $password !== '') {
        // WARNING: This is NOT secure, but fine for local/test use
        $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $accessGranted = 2; // Access granted
        } else {
            $accessGranted = 1; // Access denied
        }
    }
?>



<!doctype html>
<html lang="en">
<head>
    <title>TOP SECRET ACCESS</title>
    <link rel="stylesheet" href="css/style.css" type="text/css">
</head>
<body>
    <main>
        <div class="container">

            <?php if ($accessGranted == 2): ?>
                <!-- Access granted if username and password match text file -->
                    <div class="grantedScreen">
                        <p><strong>ACCESS GRANTED</strong></p>
                        <br>
                        <p>Two files have been found</p>

                        <form method="POST">
                        <input type="hidden" name="username" value="<?= $username ?>">
                        <input type="hidden" name="password" value="<?= $password ?>">
                        <label for="fileSelection">Select which one you would like to view:</label>
                        <select id="fileSelection" name="fileSelection">
                        <option value="FBI" <?php if ($fileChoice == 'FBI') echo 'selected'; ?>>FBI</option>
                        <option value="SPIES" <?php if ($fileChoice == 'SPIES') echo 'selected'; ?>>Spies</option>
                        </select>
                        <button type="submit">Submit</button>
                    </form>

                    <br>

                    <?php if ($fileChoice): ?>
                    
                        <?php
                        switch ($fileChoice) {
                            case 'FBI':
                                $filename = 'includes/fbi.txt';
                                break;
                            case 'SPIES':
                                $filename = 'includes/spies.txt';
                                break;
                            default:
                                $filename = '';
                        }

                        // Only proceed if file is valid and exists
                        if ($filename && file_exists($filename)) {
                            $fs = fopen($filename, 'r');
                            $filesize = filesize($filename);
                            $content = fread($fs, $filesize);
                            fclose($fs);

                            $records = explode('||>><<||', $content);

                            echo "<table class='infoTable'>";
                            echo "<tr><th>Agent</th><th>Code Name</th></tr>";

                            foreach ($records as $record) {
                                $fields = explode(',', trim($record));
                                if (count($fields) >= 2) {
                                    echo "<tr><td>" .$fields[0]."</td><td>" .$fields[1]."</td></tr>";
                                }
                            }

                            echo "</table>";
                        } 

                    ?>
            <?php endif; ?>
                    <br>
                <a href="index.php" class="resetBtn">Return</a>
                    </div>
           
            <?php else: ?>
                <!-- Login Form if user has not submitted yet -->
                <h1>Welcome</h1>
                <h4>Please enter your Username and Password</h4>

                <?php
                if($accessGranted == 1) {
                    // If access is denied, show the denied message
                    echo '<div class="deniedMSG">';
                    echo "<p><strong>ACCESS DENIED</strong></p>";
                    echo '<div class="deniedMSG">';
                }
                    ?>

                <form action="index.php" method="post" class="infoForm">

                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" 
                    
                         value="<?php echo $username; ?>">

                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password"
                    
                         value="<?php echo $password; ?>">

                    <input type="submit" name="submit" value="Login">
                    <a href="index.php" class="resetBtn">Reset<?php $accessGranted == 2 ?></a>
                </form>
            <?php endif; ?>

            
        </div>
    </main>
</body>
</html>


<?php

// Implodes the user inputs into a string for comparison that matches the format in the text file
/**
 * createUserCreds takes the user inputs for Username and Password in the form and implodes them into a string with a comma
 * @param mixed $username The users input from the Username field on the form
 * @param mixed $password The users input from the Password field on the form
 * @return string $userCreds A string containing the user input in the format "username,password" from the login form
 */
function createUserCreds($username, $password) {
    $inputs = ["$username", "$password"]; // Grabs the username and password from the form
    $userCreds = implode(',', $inputs);
    return $userCreds;
}

/**
 * 
 * Checks the user credentials against the FBI Data file by looping through all the file string instances and comparing it to the user input
 * @param string $userCreds A string containing the user input in the format "username,password" from the login form
 * @param mixed $fbiData
 * @return int $AccessGranted which will be 2 for access granted, 1 for access denied
 */
function checkCredentials($userCreds, $fbiData) {
    #$accessGranted = 0; // Default access denied
    foreach($fbiData as $fbiCred) {
        
        if ($userCreds == $fbiCred) {
            $accessGranted = 2; // Access granted
            break;
        }
        else {
            $accessGranted = 1; // Access denied
        }
    }
    return $accessGranted;
}

?>