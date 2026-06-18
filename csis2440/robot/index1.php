<?php
/**
 * @author Nelson Long
 * @description Module 6: Assignment #2 - I, Object
 * @version 1.0.0
 * @date
 * @file index.php
 * ------------------------------------------------
 * 
 * 
 * 
 */
session_start();
//----------------------------------------Initialize variables------------------------------------------------\\

$errormsg = "";
$robotpic = "";
$fileChoice = ""; // Default choice for the dropdown'';
$opsys = ""; // Default choice for the dropdown
$model = ""; // Default choice for the dropdown
$color = ""; // Default choice for the dropdown

 if (!isset($_SESSION['error'])) {
        $_SESSION['error'] = ''; 
    }
//---------------------------------------------------------------------------------------------------------------\\
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My New Project</title>
    <link rel="stylesheet" href="css/style.css"> <!-- Optional external CSS -->
</head>
<body>
    <main>
        <div class="container">

            <div class="page-header">
                    <h1 class="header">Welcome</h1>
                    <p class="msg">Create your robot below:</p>
            </div>

            <?php if ($_SESSION['error'] == "error"): ?>
                    <div class="deniedMSG">
                        <p class=denied-msg-alert><strong>ERROR</strong></p>
                        <p class="error-msg"><?php echo $errormsg ?></p>
                    </div class="deniedMSG">
            <?php endif; ?>

            <?php if (!$_SESSION['error']): ?>
                    
                <div class="robot-created">
                    <h2 class="robot-created-title">Congrats!</h2>
                    <p class="robot-created-msg">Robot Successfully Created</p>
                    <?php if (isset($robotpic)): ?>
                        <div class="robot-created-container">
                            <img src="<?php echo $robotpic; ?>" class="profile-pic-half">
                        <?php $robotpic = ""; ?>
                    </div>
                    <?php endif; ?>
                </div>
                
            <?php endif; ?>
            
            <form action="index.php" method="post" class="create-robot-form" enctype="multipart/form-data">

                    <label for="model">Model:</label>
                    <select id="fileSelection" name="fileSelection">
                            <option value="Sonny" <?php if ($fileChoice == 'FBI') echo 'selected'; ?>>Sonny</option>
                            <option value="Rosey" <?php if ($fileChoice == 'SPIES')echo 'selected'; ?>>Rosey</option>
                            <option value="Sico" <?php if ($fileChoice == 'FBI') echo 'selected'; ?>>Sico</option>
                            <option value="Data" <?php if ($fileChoice == 'SPIES')echo 'selected'; ?>>Data</option>
                            <option value="Gort" <?php if ($fileChoice == 'FBI') echo 'selected'; ?>>Gort</option>
                            <option value="Wall-E" <?php if ($fileChoice == 'SPIES')echo 'selected'; ?>>Wall-E</option>
                            <option value="Optimus Prime" <?php if ($fileChoice == 'FBI') echo 'selected'; ?>>Optimus Prime</option>
                            <option value="Hal 9000" <?php if ($fileChoice == 'SPIES')echo 'selected'; ?>>Hal 9000</option>
                            <option value="Twiki" <?php if ($fileChoice == 'FBI') echo 'selected'; ?>>Twiki</option>
                            <option value="Bender" <?php if ($fileChoice == 'SPIES')echo 'selected'; ?>>Bender</option>
                            <option value="Johnny 5" <?php if ($fileChoice == 'FBI') echo 'selected'; ?>>Johnny 5</option>
                    </select>

                    <label for="colors">Colors:</label>
                    <select id="fileSelection" name="fileSelection">
                            <option value="Shiny" <?php if ($fileChoice == 'FBI') echo 'selected'; ?>>Shiny</option>
                            <option value="Chrome" <?php if ($fileChoice == 'SPIES')echo 'selected'; ?>>Chrome</option>
                            <option value="Silver" <?php if ($fileChoice == 'FBI') echo 'selected'; ?>>Silver</option>
                            <option value="Brass" <?php if ($fileChoice == 'SPIES')echo 'selected'; ?>>Brass</option>
                            <option value="Gold" <?php if ($fileChoice == 'FBI') echo 'selected'; ?>>Gold</option>
                    </select>

                    <label for="opSys">Operating System:</label>
                    <select id="fileSelection" name="fileSelection">
                            <option value="Linux" <?php if ($fileChoice == 'FBI') echo 'selected'; ?>>Linux</option>
                            <option value="Tiny Hamsters" <?php if ($fileChoice == 'SPIES')echo 'selected'; ?>>Tiny Hamsters</option>
                            <option value="DOS" <?php if ($fileChoice == 'FBI') echo 'selected'; ?>>DOS</option>
                            <option value="Binary" <?php if ($fileChoice == 'SPIES')echo 'selected'; ?>>Binary</option>
                            <option value="Unix" <?php if ($fileChoice == 'FBI') echo 'selected'; ?>>Unix</option>
                            <option value="SPARC" <?php if ($fileChoice == 'FBI') echo 'selected'; ?>>SPARC</option>
                    </select>

                    <label for="profilePic">Choose a Profile Picture: (Optional)</label>
                    <input type="file" name="profilePic" id="profilePic">

                    <input type="submit" name="submit" value="Create Robot">
                    <button type="submit" name="reset" class="resetbtn">Reset</button>
                    
            </form>
        </div>
    </main>
</body>
</html>


<?php
class Robot 
{
    // Properties
    private $modelType;
    private $colorScheme;
    private $operatingSystem;

    public function __construct($model, $color, $opSys) {
        $this->setModel($model);
        $this->setColor($color);
        $this->setOS($opSys);
        
    }
    public function setModel($model) {$this->modelType = $model;}
    public function setColor($color) {$this->colorScheme = $color;}
    public function setOS($opsys) {$this->operatingSystem = $opsys;}


}