<?php

/**
 * @author Nelson Long
 * @version 4.1.3 
 * @description This is the revamp of the assignment for CSIS 2440, Module 9: Assignment #1 - Final Project
 * 
 * ------------------//PHP DOCUMENTATION ---- TO MAKE MY LIFE EASIER\\--------------------------------------------
 * @var string $username The username input from the login form. Used to update the session username. Updated via $_POST
 * @var string $password The password input from the login form. Updated via $_POST
 */
session_start();

// ---------------Initialize variables ---------------------------------------------
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

// -----------------------Set Database Credentials-----------------------------------
include_once 'includes/database.php';
//-----------------------------------------------------------------------------------

// -----------------------Include Common Functions-----------------------------------
include_once 'includes/functions.php';
//-----------------------------------------------------------------------------------

// -----------------------Set UserSession Class-----------------------------------
include_once 'class/UserSession.php';
//-----------------------------------------------------------------------------------

// ------------------- Initialize SESSION variables ----------------------------------

if (!isset($_SESSION['profilePic'])) {
    $_SESSION['profilePic'] = ''; // Default profile picture
}

if (!isset($_SESSION['access'])) {
    $_SESSION['access'] = '';
}

if (!isset($_SESSION['errormsg'])) {
    $_SESSION['errormsg'] = '';
}

if (!isset($_SESSION['wealth']) || !is_numeric($_SESSION['wealth'])) {
    $_SESSION['wealth'] = 0.00;
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
    <title>Nelstromos Cosmic Wonders</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="css/style.css" type="text/css">
    <link rel="stylesheet" href="css/pong.css" type="text/css">

</head>

<body class="hero-gradient">
    <main>
        <nav>
            <?php include 'includes/navbar.php'; ?>
        </nav>

        <div class="container-hide">
            <?php if ($_SESSION['access'] == "" | $_SESSION['access'] == "denied"): ?>

                <div class="page-header">
                    <h2 class="page-title">Welcome</h2>
                    <p class="page-subtitle">Please enter your Username and Password</p>
                </div>


                <?php if ($_SESSION['access'] == "denied"): ?>

                    <div class="page-header">
                        <p class=denied-msg-alert><strong>ACCESS DENIED</strong></p>
                        <p class="denied-msg"><?php echo $_SESSION['errormsg']; ?></p>
                    </div class="deniedMSG">

                <?php endif; ?>

                <form action="index.php" method="post" class="login-form">

                    <div class="input-group">
                        <input type="text" id="username" name="username" placeholder=" " required autocomplete="off" value="<?php echo htmlspecialchars($username); ?>">
                        <label for="username">Username</label>
                    </div>

                    <div class="input-group">
                        <input type="password" id="password" name="password" placeholder=" " required autocomplete="new-password" value="<?php echo htmlspecialchars($password); ?>">
                        <label for="password">Password</label>
                    </div>

                    <input type="submit" name="submit" value="Login">
                    <button type="submit" name="reset" class="resetbtn">Reset</button>
                    <p class="account-msg">Don't have an account? <a href="create-account.php">Create Account</a></p>
                </form>
            <?php elseif ($_SESSION['access'] == "granted") : ?>
                <div class="page-header">
                    <h2 class="page-title">Welcome, <?php echo capitalizeFirstLetter($_SESSION['user-key']); ?>!</h2>

                    <p class="page-subtitle"> Would you like to play a game? </p>
                    <p class="page-subtitle"> Each return of the pong gets you money! And its free to play!</p>
                </div>

                <div class="pong" id="pong">
                    <div class="pong-hud">
                        <div class="pong-stat">Time: <strong id="pongTime">30</strong>s</div>
                        <div class="pong-stat">Bounces: <strong id="pongBounces">0</strong></div>
                        <div class="pong-stat">Credits: <strong id="pongCredits">0</strong></div>
                        <button id="pongStart" class="pong-btn">Start</button>
                    </div>

                    <div class="pong-court" id="pongCourt" aria-label="Pong game area">
                        <div class="pong-net"></div>
                        <div class="pong-paddle" id="pongPaddle" aria-label="Paddle"></div>
                        <div class="pong-ball" id="pongBall" aria-label="Ball" role="img"></div>
                        <div class="pong-overlay" id="pongOverlay" hidden>
                            <div class="pong-card" hidden>
                                <h3>Round Over</h3>
                                <p><span id="ovBounces">0</span> bounces • <span id="ovCredits">0</span> credits</p>
                                <button id="pongAgain" class="pong-btn">Play again</button>
                            </div>
                        </div>
                    </div>

                    <div class="pong-hint">Drag / touch to move. Keys: ← →</div>
                </div>
                <script src="js/pong.js" defer></script>


            <?php else: ?>
                <div class="error-message">
                    <h2>Error</h2>
                    <p class="error-subtitle">An unexpected error occurred. Please try again later.</p>
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
    #    if (!isAccountLocked($username, $conn))  // Check if account is locked
    # {

    $query = "SELECT * FROM user WHERE  username = '$username' AND passcode = '$password'";
    $result = mysqli_query($conn, $query);

    if ($result) {
        if (mysqli_num_rows($result) > 0) {
            #echo 'This user exists';
            // Login success
            $user = new UserSession;
            $user->setAccessGranted();
            $user->setUserKey($username);
            #$user->setAdminStatus($username, $conn); // Not built yet, used to be function isAdmin
            $user->setProfilePic($conn); // Used to be the function setProfilePic
            #logSuccessfulLogin($username, $conn); // Not built yet
        } else {
            // Login failed
            $user = new UserSession;
            $user->setAccessDenied();
            $_SESSION['errormsg'] = "Invalid username/password!";
            #logFailedLogin($username, $conn); // Not built yet
        }
    } else {
        echo 'Query failed: ' . mysqli_error($conn);
    }
    #        } else {
    #            // If account is locked, set access to denied
    #            $_SESSION['access'] = "denied"; 
    #            $_SESSION['errormsg'] = "Your account is locked due to too many failed login attempts.";
    #        }


}

