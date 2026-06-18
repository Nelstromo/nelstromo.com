<?php
/**
 * @author Nelson Long
 * @version 6.2.3
 * @description This is the assignment for CSIS 2440, Module 6: Assignment #2 - I, Object
 * 
 * 
 */
// -------------------Initialize variables--------------------------------------------
$errormsg = "";
$model = "";
$color = "";
$opsys = "";
$size = "";
$laws = [];
$robot = null;


class Robot 
{
    /** @var string Robot model name */
    private $modelType;
    
    /** @var string Robot's color scheme */
    private $colorScheme;
    
    /** @var string Operating system of the robot */
    private $operatingSystem;
    
    /** @var string File path to robot's image */
    private $imagePath;
    
    /** @var string Size of the robot (Giant, Normal, Nano) */
    private $size;
    
    /** @var array Array of robotic laws included in the robot */
    private $laws;

    /**
     * Constructor to create a new Robot object
     *
     * @param string $model Robot model
     * @param string $color Color scheme
     * @param string $opSys Operating system
     * @param string $image Path to image
     * @param string $size Robot size
     * @param array $laws Array of selected laws
     */
    public function __construct($model, $color, $opSys, $image, $size, $laws) {
        $this->setModel($model);
        $this->setColor($color);
        $this->setOS($opSys);
        $this->setImage($image);
        $this->setSize($size);
        $this->setLaws($laws);
    }

    /** @param string $model */
    public function setModel($model) { $this->modelType = $model; }

    /** @param string $color */
    public function setColor($color) { $this->colorScheme = $color; }

    /** @param string $opsys */
    public function setOS($opsys) { $this->operatingSystem = $opsys; }

    /** @param string $image */
    public function setImage($image) { $this->imagePath = $image; }

    /** @param string $size */
    public function setSize($size) { $this->size = $size; }

    /** @param array $laws */
    public function setLaws($laws) { $this->laws = $laws; }

    /** @return string */
    public function getModel() { return $this->modelType; }

    /** @return string */
    public function getColor() { return $this->colorScheme; }

    /** @return string */
    public function getOS() { return $this->operatingSystem; }

    /** @return string */
    public function getImage() { return $this->imagePath; }

    /** @return string */
    public function getSize() { return $this->size; }

    /** @return array */
    public function getLaws() { return $this->laws; }

    /**
     * String representation of the Robot object
     *
     * @return string HTML-formatted message with robot details and image
     */    public function __toString() {
        $msg = "Your {$this->getSize()} {$this->getColor()} {$this->getModel()} robot running {$this->getOS()} will be built shortly. Thank you.<br><br>";
        $msg .= "<img src='{$this->getImage()}' alt='Robot Image' style='max-width:300px;'><br><br>";

        if (!empty($this->laws)) {
            $msg .= "<strong>Included Laws of Robotics:</strong><ul>";
            foreach ($this->laws as $law) {
                $msg .= "<li>$law</li>";
            }
            $msg .= "</ul>";
        } else {
            $msg .= "<p><em>No laws of robotics selected.</em></p>";
        }

        return $msg;
    }
}

$imageMap = [
    'Sonny' => 'img/sonny.jpg', //
    'Rosey' => 'img/rosey.jpg', //
    'Sico' => 'img/sico.jpg', //
    'Data' => 'img/data.jpg', //
    'Gort' => 'img/Gort.jpg', //
    'Wall-E' => 'img/walle.png', //
    'Optimus Prime' => 'img/optimus.png', //
    'Hal 9000' => 'img/hal9000.jpg', //
    'Twiki' => 'img/twiki.jpg', //
    'Bender' => 'img/bender.jpg', //
    'Johnny 5' => 'img/johnny5.jpeg', //
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    $model = $_POST['model'] ?? '';
    $color = $_POST['color'] ?? '';
    $opsys = $_POST['opsys'] ?? '';
    $size = $_POST['size'] ?? '';
    $laws = $_POST['laws'] ?? [];

    if ($model && $color && $opsys && $size) {
        $image = $imageMap[$model] ?? 'img/default.jpg';
        $robot = new Robot($model, $color, $opsys, $image, $size, $laws);
    } else {
        $errormsg = "All fields (except laws) are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Robot Creator</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <main>
        <div class="container">
            <div class="page-header">
                <h1>Robot Factory</h1>
                <p>Design your custom robot</p>
            </div>

            <?php if ($robot): ?>
                <div class="robot-created">
                    <h2>Robot Created!</h2>
                    <p><?= $robot->__toString() ?></p>
                    <p><?php var_dump($robot); ?></p>
                </div>
            <?php else: ?>
                <form action="index.php" method="post" class="create-robot-form">
                   
                    <label for="model">Model:</label>
                    <select id="model" name="model" required>
                        <?php foreach ($imageMap as $key => $val): ?>
                            <option value="<?= $key ?>" <?= ($model == $key) ? 'selected' : '' ?>><?= $key ?></option>
                        <?php endforeach; ?>
                    </select>


                    <label for="color">Color:</label>
                    <select id="color" name="color" required>
                        <?php foreach (['Shiny', 'Chrome', 'Silver', 'Brass', 'Gold'] as $c): ?>
                            <option value="<?= $c ?>" <?= ($color == $c) ? 'selected' : '' ?>><?= $c ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label for="opsys">Operating System:</label>
                    <select id="opsys" name="opsys" required>
                        <?php foreach (['Linux', 'Tiny Hamsters', 'DOS', 'Binary', 'Unix', 'SPARC'] as $os): ?>
                            <option value="<?= $os ?>" <?= ($opsys == $os) ? 'selected' : '' ?>><?= $os ?></option>
                        <?php endforeach; ?>
                    </select>

                    <p>Robot Size:</p>
                    <?php foreach (['Giant', 'Normal', 'Nano'] as $s): ?>
                        <label>
                            <input type="radio" name="size" value="<?= $s ?>" <?= ($size == $s) ? 'checked' : '' ?> required> <?= $s ?>
                        </label>
                    <?php endforeach; ?>

                    <p>Select Laws of Robotics to include:</p>
                    <?php
                    $robotLaws = [
                        "First Law: A robot may not injure a human being or, through inaction, allow a human being to come to harm.",
                        "Second Law: A robot must obey the orders given it by human beings except where such orders would conflict with the First Law.",
                        "Third Law: A robot must protect its own existence as long as such protection does not conflict with the First or Second Law."
                    ];
                    foreach ($robotLaws as $law): ?>
                        <label>
                            <input type="checkbox" name="laws[]" value="<?= $law ?>" <?= (in_array($law, $laws)) ? 'checked' : '' ?>> <?= $law ?>
                        </label><br>
                    <?php endforeach; ?>

                    <br><input type="submit" name="submit" value="Build Robot">
                </form>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
    