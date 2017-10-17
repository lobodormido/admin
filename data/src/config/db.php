<?php

class db{
    private $dbhost = 'localhost';
    private $dbuser = 'root';
    private $dbpass = 'root';
    private $dbname = 'home_modernizacion';

    public function connect(){
        $mysql_connect_str = "mysql:host=$this->dbhost;dbname=$this->dbname";
        $dbConnection = new PDO($mysql_connect_str, $this->dbuser, $this->dbpass, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
        $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        mysqli_set_charset("utf8");
        return $dbConnection;
    }
}