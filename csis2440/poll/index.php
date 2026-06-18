<?php
$name = $_POST["name"] ?? '';
$answer = $_POST["option"] ?? '';
$useChuck = isset($_POST['useChuck']) && $_POST['useChuck'] === '1';
$pollResults = [];
$tally = [];
$showResults = false;

// Set Database Credentials
if ($_SERVER['HTTP_HOST'] == 'localhost') {
    define('HOST', 'localhost');
    define('USER', 'root');
    define('PASS', '1550');
    define('DB', 'eghbxxte_2440_poll');
    #echo $_SERVER["HTTP_HOST"] .' detected. Using local database credentials.<br>'; #testing
} else {
    define('HOST', 'localhost');
    define('USER', 'eghbxxte_rootNelstromo');
    define('PASS', 'n7c/bB93g7-n7c/bB93g7');
    define('DB', 'eghbxxte_2440_poll');
    #echo $_SERVER["HTTP_HOST"] .' detected. Using remote database credentials.<br>'; #testing
}

// Connect to the database
$conn = mysqli_connect(HOST, USER, PASS, DB);

// If the form is submitted, upload the data to the database
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pollSuccess = uploadToDatabase($name, $answer, $conn, $useChuck);

    if ($pollSuccess) {
        $pollResults = getPollResults($conn);
        $tally = tallyInstances($pollResults);
        arsort($tally); 
        $chuckCount = countChuckNorrisVotes($conn);

        $showResults = true;
    }
}
?>


<!doctype html>
<html lang="en">
<head>
    <title>Poll Form</title>
    <link rel="stylesheet" href="css/style.css" type="text/css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<!-- Chuck NOrris box functionality -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const checkbox = document.getElementById("useChuckCheckbox");
        const nameField = document.getElementById("name");

        checkbox.addEventListener("change", function () {
            if (this.checked) {
                nameField.disabled = true;
                nameField.value = ""; // Clear input if Chuck is checked
            } else {
                nameField.disabled = false;
            }
        });
    });
</script>

