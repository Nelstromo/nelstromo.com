<?php
/**
 * @file navbar.php
 * @description This file contains the navigation bar for the Export Valid App web application.
 */



$page = basename($_SERVER['PHP_SELF']);

?>

<br>

<header class="site-header" role="banner">
    <div class="nav-wrap">
      <a class="brand" href="." aria-label="Home">
        <span class="brand-mark" aria-hidden="true">◆</span>
        <span class="brand-name">Export Validation App</span>
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
        <?php /*if ($_SESSION['access'] == "granted"):*/ ?>
        <ul class="links">

          <li><a href="howto.php">How To</a></li>
          <li><a href="about.php">About</a></li>
          <li><a href="reset.php">Reset</a></li>
        </ul>
        <?php /*endif; */?>

        <div class="actions">
          <?php /*if ($_SESSION['access'] == "granted"): */?>
            <a class="button" >LightMode</a>
          <?php /* endif; */?>
          
        </div>
      </nav>
    </div>
  </header>