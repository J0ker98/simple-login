<?php

class Database {

    private $con;
    private $config;

    public function __construct() {
        $this->config = parse_ini_file("config.ini.php");
        $this->con = new mysqli(
            $this->config['db_host'], 
            $this->config['db_user'], 
            $this->config['db_password'], 
            $this->config['db_name']
        );
		if ($this->con->connect_error) {
            throw new Exception('Connection error (' . $this->con->connect_errno . ') '
            . $this->con->connect_error);
		}
    }

    public function getCon() { return $this->con; }

    public function close() { mysqli_close($this->con); }
}
?>    
