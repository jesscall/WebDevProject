<?php
require_once("dbconfig.php");

function getDB() {
    $dbhost = DB_HOST;
    $dbuser = DB_USER;
    $dbpass = DB_PASS;
    $dbname = DB_NAME;

    $dbConnection = new PDO(
        "mysql:host=$dbhost;dbname=$dbname",
        $dbuser,
        $dbpass
    );

    $dbConnection->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    return $dbConnection;
}
?>