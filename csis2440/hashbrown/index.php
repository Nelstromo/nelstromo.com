<?php
/**
 * @author Nelson Long
 * @version 4.0.11 
 * @description This is the assignment for CSIS 2440, Module 5: Assignment #3 - Hash Browns
 *
 * @todo Add user creation functionality and user deletion functionality to admins
 * @todo Add Admin ability to unlock users
 * @todo Add Admin page

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
        define('DB', 'hashbrowns');
    } else {
        define('HOST', 'localhost');
        define('USER', 'eghbxxte_rootNelstromo');
        define('PASS', 'n7c/bB93g7-n7c/bB93g7');
        define('DB', 'eghbxxte_hashbrowns');
    }
    //-----------------------------------------------------------------------------------

    // ------------------------Connect to the database ----------------------------------
    $conn = mysqli_connect(HOST, USER, PASS, DB);
    //-----------------------------------------------------------------------------------

    // Check if Access has been set in the session
    if (!isset($_SESSION['access'])) {
        $_SESSION['access'] = '';
    }

    if (!isset($_SESSION['errormsg'])) {
        $_SESSION['errormsg'] = '';
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
            <?php include 'includes/navbar.php'; ?>
        </nav>

        <div class="container">
            <?php if ($_SESSION['access'] == "" | $_SESSION['access'] == "denied" ): ?>
                
                <div class="page-header">
                    <h1 class="header">Welcome</h1>
                    <p class="msg">Please enter your Username and Password</p>
                </div>

                
                <?php if ($_SESSION['access'] == "denied"): ?>
                    
                    <div class="deniedMSG">
                        <p class=denied-msg-alert><strong>ACCESS DENIED</strong></p>
                        <p class="denied-msg"><?php echo $_SESSION['errormsg']; ?></p>
                    </div class="deniedMSG">
                
                <?php endif; ?>

                <form action="index.php" method="post" class="login-form">

                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" value="<?php echo $username; ?>">

                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" value="<?php echo $password; ?>">

                    <input type="submit" name="submit" value="Login">
                    <button type="submit" name="reset" class="resetbtn">Reset</button>
                    <p class="account-msg">Don't have an account? <a href="create-account.php">Create Account</a></p>
                </form>
            <?php endif; ?>


            <?php if ($_SESSION['access'] == "granted"): ?>
                <!-- Access granted if username and password match database -->
                <div class="account-header">
                    <h2 class="account-header-msg1">Welcome Agent, <?php echo capitalizeFirstLetter($_SESSION['user-key']); ?>!</h2>
                    <p> You have logged in <?php getSuccessfulLogins($conn) ?> times.</p>
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
                    <!-- Generate Info Table -->
                    <?php if ($fileChoice): ?>
                        <?php createTable($fileChoice); ?> 
                    <?php endif; ?>
                </div>
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
    
    $hashedPassword = nelstromoHash($username, $password);
    $password = $hashedPassword; // Use the hashed password for the query
    if (!isAccountLocked($username, $conn))  // Check if account is locked
        {
            #$query = "SELECT * FROM secureusers WHERE BINARY username = '$username' AND BINARY passcode = '$hashedPassword'";
            $query = "SELECT * FROM secureusers WHERE  username = '$username' AND passcode = '$password'";
            $result = mysqli_query($conn, $query);

            if ($result) {
                if (mysqli_num_rows($result) > 0) {
                #echo 'This user exists';
                // Login success
                $_SESSION['access'] = "granted";
                $_SESSION['user-key'] = $username;
                isAdmin($username, $conn); // Check if the user is an admin
                setProfilePic($conn);
                logSuccessfulLogin($username, $conn);
                } else {
                #echo 'No such user';
                // Login failed
                $_SESSION['access'] = "denied";
                $_SESSION['errormsg'] = "Invalid username/password!";
                logFailedLogin($username, $conn);
                } 
            } else {
                echo 'Query failed: ' . mysqli_error($conn);
            }
        } else {
            // If account is locked, set access to denied
            $_SESSION['access'] = "denied";
            $_SESSION['errormsg'] = "Your account is locked due to too many failed login attempts.";
        }


}

/** Summary of logSuccessfulLogin
 * If run, it will update the user_logins table with the last login date and increment the successful logins count
 * @param mixed $username The username inserted by the login form
 * @param mixed $conn Database connection resource
 * 
 */
function logSuccessfulLogin($username, $conn) {
    $sql = "UPDATE user_logins SET lastlogindate = NOW(), successfulLogins = successfulLogins + 1, failedLogins = 0 WHERE username = '$username'";

    return mysqli_query($conn, $sql);
}
/**
 * Summary of logFailedLogin
 * If run, it will update the user_logins table with the failed login count and lock the account if it reaches 4 failed attempts
 * @param mixed $username The username inserted by the login form
 * @param mixed $conn Database connection resource
 * @return bool Returns true if the query was successful, false otherwise
 */
function logFailedLogin($username, $conn) {
    $sql = "UPDATE user_logins SET failedLogins = failedLogins + 1, locked = CASE WHEN failedLogins + 1 >= 4 THEN 1 ELSE locked END WHERE username = '$username'";

    return mysqli_query($conn, $sql);
}
/**
 * Summary of isAccountLocked
 * Checks if the account is locked by querying the user_logins table
 * @param mixed $username The username inserted by the login form
 * @param mixed $conn Database connection resource
 * @return bool
 */
function isAccountLocked($username, $conn) {
    $sql = "SELECT locked FROM user_logins WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if ($row['locked'] == 1) {
            $_SESSION['access'] = "denied"; // Set access to denied if account is locked
            $_SESSION['errormsg'] = "Your account is locked due to too many failed login attempts.";
            return true; // Account is locked
        }
    }

    return false; // default to unlocked if user not found
}
/**
 * Summary of isAdmin
 * Checks if the account is an Admin by querying the secureuser table
 * @param mixed $username The username inserted by the login form
 * @param mixed $conn Database connection resource
 * @return bool
 */
function isAdmin($username, $conn) {
    $sql = "SELECT isAdmin FROM secureusers WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        if ($row['isAdmin'] == 1) {
            $_SESSION['admin'] = "true"; 
        }
    }

    return false; // default to unlocked if user not found
}
/**
 * Retrieves the number of successful logins for the current user from the user_logins table
 * 
 * If the user is not found, it returns a message indicating no such user
 * @param mixed $conn Database connection resource
 */
function getSuccessfulLogins($conn) {
    
    $userKey = $_SESSION['user-key'];

    // Query to retrieve successfulLogins for the given username
    $sql = "SELECT successfulLogins FROM user_logins WHERE username = '$userKey'";
    $result = mysqli_query($conn, $sql);

    // Check if a row was returned
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        echo $row['successfulLogins'];
    } else {
        #echo "No such user found.";
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
    $query = "SELECT image FROM secureusers WHERE username = '$userKey'";
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

/**
 * Function to hash the username and password using a specific algorithm
 * 
 * This function is used to create a secure hash for the user's credentials
 * @param string $username The username input from the login form
 * @param string $password The password input from the login form
 * @return string The hashed password
 */
function nelstromoHash($username, $password)
{
    $hash1 = "4164616C656E65";
    $hash2 = "436C61697265";
    $hash3 = "4D696C61";
    $user = $hash1.$username.$password.$hash3;
    $user2 = $hash2.$user.$user.$hash3;
    $word = hash('sha512', $user2);

    return $word;
}
?>