<?php
    require_once("dbconfig.php");

    $dbhost = DB_HOST;
    $dbuser = DB_USER;
    $dbpass = DB_PASS;
    $dbname = DB_NAME;

    $dbConnection = new PDO(
        "mysql:host=$dbhost;dbname=$dbname;charset=utf8mb4",
        $dbuser,
        $dbpass
    );

    $dbConnection->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    return $dbConnection;
    function getDB() {
        global $dbConnection;
        return $dbConnection;
    }
?>