<?php

class User {

    private $username;
    private $password;
    private $id;

    public function __construct($username, $password) {
        $this->username = $username;
        $this->password = $this->hashPassword($password);
        $this->id = uniqid();
    }

    public function hashPassword($password) {
        $options = [
            'cost' => 11,
        ];
        return password_hash($password, PASSWORD_BCRYPT, $options);
    }

    public function getUsername() { return $this->username; }
    public function getPassword() { return $this->password; }
    public function getId() { return $this->id; }

    public function login() {
        $db = new Database('config.ini.php');
        $mysqli = $db->getCon();
        
        $query = $mysqli->prepare("SELECT * FROM members WHERE user = ?");
        $query->bind_param('ss', $this->username);
        $result = $query->execute();
        if(!$result) {
            throw new Exception("User not found");
        }

        $fetch = $result->fetch_row();
        $storedPassword = $fetch['password'];
        
        $db->close();

        return password_verify($storedPassword, $this->password);
    }

}