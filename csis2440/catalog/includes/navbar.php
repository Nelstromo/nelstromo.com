<?php
/**
 * @file navbar.php
 * @description This file contains the navigation bar for the Catalog application.
 */

use PhpParser\Node\Expr\Cast\Double;

$page = basename($_SERVER['PHP_SELF']);

?>

<br>

<header class="site-header" role="banner">
    <div class="nav-wrap">
      <a class="brand" href="." aria-label="Home">
        <span class="brand-mark" aria-hidden="true">◆</span>
        <span class="brand-name">Nelstromo's Cosmic Wonders</span>
      </a>

      <!-- Mobile toggle (CSS-only) -->
      <input id="nav-toggle" class="nav-toggle" type="checkbox" />
      <label for="nav-toggle" class="nav-burger" aria-label="Toggle menu">
        <span class="sr-only">Menu</span>
        <span class="bar" aria-hidden="true"></span>
        <span class="bar" aria-hidden="true"></span>
        <span class="bar" aria-hidden="true"></span>
      </label>

      <nav class="main-nav" role="navigation" aria-label="Main">
        <?php if ($_SESSION['access'] == "granted"): ?>
        <ul class="links">
          <li><a href="catalog.php">Catalog</a></li>
          <li><a href="cart.php">Cart</a></li>
          <li><a href="account.php">Account</a></li>
          <li><a href="logout.php">Logout</a></li>
        </ul>
        <?php endif; ?>

        <div class="actions">
          <?php if ($_SESSION['access'] == "granted"): ?>
          <a class="btn primary" href="#" aria-label="Wallet"><?php echo "Wallet: $" . number_format((float)($_SESSION['wealth'] ?? 0)); ?></a>
          <?php endif; ?>
          <!--<a class="btn primary" href="#">Music</a>-->
        </div>
      </nav>
    </div>
  </header>