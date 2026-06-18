<?php
session_start();
if ($_SESSION['access'] !== 'granted') {
        header('Location: .'); // Redirect to login if access is not granted
        exit();
    }
    /**
     * @file product.php
     * @description This file displays the details of a specific product.
     * It retrieves product information from the database based on the product ID passed in the URL.
     */
    // -----------------------Set Database Credentials-----------------------------------
    include_once 'includes/database.php';
    //-----------------------------------------------------------------------------------

    // -----------------------Include Common Functions-----------------------------------
    include_once 'includes/functions.php';
    //-----------------------------------------------------------------------------------

    // -----------------------Set Classes-----------------------------------
    include_once 'class/Product.php';
    //-----------------------------------------------------------------------------------


if (isset($_POST['add-to-cart']))
{
    $prod = Product::fromRequest($conn, $_POST); // loads from DB
    if ($prod !== null) {
        $qty = (int)($_POST['quantity'] ?? 1);
        $prod->updateCart($qty);
    } else {
        // handle product not found
    }
}

   
?>

<!doctype html>
<html lang="en">
    <head>
        <title>PRODUCT</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="stylesheet" href="css/style.css" type="text/css">
    </head>
    <body>
        <main class="hero-gradient">
            <nav>
                <?php include 'includes/navbar.php'; ?>
            </nav>
            <?php if (isset($_POST['add-to-cart'])) : ?>
                
                    <div class="cart-updated-header">
                        <h2>Product Added to Cart</h2>
                        <p><span><?php echo $_POST['quantity'];?> - <?php echo $_POST['product_name']; ?>(s)</span> has been added to your cart.</p>
                        <div class="cart-updated-nav-buttons">
                            <a href="catalog.php" class="back-to-catalog">Continue Shopping</a>
                            <a href="cart.php" class="view-cart">Checkout</a>
                        </div>
                    </div>
                
            <?php else : ?>
            <div class="container-hide"> 
                <div class="page-header">
                    <h2 class="page-title">Product Details</h2>
                </div>
                <div class="product-details-container">
                    <?php
                        if (isset($_GET['id']) && !empty($_GET['id'])) {
                            $productId = $_GET['id'];
                            $query = "SELECT * FROM product WHERE name = '$productId'";
                            $result = mysqli_query($conn, $query);

                            if ($result->num_rows > 0) {
                                $row = $result->fetch_assoc();
                                $prod = new Product($row);
                                $prod->displayProductPage();
                            } else {
                                echo "<p>Product not found.</p>";
                            }
                        } else {
                            echo "<p>No product ID specified.</p>";
                        }
                    ?>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </body>
</html>