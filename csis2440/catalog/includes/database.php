<?php 
/*
 * @file database.php
 * @description This file contains the database connection details for the catalog application.
 * @
 */

if ($_SERVER['HTTP_HOST'] == 'localhost') {
        define('HOST', 'localhost');
        define('USER', 'root');
        define('PASS', '1550');
        define('DB', 'catalog');
    } else {
        define('HOST', 'localhost');
        define('USER', 'eghbxxte_rootNelstromo');
        define('PASS', 'n7c/bB93g7-n7c/bB93g7');
        define('DB', 'eghbxxte_catalog');
    }
    //-----------------------------------------------------------------------------------

    // ------------------------Connect to the database ----------------------------------
    $conn = mysqli_connect(HOST, USER, PASS, DB);
    ?>