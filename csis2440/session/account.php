<?php
    session_start();
    if ($_SESSION['access'] !== 'granted') {
        header('Location: .'); // Redirect to login if access is not granted
        exit();
    }
    $name = $_SESSION['user-key'];

    playGuestThemeIfNeeded()
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
                   
                </div>
            </div>
        </nav>
        <div class="container"> 
            <div class="account-header">
                <h2 class="account-header-msg1">Welcome Agent, <?php echo capitalizeFirstLetter($_SESSION['user-key']); ?>!</h2>
                <p>We thank you for your service</p>
            </div>
            
            <div class="account-container">
                <img src="<?php echo $_SESSION['profilePic']; ?>" class="profile-pic-full">
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
