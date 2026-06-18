<?php
/**
 * @file navbar.php
 * @description This file contains the navigation bar for the Hash Browns application.
 */

$page = basename($_SERVER['PHP_SELF']);

?>
<div class="nav-bar">
    <div class="nav-title">
        <h1>TOP SECRET ACCESS</h1>
        <?php if ($_SESSION['access'] == "granted"): ?>
            <div class="grantedScreen">
                <p class="access-granted"><strong>ACCESS GRANTED</strong></p>
            </div>
        <?php endif; ?>
    </div>
    <div class="nav-links">
        <ul class="links">
            <?php if ($_SESSION['access'] == "granted"): ?>
                <li><a href=".">Home</a></li>
                <li><a href="video.php">Video</a></li>
                <li><a href="users.php">Users</a></li>
                <li><a href="logout.php">Logout</a></li>
            <?php endif; ?>
        </ul>
    </div>
    <div class="nav-profile-Img">
        <?php if (!empty($_SESSION['profilePic'])): ?>
            <a href="account.php" class="profile-pic-wrapper">
                <img src="<?php echo $_SESSION['profilePic']; ?>" class="profile-pic">
            </a>
        <?php endif; ?>
    </div>
</div>

?>