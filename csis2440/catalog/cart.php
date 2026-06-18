<?php 
session_start();
if ($_SESSION['access'] !== 'granted') {
    header('Location: .');
    exit();
}

$insufficientFunds = false;
$purchaseSuccess   = false;
$grandTotal        = 0.00; 

if (isset($_POST['update-cart'])) {
    if (!empty($_SESSION['cart']) && isset($_POST['quantities']) && is_array($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $index => $quantity) {
            if (isset($_SESSION['cart'][$index])) {
                $qty = (int)$quantity;
                if ($qty <= 0) {
                    unset($_SESSION['cart'][$index]); // remove item
                } else {
                    $_SESSION['cart'][$index]['quantity'] = min(100, $qty);
                }
            }
        }
        $_SESSION['cart'] = array_values($_SESSION['cart']); // reindex
    }
}

if (isset($_POST['checkout'])) {
    // ---- compute cart total
    $grandTotal = array_sum(array_map(function($item){
        return (float)$item['price'] * (int)$item['quantity'];
    }, $_SESSION['cart'] ?? []));

    // ---- wallet + conversion
    $CREDITS_PER_DOLLAR = 1;                    // change if 1 credit != $1
    $neededCredits      = (int)ceil($grandTotal * $CREDITS_PER_DOLLAR);
    $wallet             = isset($_SESSION['wealth']) ? (int)$_SESSION['wealth'] : 0;

    if (!empty($_SESSION['cart']) && $wallet >= $neededCredits) {
        // Deduct + show thank-you
        $_SESSION['wealth'] = max(0, $wallet - $neededCredits);

        // Save a snapshot for the receipt (since we’ll clear the cart)
        $_SESSION['last_order']       = $_SESSION['cart'];
        $_SESSION['last_order_total'] = $grandTotal;

        // Clear the cart
        unset($_SESSION['cart']);

        $purchaseSuccess = true;
    } else {
        $insufficientFunds = true; // not enough wealth (or empty cart)
    }
}
?>


<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopping Cart</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="css/style.css" type="text/css">
    <script src="js/script.js"></script>
</head>
<body class="hero-gradient">
    <nav>
        <?php include 'includes/navbar.php'; ?>
    </nav>
    <main>
        <div class="container">

  <?php if ($purchaseSuccess): ?>
    <div class="thank-you-message">
      <h2>Thank You for Your Order!</h2>
      <p>Your order has been placed successfully. Here are the details:</p>
      <ul>
        <?php foreach (($_SESSION['last_order'] ?? []) as $item): ?>
          <li>
            <?php echo (int)$item['quantity'] . ' x ' . htmlspecialchars($item['name']); ?>
            - $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
          </li>
        <?php endforeach; ?>
      </ul>
      <p>Total: $<?php echo number_format($_SESSION['last_order_total'] ?? 0, 2); ?></p>
      <p>Wallet remaining: <strong><?=
        number_format((int)($_SESSION['wealth'] ?? 0))
      ?></strong></p>
      <a href="catalog.php" class="continue-shopping">Continue Shopping</a>
      <?php
        // clear the snapshot so refresh doesn't re-show
        unset($_SESSION['last_order'], $_SESSION['last_order_total']);
      ?>
    </div>

  <?php else: ?>
    <div class="page-header">
      <h2 class="page-title">Shopping Cart</h2>
      <p class="page-subtitle">Review your selected items below.</p>
    </div>

    <?php if ($insufficientFunds): ?>
      <div class="deniedMSG">
        <p class="denied-msg-alert"><strong>Insufficient funds</strong></p>
        <p class="error-msg">
          You need $<?= number_format($grandTotal, 2) ?> but your wallet has $<?= number_format($_SESSION['wealth'], 2 ) ?> credits.
        </p>
      </div>
    <?php endif; ?>

    <form method="post" action="cart.php" class="cart-form">
      <?php if (!empty($_SESSION['cart'])): ?>
        <table class='cart-table'>
          <thead>
            <tr>
              <th>Product Name</th>
              <th>Price</th>
              <th>Quantity</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <?php $grandTotal = displayCartItems(); ?>
            <tr class='cart-total'>
              <td colspan='3'>Grand Total</td>
              <td>$<?php echo number_format($grandTotal, 2); ?></td>
            </tr>
          </tbody>
        </table>

        <div class="cart-actions">
          <input type="submit" name="update-cart" value="Update Cart" class="update-cart-button">
          <input type="submit" value="Checkout" name="checkout" class="checkout-button">
        </div>
      <?php else: ?>
        <p class='empty-cart-message'>Your cart is empty.</p>
      <?php endif; ?>
    </form>
  <?php endif; ?>

</div>

       
    </main>
</body>
</html>

<?php
function displayCartItems() {
    $total = 0;
    foreach ($_SESSION['cart'] as $index => $item) {
        $itemTotal = $item['price'] * $item['quantity'];
        $total += $itemTotal;

        echo "
            <tr>
                <td>{$item['name']}</td>
                <td>$" . number_format($item['price'], 2) . "</td>
                <td>
                    <input type='number' name='quantities[{$index}]' value='{$item['quantity']}' min='0' class='quantity-input'>
                </td>
                <td>$" . number_format($itemTotal, 2) . "</td>
            </tr>";
    }
    return $total;
}
?>
