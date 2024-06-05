<?php

session_start();
date_default_timezone_set('Asia/Jakarta');

try {
    $dbhost = 'localhost';
    $dbuser = 'root';
    $dbpass = '';
    $dbname = ''; // Nama Database
    
    $pdo = new PDO('mysql:host='.$dbhost.';dbname='.$dbname, $dbuser, $dbpass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set PDO to throw exceptions
    $session_login = isset($_COOKIE['user_login']) && $_COOKIE['user_login'] != '' ? $_COOKIE['user_login'] : '';

} catch(PDOException $e) {
    die('
        <p>
            Gagal terkoneksi ke dalam database!<br/><b>Error</b>: '.$e->getMessage().'
        </p>
    ');
}

include 'functions.php';

?>