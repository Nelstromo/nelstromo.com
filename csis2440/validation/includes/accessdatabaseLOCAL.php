<?php

//Database Access stuffs
$host = "localhost";
$username = "root";
$password = "1550"; 
$database = "eghbxxte_datavalid_contact_form";

// Create connection
$conn = new mysqli($host, $username, $password, $database);


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $phone = $_POST["phone"];

    $sql = "INSERT INTO contacts (name, phone)
            VALUES ('$name', '$phone')";

    if (!$conn->query($sql)) {
        echo "<p style='color:red;'>Error saving to database: " . $conn->error . "</p>";
    }
}
?>