<body>
    <main>
        <div class="container">
            <h1>Survey</h1>
            <h4>What is the Best Cultural Reference made by Jeff Stone</h4>

            <?php if (!$showResults): ?>
            <!-- Poll Form -->
            <div class="form-container">
                <form action="index.php" method="post">
                    <label for="name">Name:</label>
                    <input type="text" name="name" id="name" <?= $useChuck ? 'disabled' : '' ?>>

                    <label>
                        <input type="checkbox" name="useChuck" value="1" id="useChuckCheckbox" <?= $useChuck ? 'checked' : '' ?>>
                        Are you Chuck Norris?
                    </label><br><br>

                    <label for="option1">
                        <input type="radio" name="option" id="option1" value="ZION HEAR ME">
                        ZION HEAR ME
                    </label><br>

                    <label for="option2">
                        <input type="radio" name="option" id="option2" value="CAN YOU DIG IT">
                        CAN YOU DIG IT
                    </label><br>

                    <label for="option3">
                        <input type="radio" name="option" id="option3" value="BEAVIS AND BUTTHEAD">
                        BEAVIS AND BUTTHEAD
                    </label><br>

                    <label for="option4">
                        <input type="radio" name="option" id="option4" value="CHUCK NORRIS">
                        CHUCK NORRIS
                    </label><br><br>

                    <button type="submit">Submit</button>
                </form>
            </div>
            <?php endif; ?>

            <?php if ($showResults): ?>
            <div id="results" class="results-wrapper">
                <h2 style="text-align: center;">Poll Results</h2>
                <div class="results-grid">
                    <!-- Pie Chart -->
                    <div class="chart-container">
                        <canvas id="pollChart"></canvas>
                    </div>

                    <!-- Ranked List -->
                    <div class="result-list">
                        <h3>Vote Totals</h3>
                        <ol>
                            <?php foreach ($tally as $label => $count): ?>
                                <li><strong><?= htmlspecialchars($label) ?></strong>: <?= $count ?> vote<?= $count != 1 ? 's' : '' ?></li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                </div>
            </div>

            <script>
            const ctx = document.getElementById('pollChart').getContext('2d');

            const chartData = {
                labels: <?= json_encode(array_keys($tally)) ?>,
                datasets: [{
                    label: 'Votes',
                    data: <?= json_encode(array_values($tally)) ?>,
                    backgroundColor: [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
                    ],
                    borderWidth: 1
                }]
            };

            new Chart(ctx, {
                type: 'pie',
                data: chartData,
                options: {
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
            </script>

            <p style="text-align:center; margin-top: 20px;">
                 🥋 Chuck Norris has voted <strong><?= $chuckCount ?></strong> time<?= $chuckCount !== 1 ? 's' : '' ?>.
            </p>


            <!-- Back Button -->
            <a href="index.php" class="back-button">Back to Poll</a>

            <?php endif; ?>
            </div>
        </main>
    </body>
</html>




<?php
/**
 * Pulls the data from the global variables and uploads it to the database.
 * And auto fills Chucks name into the name field if the user is Chuck Norris
 * Also checks if the user has used the form correctly
 * @param mixed $name The name from the form submission... Or Chuck
 * @param mixed $answer The radio option answer selected by the user
 * @param mixed $conn The database connection variables
 * @param mixed $useChuck The ultimate question: Is the user Chuck Norris?
 * @return bool Returns true if the upload was successful, false otherwise
 */
function uploadToDatabase($name, $answer, $conn, $useChuck) {
    if (empty($answer)) {
        echo "<p style='color:red;'>Please select an answer.</p>";
        return false;
    }

    if (empty($name) && !$useChuck) {
        echo "<p style='color:red;'>Please enter your name or check the Chuck Norris box.</p>";
        return false;
    }

    if ($useChuck && empty($name)) {
        $name = 'Chuck Norris';
    }

    $sql = "INSERT INTO poll_responses (name, answer) VALUES ('$name', '$answer')";
    if (!$conn->query($sql)) {
        echo "<p style='color:red;'>Error saving to database: " . $conn->error . "</p>";
        return false;
    }

    echo "<p style='color:green;'>Thank you for your response, $name!</p>";
    return true;
}


/**
 * Summary of getPollResults
 * Fetches all poll responses from the database and returns them as an array.
 * * @param mysqli $conn The database connection
 * * @return array $pollResults An array of poll responses
 */
function getPollResults($conn,) {
    $sql = 'SELECT * FROM poll_responses;';
    $dbResults = mysqli_query($conn, $sql);
    
    while ($row = mysqli_fetch_array($dbResults, MYSQLI_ASSOC)) {
        #echo $row['answer'].'<br>';
        $pollResults[] = $row['answer'];  
    }

    return $pollResults;

}

/**
 * Summary of tallyInstances
 * @param mixed $pollResults An array of poll responses
 */
function tallyInstances($pollResults) {
     $tally = [];

    foreach ($pollResults as $item) {
        if (isset($tally[$item])) {
            $tally[$item]++;
        } else {
            $tally[$item] = 1;
        }
    }

    return $tally;
}

/**
 * Counts how many responses were submitted under the name "Chuck Norris"
 * @param mysqli $conn The database connection
 * @return int Number of Chuck Norris responses
 */
function countChuckNorrisVotes($conn) {
    $sql = "SELECT COUNT(*) as count FROM poll_responses WHERE name = 'Chuck Norris'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return (int)$row['count'];
}




/**
 * PHP DOCUMENTATION ---- TO MAKE MY LIFE EASIER
 * Variables 
 * @var string $name The name of the user submitting the poll
 * @var string $answer The answer selected by the user in the poll
 * @var array $pollResults The results of the poll, fetched from the database
 * @var int $tally The tally of the poll results, indexed by answer
 * @var bool $useChuck If user is Chuck Norris, he does not need to enter a name on the form
*/
?>