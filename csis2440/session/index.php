<?php
/**
 * @author Nelson Long
 * @version 3.0.12 Beta 
 * @description This is the assignment for CSIS 2440, Module 5: Assignment #2 - Keepin' the Session Alive
 * @todo Make the login mechanism case sensitive
 * @todo Remove the reset button from the table page
 * ------------------//PHP DOCUMENTATION ---- TO MAKE MY LIFE EASIER\\--------------------------------------------
 * @var string $username The username input from the login form. Used to update the session username.
 * @var string $password The password input from the login form
 * @var string $fileChoice The file selected by the user from the dropdown
 */
    session_start();

    // ---------------Initialize variables ---------------------------------------------
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';


    // If the user selects a file, store the choice in the session
    if (isset($_POST['fileSelection'])) {
        $_SESSION['fileSelection'] = $_POST['fileSelection'];
    } elseif (!isset($_SESSION['fileSelection'])) {
        $_SESSION['fileSelection'] = '';
    }
    $fileChoice = $_SESSION['fileSelection'] ?? '';

    // ------------------- Initialize SESSION variables ----------------------------------
    
    if (!isset($_SESSION['profilePic'])) {
        $_SESSION['profilePic'] = ''; // Default profile picture
    }
    
    // -----------------------Set Database Credentials-----------------------------------
    if ($_SERVER['HTTP_HOST'] == 'localhost') {
        define('HOST', 'localhost');
        define('USER', 'root');
        define('PASS', '1550');
        define('DB', 'eghbxxte_sessions');
    } else {
        define('HOST', 'localhost');
        define('USER', 'eghbxxte_rootNelstromo');
        define('PASS', 'n7c/bB93g7-n7c/bB93g7');
        define('DB', 'eghbxxte_sessions');
    }
    //-----------------------------------------------------------------------------------

    // ------------------------Connect to the database ----------------------------------
    $conn = mysqli_connect(HOST, USER, PASS, DB);
    //-----------------------------------------------------------------------------------

    // Check if Access has been set in the session
    if (!isset($_SESSION['access'])) {
        $_SESSION['access'] = '';
    }

    // If username and password are set, run the checkCredentials function
    if ($username !== '' && $password !== '') {
        checkCredentials($username, $password, $conn);
    }

    // If the user selects the reset button, terminate the session
    if (isset($_POST['reset'])) {
        sessionTermination();
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
        <nav>
            <div class="nav-bar">
                <div class="nav-title">
                    <h1>TOP SECRET ACCESS</h1>
                    <?php if ($_SESSION['access'] == "granted") {
                                echo '<div class="grantedScreen">
                                <p class="access-granted"><strong>ACCESS GRANTED</strong></p>
                            </div>';
                            }
                            ?>
                </div>
                <div class="nav-links">
                    <ul class="links">
                        <?php
                        if ($_SESSION['access'] == "granted") {
                            echo '<li><a href=".">Home</a></li>';
                            echo '<li><a href="video.php">Video</a></li>';
                            echo '<li><a href="logout.php">Logout</a></li>';
                        }
                        ?>
                    </ul>
                </div>
                <div class="nav-profile-Img">
                    <?php if (!empty($_SESSION['profilePic'])): ?>
                        <a href="account.php">
                            <img src="<?php echo $_SESSION['profilePic']; ?>" class="profile-pic">
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>

        <div class="container">
            <?php if ($_SESSION['access'] == "granted"): ?>
                <!-- Access granted if username and password match database -->
                <div class="account-header">
                    <h2 class="account-header-msg1">Welcome Agent,
                        <?php echo capitalizeFirstLetter($_SESSION['user-key']); ?>!</h2>
                    <p>We found some files for you to review</p>
                </div>

                <div class="fileSelection">
                    <p>Two files have been found</p>

                    <form method="POST">
                        <label for="fileSelection">Select which one you would like to view:</label>
                        <select id="fileSelection" name="fileSelection">
                            <option value="FBI" <?php if ($fileChoice == 'FBI')
                                echo 'selected'; ?>>FBI</option>
                            <option value="SPIES" <?php if ($fileChoice == 'SPIES')
                                echo 'selected'; ?>>Spies</option>
                        </select>
                        <button type="submit">Submit</button>
                    </form>

                    <br>

                    <?php if ($fileChoice): ?>
                        <?php createTable($fileChoice); ?>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <!-- Login Form if user has not submitted yet -->
                <h1>Welcome</h1>
                <h4>Please enter your Username and Password</h4>

                <?php
                if ($_SESSION['access'] == "denied") {
                    // If access is denied, show the denied message
                    echo '<div class="deniedMSG">';
                    echo "<p class=access-granted><strong>ACCESS DENIED</strong></p>";
                    echo '<div class="deniedMSG">';
                }
                ?>

                <form action="index.php" method="post" class="infoForm">

                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" value="<?php echo $username; ?>">

                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" value="<?php echo $password; ?>">

                    <input type="submit" name="submit" value="Login">
                    <button type="submit" name="reset" class="resetbtn">Reset</button>
                </form>
            <?php endif; ?>


        </div>
    </main>
</body>

</html>


<?php

/**
 * Function to check user credential input against the database and stores the result in the session
 * 
 * If successful, it updates the session username and access to granted
 * 
 * If unsuccessful, it updates the session access to denied
 * @param string $username A string containing the username input from the login form
 * @param string $password A string containing the password input from the login form
 * @param mixed $conn Database connection resource
 * 
 */
function checkCredentials($username, $password, $conn)
{
    $query = "SELECT * FROM users WHERE BINARY username = '$username' AND BINARY passcode = '$password'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $_SESSION['access'] = "granted";
        $_SESSION['user-key'] = $username;
        setProfilePic($conn);
    } else {
        $_SESSION['access'] = "denied";
    }
}

