<?php
    session_start();
    if ($_SESSION['access'] !== 'granted') {
        header('Location: .'); // Redirect to login if access is not granted
        exit();
    }

    
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

    if (!isset($_SESSION['user-key'])) {
        $_SESSION['user-key'] = '';
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
            <div class="account-header">
                    <h2 class="account-header-msg1">Welcome Agent, <?php echo capitalizeFirstLetter($_SESSION['user-key']); ?>!</h2>
                    
                    <p>Here is a complete list of the User Credentials for this system.</p>
            </div>
            <div class="table-section">
                <?php displayUsersTable($conn); ?>
            </div>
        </div>
    </main>
</body>
</html>


<?php

function capitalizeFirstLetter($name) {
    return preg_replace_callback('/^[a-z]/', function ($matches) {
        return strtoupper($matches[0]);
    }, $name);
}

function displayUsersTable($conn) {
    $sql = "SELECT username, passcode FROM secureusers";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        echo "<table class='infoTable'>";
        echo "<tr><th>Username</th><th>Passcode</th></tr>";

        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr><td>" . htmlspecialchars($row['username']) . "</td><td>" . htmlspecialchars($row['passcode']) . "</td></tr>";
        }

        echo "</table>";
    } else {
        echo "<p>No users found in the database.</p>";
    }
}
?>