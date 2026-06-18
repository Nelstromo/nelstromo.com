<?php
/**
 * Summary of Product Class
 * 
 * This class represents the products in the catalog with properties such as name, description, image, and price.
 */
class Product {
    /** @var $name The name of the product from the catalog table*/
    public $name;
    /** @var $description The description of the product from the catalog table*/
    public $description;
    /** @var $image The image path of the product from the catalog table*/
    public $image;
    /** @var $price The price of the product from the catalog table*/
    public $price;

    public function __construct($row) 
    {
        $this->name = $row['name'];
        $this->description = $row['description'];
        $this->image = 'img/productImg/' . $row['image'] . '.jpg';
        $this->price = $row['price'];
    }

    /**
     * Build a Product from request data by looking it up in the DB.
     * Accepts: id (numeric) or product_name/name (string).
     */
    
    public static function fromRequest(mysqli $conn, array $data)
    {
        if (!empty($data['id'])) {
            $id = $data['id']; // no validation, no casting
            $query = "SELECT name, description, image, price FROM product WHERE id = $id LIMIT 1";
        } else {
            $lookup = $data['product_name'] ?? $data['name'] ?? '';
            if ($lookup === '') {
                return null;
            }
            $query = "SELECT name, description, image, price FROM product WHERE name = '$lookup' LIMIT 1";
        }

        $result = mysqli_query($conn, $query);
        $row = $result ? mysqli_fetch_assoc($result) : null;

        return $row ? new Product($row) : null;
    }


    /**
     * Displays the product in a card format for the catalog page.
     * 
     * @return string HTML representation of the product card.
     */
    public function displayCatalog()
    {
        return "
        <div class='product-card'>
            <div class='card-details'>
                <h3 class='card-title'>{$this->name}</h3>
                <img src='{$this->image}' alt='{$this->name}' class='product-image'>
                <p class='card-body'>{$this->description}</p>
                <span class='product-price'>\${$this->price}</span>
            </div>
            <a href='product.php?id=" . $this->name . "' class='card-button'>View Product</a>
        </div>";
    }

    /**
     * Displays the product details on the product page.
     * 
     * @return void
     */
    public function displayProductPage()
    {
       echo "
            <div class='product-wrapper'>
                <div class='product-left'>
                    <img src='{$this->image}' alt='{$this->name}'>
                    <a href='catalog.php' class='back-to-catalog'>Back to Catalog</a>
                </div>

                <form method='post' action='product.php' class='product-right'>
                    <h1>{$this->name}</h1>
                    <p>{$this->description}</p>
                    <span class='price-tag'>\${$this->price}</span>

                    <input type='hidden' name='product_name' value='{$this->name}'>
                    <input type='hidden' name='product_price' value='{$this->price}'>

                    <label for='quantity'>Quantity:</label>
                    <input type='number' name='quantity' id='quantity' min='1' max='100' value='1'>
                    <input type='submit' name='add-to-cart' value='Add to Cart'>
                </form>
            </div>";
    }

    /**
     * Update cart using the current product and a quantity.
     */
    public function updateCart(int $quantity = 1): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $quantity = max(1, min(100, (int)$quantity));

        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        foreach ($_SESSION['cart'] as &$item) {
            if ($item['name'] === $this->name) {
                $item['quantity'] += $quantity;
                return;
            }
        }

        $_SESSION['cart'][] = [
            'name' => $this->name,
            'price' => (float)$this->price, // price from DB, not user input
            'quantity' => $quantity,
            'image' => $this->image,
            'description' => $this->description,
        ];
    }
}
?>