/**
 * Terminates the session by unsetting all session variables and destroying the session
 * Redirects the user to the login page after termination
 * @return never
 */
function sessionTermination()
{
    session_unset();
    session_destroy();
    header("Location: ."); // Redirect to the login page after session termination
    exit();
}

/**
 * Sets the profile picture in the session by adding to the $_SESSION variables
 * 
 * Checks if the user-key is set before proceeding
 * @global $_SESSION['user-key'] The username used to set the profile picture
 * @return mixed Returns true if the profile picture is set successfully, false if the user-key is not set
 */
function setProfilePic($conn)
{
    if (!isset($_SESSION['user-key'])) {
        return false; // User key not set
    }

    $userKey = $_SESSION['user-key'];

    // Query the database for the image path
    $query = "SELECT image FROM users WHERE username = '$userKey'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['profilePic'] = $row['image'];
        return true; // Success
    }

    return false; // Query failed or no result
}


/**
 * 
 * Reads a file based on the filename, explodes its content, and creates a table from the records
 * @param string $fileChoice The file selected by the user from the dropdown
 */
function createTable($fileChoice)
{
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

        echo "<table class='infoTable'>"; // Create a table
        echo "<tr><th>Agent</th><th>Code Name</th></tr>";

        foreach ($records as $record) {
            $fields = explode(',', trim($record));
            if (count($fields) >= 2) {
                echo "<tr><td>" . $fields[0] . "</td><td>" . $fields[1] . "</td></tr>";
            }
        }

        echo "</table>"; // Close the table
    }

}

/**
 * Function to capitalize the first letter of a string
 * 
 * Uses a regular expression to match the first letter and convert it to uppercase
 * @param string $name The name to be capitalized
 * @return string The name with the first letter capitalized
 */

function capitalizeFirstLetter($name) {
    return preg_replace_callback('/^[a-z]/', function ($matches) {
        return strtoupper($matches[0]);
    }, $name);
}
?>