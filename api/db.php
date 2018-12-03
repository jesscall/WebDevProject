<?php
function getDB() {
    $dbhost="127.0.0.1";
    $dbuser="root";
    $dbpass="jess53561";
    $dbname="project";

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