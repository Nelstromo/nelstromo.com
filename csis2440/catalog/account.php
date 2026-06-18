<?php
    session_start();
    if ($_SESSION['access'] !== 'granted') {
        header('Location: .'); // Redirect to login if access is not granted
        exit();
    }
    $name = $_SESSION['user-key'];
    // -----------------------Set Database Credentials-----------------------------------
    include_once 'includes/database.php';
    //-----------------------------------------------------------------------------------

    // -----------------------Include Common Functions-----------------------------------
    include_once 'includes/functions.php';
    //-----------------------------------------------------------------------------------

    playGuestThemeIfNeeded()
?>

<!doctype html>
<html lang="en">
<head>
    <title>ACCOUNT</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="css/style.css" type="text/css">
</head>
<body class="hero-gradient">
    
    <main>
        <nav>
            <?php include 'includes/navbar.php'; ?>
        </nav>
        <div class="container"> 
            <div class="page-header">
                <h2 class="page-title">Welcome, <?php echo capitalizeFirstLetter($_SESSION['user-key']); ?>!</h2>
                <p class="page-subtitle">We thank you for shopping with us</p>
            </div>
            
            <div class="page-body-image-wrapper">
                <img src="<?php echo $_SESSION['profilePic']; ?>" class="profile-pic-full">
            </div>

            <div class="page-body">
                <h2 class="page-title">Account Information</h2>
                <p><strong><u>Username:</u></strong> <?php echo capitalizeFirstLetter($_SESSION['user-key']); ?></p>
                <!--<p><strong><u>Access Level:</u></strong> <?php #($_SESSION['admin']) ? "Administrator" : "Agent"; ?></p> -->
            </div>
        </div>
    </main>
</body>
</html>

<?php

/**
 * Function to play a guest theme if the user is a guest
 * This function checks if the current page is 'account.php' and if the user-key is 'guest'
 * If both conditions are met, it plays a specific audio file and provides controls for muting and volume adjustment
 */
function playGuestThemeIfNeeded() {
    $currentPage = basename($_SERVER['PHP_SELF']);

    if ($currentPage === 'account.php' && isset($_SESSION['user-key']) && $_SESSION['user-key'] === 'guest') {
        echo <<<HTML
            <audio id="guestAudio" autoplay loop>
                <source src="includes/soda-pop.mp3" type="audio/mpeg">
            </audio>

            <div id="audioControls" style="
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: rgba(0, 0, 0, 0.5);
                padding: 5px 10px;
                border-radius: 8px;
                display: flex;
                align-items: center;
                gap: 8px;
                z-index: 9999;
            ">
                <button id="muteBtn" style="
                    background: none;
                    border: none;
                    color: white;
                    font-size: 1.2em;
                    cursor: pointer;
                ">🔊</button>
                <input type="range" id="volumeSlider" min="0" max="1" step="0.01" value="1" style="width: 80px;">
            </div>

            <script>
                const audio = document.getElementById('guestAudio');
                const muteBtn = document.getElementById('muteBtn');
                const volumeSlider = document.getElementById('volumeSlider');

                muteBtn.addEventListener('click', () => {
                    audio.muted = !audio.muted;
                    muteBtn.textContent = audio.muted ? '🔇' : '🔊';
                });

                volumeSlider.addEventListener('input', () => {
                    audio.volume = volumeSlider.value;
                    audio.muted = audio.volume == 0;
                    muteBtn.textContent = audio.muted ? '🔇' : '🔊';
                });
            </script>
        HTML;
    }
}
