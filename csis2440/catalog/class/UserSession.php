<?php 
/**
 * @author Nelson Long
 * @version 4.1.3   
 * @description We will create a session manager that will handle the session creation, destruction, and validation.
 * 
 * 
 * current Session variables are:
 * $_SESSION['profilePic']
 * $_SESSION['user-key']
 * $_SESSION['admin'] 
 * $_SESSION['access'] - Is Access Granted after login If true then = granted, else = denied
 * 
 */

class UserSession {


    private $access;

    private $userKey;
    private $profilePic;
    private $admin;


/*
public function __construct($userKey, $profilePic, $admin, $access) {
    $this->userKey = $userKey;
    $this->profilePic = $profilePic;
    $this->admin = $admin;
    $this->access = $access;

    // Initialize session variables
    $_SESSION['user-key'] = $userKey;
    $_SESSION['profilePic'] = $profilePic;
    $_SESSION['admin'] = $admin;
    $_SESSION['access'] = $access ? 'granted' : 'denied';
}
*/
// ----------------------------[Setters]---------------------------- \\ 
/**
 * Summary of setUserKey
 * @param mixed $username The username from the login form on index.php
 * @return void
 */
public function setUserKey($username)
{
    $this->userKey = $username;
    $_SESSION['user-key'] = $username;
}

/**
 * Summary of setProfilePic
 * This function retrieves the user's profile picture from the database and sets it in the session.
 * It queries the user_account_detail table for the image associated with the username stored in the session
 * @param mysqli $conn The database connection object
 */
public function setProfilePic($conn)
{
    $userKey = $_SESSION['user-key']; // Get the user key from the session

    $query = "SELECT image FROM user_account_detail WHERE username = '$userKey'";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['profilePic'] = $row['image'];
        $this->profilePic = $row['image'];
        
    }
}
public function setAdminStatus($admin)
{
    $this->admin = $admin;
    $_SESSION['admin'] = $admin;
}


public function setAccessGranted()
{
    $this->access = 'granted';
    $_SESSION['access'] = 'granted';
}

public function setAccessDenied()
{
    $this->access = 'denied';
    $_SESSION['access'] = 'denied';
}


// ----------------------------[Getters]---------------------------- \\
/** Get the users access status via checking the credentials in the database*/

public function getUserKey()
{
    return $this->userKey;
}

public function getProfilePic()
{
    return $this->profilePic;
}

/** Get the users access status via checking the credentials in the database*/
public function getAccessStatus()
{
    return $this->access;
}

public function getAdminStatus()
{
    return $this->admin;
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




//-------------------------------------------Test Function-------------------------------
/**
 * Summary of add
 * @param mixed $a first number to add
 * 
 * @param mixed $b second number to add
 * 
 */
public function add($a, $b) {
        return $a + $b;
    }




}