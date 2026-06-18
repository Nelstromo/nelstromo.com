<?php
/**
 * @file functions.php
 * @description This file contains utility functions for the Catalog application.   
 * * This file is used to define common functions that can be reused across the application.
 */

//--------------------------------------------------------------------------------------------
/**
 * Summary of capitalizeFirstLetter
 * This function capitalizes the first letter of a given name.
 * @param string $name The name of the user, retrieved from the session
 * @return mixed
 */
function capitalizeFirstLetter($name) 
{
    return preg_replace_callback('/^[a-z]/', function ($matches) 
    {
        return strtoupper($matches[0]);
    }, $name);
}
/**
 * Summary of nelstromoHash
 * This function generates a hash for the username and password using a specific algorithm.
 * @param string $username The username input from the login form.
 * @param string $password The password input from the login form.
 * @return string The generated hash.
 */
function nelstromoHash($username, $password)
{
    $hash1 = "4164616C656E65526165"; //AdaleneRae
    $hash2 = "436C61697265526165";//ClaireRae
    $hash3 = "4D696C61417A756C457661456C666965";//MilAzuEvaElfie

    $user = $hash1.$username.$password.$hash3;
    $user2 = $hash2.$user.$user.$hash3;

    $word = hash('sha512', $user2);

    return $word;
}
