<?php
    session_start();
    if ($_SESSION['access'] !== 'granted') {
        header('Location: .'); // Redirect to login if access is not granted
        exit();
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
            <div class="video-header">
                <h2 class="video-header-msg1">Secret Uncovered Propaganda Video</h2>
                <p>May the memory of the heroes that fell getting this info for us never falter</p>
            </div>
            
            <div class="video-container">
                <iframe width="560" height="315" src="https://www.youtube.com/embed/hWTFG3J1CP8" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
            </div>
        </div>
    </main>
</body>
</html>


