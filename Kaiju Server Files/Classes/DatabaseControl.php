<?php

class DatabaseControl {

    public $pdoConnection;

    function __construct($DBHOST, $DBNAME, $DBUSER, $DBPASS)
    {
        if (empty($DBHOST) || empty($DBNAME) || empty($DBUSER)) {
            die("You must specify the database information and your credentials in 'Include.php'.");
        }

        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $dsn = "mysql:host=$DBHOST;dbname=$DBNAME;charset=utf8mb4";

        try {
            $this->pdoConnection = new \PDO($dsn, $DBUSER, $DBPASS, $options);
            return true;
        } catch (PDOException $e) {
            die($e->getMessage() . ' - ' . (int)$e->getCode());
        }
    }

    function GetPdoConnection(): PDO
    {
        return $this->pdoConnection;
    }
}