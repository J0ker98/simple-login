<?php

require("./Facebook.php");

class External {

    private $config;
    
    public function __construct() { 
        $this->config = parse_ini_file("config.ini.php");
    }

    public function exchange($token = "") {
        $fb = new Facebook($this->$config);

        if ($token != ""){
            $fb->exchangeCode($token);
        } else {
            $fb->login();
        }
    }

    public function auth($token = "") {
        $fb = Facebook::getUserInfo($token);
        if(!$fb) {
            throw new Exception("Invalid access token");
        }

        $username = $fb->email;
        $password = $token;
        $name = $fb->name;

        $user = new User($username, $password, $name);

        return $user;
    }

}

?>