/** Summary of logSuccessfulLogin
 * If run, it will update the user_account_detail table with the last login date and increment the successful logins count
 * @param mixed $username The username inserted by the login form
 * @param mixed $conn Database connection resource
 * 
 */
#function logSuccessfulLogin($username, $conn) {
#  $sql = "UPDATE user_account_detail SET lastlogindate = NOW(), successfulLogins = successfulLogins + 1, failedLogins = 0 WHERE username = '$username'";

#   return mysqli_query($conn, $sql);
#}

/**
 * Summary of logFailedLogin
 * If run, it will update the user_account_detail table with the failed login count and lock the account if it reaches 4 failed attempts
 * @param mixed $username The username inserted by the login form
 * @param mixed $conn Database connection resource
 * @return bool Returns true if the query was successful, false otherwise
 */
#function logFailedLogin($username, $conn) {
#    $sql = "UPDATE user_account_detail SET failedLogins = failedLogins + 1, locked = CASE WHEN failedLogins + 1 >= 4 THEN 1 ELSE locked END WHERE username = '$username'";
#
#    return mysqli_query($conn, $sql);
#}

/**
 * Summary of isAccountLocked
 * Checks if the account is locked by querying the user_account_detail table
 * @param mixed $username The username inserted by the login form
 * @param mixed $conn Database connection resource
 * @return bool
 */
#function isAccountLocked($username, $conn) {
#    $sql = "SELECT locked FROM user_account_detail WHERE username = '$username'";
#    $result = mysqli_query($conn, $sql);
#
#    if ($result && mysqli_num_rows($result) > 0) {
#        $row = mysqli_fetch_assoc($result);
#        if ($row['locked'] == 1) {
#            $_SESSION['access'] = "denied"; // Set access to denied if account is locked
#           $_SESSION['errormsg'] = "Your account is locked due to too many failed login attempts.";
#            return true; // Account is locked
#        }
#    }
#
#    return false; // default to unlocked if user not found
#}

/**
 * Summary of isAdmin
 * Checks if the account is an Admin by querying user_account_detail table
 * @param mixed $username The username inserted by the login form
 * @param mixed $conn Database connection resource
 * @return bool
 */
#function isAdmin($username, $conn) {
#    $sql = "SELECT isAdmin FROM user_account_detail WHERE username = '$username'";
#    $result = mysqli_query($conn, $sql);

#    if ($result && mysqli_num_rows($result) > 0) {
#        $row = mysqli_fetch_assoc($result);
#        if ($row['isAdmin'] == 1) {
#            $_SESSION['admin'] = true; 
#        }else {
#            $_SESSION['admin'] = false; // Not an admin
#        }
#    }
#
#    return false; // default to unlocked if user not found
#}
/**
 * Retrieves the number of successful logins for the current user from the user_account_detail table
 * 
 * If the user is not found, it returns a message indicating no such user
 * @param mixed $conn Database connection resource
 */
#function getSuccessfulLogins($conn) {
#    
#    $userKey = $_SESSION['user-key'];
#
#    // Query to retrieve successfulLogins for the given username
#    $sql = "SELECT successfulLogins FROM user_account_detail WHERE username = '$userKey'";
#    $result = mysqli_query($conn, $sql);
#
#    // Check if a row was returned
#    if ($result && mysqli_num_rows($result) > 0) {
#        $row = mysqli_fetch_assoc($result);
#        echo $row['successfulLogins'];
#    } else {
#        #echo "No such user found.";
#    } 
#}


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

?>