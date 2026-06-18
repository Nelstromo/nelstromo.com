<?php
session_start();
if ($_SESSION['access'] == 'granted') {
    header('Location: .');
    exit();
}
/**
 * @file create-account.php
 * @brief This file handles the creation of a new user account.
 * It includes form handling, validation, and database interactions.
 * It also manages the upload of a profile picture and displays appropriate messages.
 * 
 */
//---------------- Declare variables for username and password ----------------------------------------
/** @var string $username The username input from the login form.*/
$username = $_POST['username'] ?? '';
/** @var string $password The password input from the login form.*/
$password = $_POST['password'] ?? '';
/** @var string $confirmPassword  */
$confirmPassword = $_POST['confirmPassword'] ?? '';
/** @var string $errormsg Behold an error */
$errormsg = '';
/** @var string $targetDir This variable holds the target directory for uploaded images.*/
$targetDir = "img/profilepics/";
$accountCreated = false; // Flag to check if account is created successfully

// -----------------------Set Database Credentials-----------------------------------
include_once 'includes/database.php';
//-----------------------------------------------------------------------------------
include_once 'includes/functions.php';



// ------------------Session Variables------------------------------------------------
if (!isset($_SESSION['error'])) {
    $_SESSION['error'] = '';
}
if (!isset($_SESSION['profilePic'])) {
    $_SESSION['profilePic'] = '';
}
if (!isset($_SESSION['access'])) {
    $_SESSION['access'] = '';
}
if (!isset($_SESSION['user-key'])) {
    $_SESSION['user-key'] = '';
}
$name = $_SESSION['user-key'];

// --------------------------------------------------------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the form is submitted
    if (isset($_POST['submit'])) {
        // Get the username and password from the form
        $username = $_POST['username'];
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirmPassword'];

        if (!blankFields($username, $password, $confirmPassword, $errormsg)) {
            // Check for duplicate user
            if (!duplicateUserCheck($username, $conn, $errormsg)) {
                // Check if passwords match
                if (isPasswordConfirmed($password, $confirmPassword, $errormsg)) {

                    addUserToDatabase($username, nelstromoHash($username, $password), $conn);
                    $_SESSION['error'] = '';
                    $accountCreated = true;
                }
            }
        }
    } elseif (isset($_POST['reset'])) {

        $username = '';
        $password = '';
        $confirmPassword = '';
        $_SESSION['error'] = '';
        $accountCreated = false;
    }

    $file = $_FILES['profilePic'];
    if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
        profilepictureUpload($file, $targetDir, $username, $conn);
    } elseif ($file['error'] === UPLOAD_ERR_NO_FILE && $accountCreated) {
        // No file was uploaded; set default image
        $defaultPic = "img/generic.jpg";
        $safePath = mysqli_real_escape_string($conn, $defaultPic);
        $updateQuery = "UPDATE user_account_detail SET image = '$safePath' WHERE username = '$username'";
        mysqli_query($conn, $updateQuery);
        $_SESSION['createdProfilePic'] = $defaultPic;
    } else {
        // Handle other file upload errors
        if ($file['error'] !== UPLOAD_ERR_NO_FILE) {
            $errormsg = "File upload error: " . $file['error'];
            $_SESSION['error'] = "error";
        }
    }
}



?>

<!doctype html>
<html lang="en">

<head>
    <title>Nelstromo's Cosmic Wonders</title>
    <link rel="stylesheet" href="css/style.css" type="text/css">
    <script defer src="js/createValidLogin.js"></script>
    <script defer src="js/fileupload.js"></script>
</head>

