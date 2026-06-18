<?php
    session_start();
    if ($_SESSION['access'] !== 'granted') {
        header('Location: .'); // Redirect to login if access is not granted
        exit();
    }
   
    // -----------------------Set Database Credentials-----------------------------------
    include_once 'includes/database.php';
    //-----------------------------------------------------------------------------------

    // -----------------------Include Common Functions-----------------------------------
    include_once 'includes/functions.php';
    //-----------------------------------------------------------------------------------

    // -----------------------Set Classes-----------------------------------
    include_once 'class/Product.php';
    //-----------------------------------------------------------------------------------


?>
<!doctype html>
<html lang="en">
<head>
    <title>CATALOG</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="css/style.css" type="text/css">
</head>
<body >
    <div class="hero-gradient"></div>
    <?php include 'includes/navbar.php'; ?>
    <main>
        <div class="container">
            <?php if ($_SESSION['access']) : ?> 
                <div class="page-header">
                    <h2 class="page-title">Welcome, <?php echo capitalizeFirstLetter($_SESSION['user-key']); ?>!</h2>
                    <p class="page-subtitle">Browse our exclusive product collection below.</p>
                </div>

                <section class="product-grid">
                    <?php displayProducts($conn); ?>
                </section>

            <?php else : ?> 
                <div class="no-access">
                    <p>You do not have access to this catalog. Please log in.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>

<?php
function displayProducts($conn) {
    $sql = "SELECT name, description, image, price FROM product";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $prod = new Product($row);
            echo $prod->displayCatalog();
        }
    } else {
        echo "<p class='no-products'>No products found in the database.</p>";
    }
}
?>
