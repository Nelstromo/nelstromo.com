<?php
    session_start();
    // Declare variables for username and password ----------------------------------------
    /** @var string $username The username input from the login form.*/
    $username = $_POST['username'] ?? '';
    /** @var string $password The password input from the login form.*/
    $password = $_POST['password'] ?? '';
    /** @var string $confirmPassword  */
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    /** @var string $errormsg Behold an error */
    $errormsg = '';
    /** @var string $targetDir This variable holds the target directory for uploaded images.*/
    $targetDir = "img/";
    $accountCreated = false; // Flag to check if account is created successfully

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
    /** @var string $name The name of the user, retrieved from the session. */
    $name = $_SESSION['user-key'];

    // --------------------------------------------------------------------------------------

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Check if the form is submitted
        if (isset($_POST['submit'])) 
        {
            // Get the username and password from the form
            $username = $_POST['username'];
            $password = $_POST['password'];
            $confirmPassword = $_POST['confirmPassword'];

            if (!blankFields($username, $password, $confirmPassword, $errormsg)) 
            {
                // Check for duplicate user
                if (!duplicateUserCheck($username, $conn, $errormsg)) 
                {
                    // Check if passwords match
                    if (isPasswordConfirmed($password, $confirmPassword, $errormsg)) {
                        
                        addUserToDatabase($username, nelstromoHash($username,$password), $conn);
                        $_SESSION['error'] = ''; // Clear error message
                        $accountCreated = true; 
                    }
                }
            }
        } elseif (isset($_POST['reset'])) 
        {
            // Reset the form fields
            $username = '';
            $password = '';
            $confirmPassword = '';
            $_SESSION['error'] = ''; // Clear error message
            $accountCreated = false; 
        }

        $file = $_FILES['profilePic'];
        if (isset($file) && $file['error'] === UPLOAD_ERR_OK) {
    profilepictureUpload($file, $targetDir, $username, $conn);
} elseif ($file['error'] === UPLOAD_ERR_NO_FILE && $accountCreated) {
    // No file was uploaded; set default image
    $defaultPic = "img/generic.jpg";
    $safePath = mysqli_real_escape_string($conn, $defaultPic);
    $updateQuery = "UPDATE secureusers SET image = '$safePath' WHERE username = '$username'";
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
    <title>TOP SECRET ACCESS</title>
    <link rel="stylesheet" href="css/style.css" type="text/css">
</head>
<body>
    <main>
        <nav>
            <?php include 'includes/navbar.php'; ?>
        </nav>

        <div class="container">

            <div class="page-header">
                    <h1 class="header">Welcome</h1>
                    <p class="msg">Create an account below:</p>
            </div>

            <?php if ($_SESSION['error'] == "error"): ?>
                    
                    <div class="deniedMSG">
                    <p class=denied-msg-alert><strong>ERROR</strong></p>
                    <p class="error-msg"><?php echo $errormsg ?></p>
                    </div class="deniedMSG">
                
            <?php endif; ?>

            <?php if ($accountCreated): ?>
                    
                <div class="account-created">
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

                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" value="<?php echo $username; ?>" >

                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" value="<?php echo $password; ?>" >

                    <label for="confirmPassword">Confirm Password:</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" value="<?php echo $confirmPassword; ?>">

                    <label for="profilePic">Choose a Profile Picture: (Optional)</label>
                    <input type="file" name="profilePic" id="profilePic">

                    <input type="submit" name="submit" value="Create account">
                    <button type="submit" name="reset" class="resetbtn">Reset</button>
                    <?php if (empty($_SESSION['access'])) echo '<p class="account-msg">Already have an account? <a href=".">Login</a></p>'; ?>
            </form>
        </div>
    </main>
</body>
</html>






<?php
/**
 * Summary of capitalizeFirstLetter
 * This function capitalizes the first letter of a given name.
 * @param string $name The name of the user, retrieved from the session
 * @return mixed
 */
function capitalizeFirstLetter($name) 
{
    return preg_replace_callback('/^[a-z]/', function ($matches) 
    {
        return strtoupper($matches[0]);
    }, $name);
}

/**
 * Returns the name of the current page.
 * This function retrieves the name of the current page from the server's PHP_SELF variable.
 * @return string
 */
function returnPageName() 
{
    $page = basename($_SERVER['PHP_SELF']);
    return $page;
}

function addUserToDatabase($username, $password, $conn) 
{
    // Directly insert into database (insecure)
    $sql = "INSERT INTO secureusers (username, passcode) VALUES ('$username', '$password')";

    if (mysqli_query($conn, $sql)) {
        //echo "User added successfully.";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
    #if (mysqli_query($conn, $sql2)) {
        //echo "User login record added successfully.";
    #} else {
        #echo "Error: " . mysqli_error($conn);
    #}
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
    $sql = "SELECT * FROM secureusers WHERE username = '$username'";
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
    $generic = '';

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
                    $sql = "UPDATE secureusers SET image = '$path' WHERE username = '$username'";
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