<body class="hero-gradient">
    <main>
        <nav>
            <?php include 'includes/navbar.php'; ?>
        </nav>

        <div class="container-hide">

            <div class="page-header">
                <h2 class="page-title">Welcome</h1>
                    <p class="page-subtitle">Create an account below:</p>
            </div>

            <?php if ($_SESSION['error'] == "error"): ?>
                <div class="page-header">
                    <p class=denied-msg-alert><strong>ERROR</strong></p>
                    <p class="error-msg"><?php echo $errormsg ?></p>
                </div>
            <?php endif; ?>

            <?php if ($accountCreated): ?>

                <div class="page-header">
                    <?php /*$_SESSION['access'] = "granted";
                    $_SESSION['user-key'] = $username;
                    $_SESSION['profilePic'] = $_SESSION['createdProfilePic'] ?? '';
                    $_SESSION['createdProfilePic'] = ""; 

                    if ($_SESSION['access'] == 'granted') {
                        header('Location: .');
                        exit();
                    }*/ ?>

                    <h2 class="account-created-title">Welcome Agent, <?php echo capitalizeFirstLetter($username); ?>!</h2>
                    <p class="account-created-msg">Account Successfully Created</p>
                    <?php if (isset($_SESSION['createdProfilePic'])): ?>
                        <div class="account-created-container">
                            <img src="<?php echo $_SESSION['createdProfilePic']; ?>" class="profile-pic-half">
                            <?php $_SESSION['createdProfilePic'] = ""; ?>
                        </div>
                    <?php endif; ?>
                </div>

            <?php endif; ?>


            <form action="create-account.php" method="post" class="create-account-form" enctype="multipart/form-data">

                <div class="input-group">
                    <input type="text" id="username" name="username" placeholder=" " required autocomplete="off" value="<?php echo htmlspecialchars($username); ?>">
                    <label for="username">Username</label>
                </div>

                <div class="pass-label-box"><span class="requirement-hint-pass">Must contain 8+ characters and >= 1 number</span></div>
                <div class="input-group">
                    <input type="password" id="password" name="password" placeholder=" " required autocomplete="new-password" value="<?php echo htmlspecialchars($password); ?>">
                    <label for="password">Password</label>
                </div>

                <div class="pass-label-box"><span class="requirement-hint-conf">Must match password</span></div>
                <div class="input-group">
                    <input type="password" id="confirmPassword" name="confirmPassword" placeholder=" " required autocomplete="new-password" value="<?php echo htmlspecialchars($confirmPassword); ?>">
                    <label for="confirmPassword">Confirm Password</label>
                </div>

                <div class="input-group-file">
                    <input type="file" name="profilePic" id="profilePic" accept="image/*">

                    <label for="profilePic" class="file-cta">
                        <svg aria-hidden="true" viewBox="0 0 24 24" class="file-ico">
                            <path d="M14.5 2a3.5 3.5 0 0 1 2.475 1.025l3.999 4A3.5 3.5 0 0 1 21.999 9.5V18a4 4 0 0 1-4 4h-12a4 4 0 0 1-4-4V6a4 4 0 0 1 4-4h8.5Zm0 2H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9.5a1.5 1.5 0 0 0-.44-1.06l-4-4A1.5 1.5 0 0 0 14.5 4ZM12 8a1 1 0 0 1 1 1v2h2a1 1 0 1 1 0 2h-2v2a1 1 0 1 1-2 0v-2H9a1 1 0 1 1 0-2h2V9a1 1 0 0 1 1-1Z" />
                        </svg>
                        <span>Choose a Profile Picture (Optional)</span>
                    </label>

                    <div class="file-meta">
                        <span id="profilePicName" class="file-name">No file chosen</span>
                        <span class="file-hint">(PNG/JPG/GIF, up to 25MB)</span>
                    </div>

                    <img id="profilePreview" class="file-preview" alt="Profile preview" hidden>
                </div>


                <br>
                <input type="submit" name="submit" class="submitbtn" value="Create account" disabled>
                <button type="submit" name="reset" class="resetbtn">Reset</button>
                <?php if (empty($_SESSION['access'])) echo '<p class="account-msg">Already have an account? <a href=".">Login</a></p>'; ?>
            </form>
        </div>
    </main>
</body>

</html>






<?php


function addUserToDatabase($username, $password, $conn)
{

    $sql = "INSERT INTO user (username, passcode) VALUES ('$username', '$password')";

    if (mysqli_query($conn, $sql)) {
        //echo "User added successfully.";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

/**
 * Summary of blankFields
 * 
 * This function checks if any of the fields (username, password, confirm password) are blank.
 * If any field is blank, it updates the error message and returns true.
 * If all fields are filled, it returns false.
 * 
 * @param mixed $username The username input from the login form.
 * @param mixed $password The password input from the login form.
 * @param mixed $confirmPassword The confirm password input from the login form.
 * @param mixed $errormsg Update the error message if fields are blank
 * @return bool
 */
function blankFields($username, $password, $confirmPassword, &$errormsg)
{
    // Check if any of the fields are blank
    if (empty($username) || empty($password) || empty($confirmPassword)) {
        $errormsg = "Missing Required Field ";
        $_SESSION['error'] = "error"; // Set error message in session
        return true; // Fields are blank
    } else {
        return false; // Fields are not blank
    }
}

/**
 * Checks if a user already exists in the database. And throws an error if it does.
 * @param mixed $username The username to check for duplicates.
 * @param mixed $conn The database connection.
 * @param mixed $errormsg Update the error message if user already exists
 * @return bool
 */
function duplicateUserCheck($username, $conn, &$errormsg)
{
    $sql = "SELECT * FROM user WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $errormsg = "User already exists.";
        $_SESSION['error'] = "error"; // Set error message in session
        return true; // User already exists
    } else {
        return false; // User does not exist
    }
}

function isPasswordConfirmed($password, $confirmPassword, &$errormsg)
{
    if ($password === $confirmPassword) {
        return true; // Passwords match
    } else {
        $errormsg = "Passwords do not match.";
        $_SESSION['error'] = "error"; // Set error message in session
        return false; // Passwords do not match
    }
}

function profilepictureUpload($file, $targetDir, $username, $conn)
{
    // Basic data
    $fileName = basename($file['name']);
    $fileTmp = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];

    // Limit types 
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (in_array($fileExt, $allowed)) {
        if ($fileError === 0) {
            if ($fileSize < 25 * 1024 * 1024) { // 5MB max
                $newFileName = $username . "." . $fileExt;
                $fileDestination = $targetDir . $newFileName;

                if (move_uploaded_file($fileTmp, $fileDestination)) {
                    $_SESSION['createdProfilePic'] = $fileDestination;

                    $path = mysqli_real_escape_string($conn, $fileDestination);
                    $sql = "UPDATE user_account_detail SET image = '$path' WHERE username = '$username'";
                    mysqli_query($conn, $sql);

                    // Redirect or show the image
                    #echo "<img src='$fileDestination' class='preview-img'>";
                } else {
                    $_SESSION['error'] = "Failed to move uploaded file.";
                }
            } else {
                $_SESSION['error'] = "File too large.";
            }
        } else {
            $_SESSION['error'] = "Upload error: $fileError";
        }
    } else {
        $_SESSION['error'] = "Invalid file type. Only JPG, PNG, GIF allowed.";
    }
}




?>