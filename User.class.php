<?php

class User {

    private $username;
    private $password;
    private $name;
    private $id;

    private $config;

    public function __construct($username, $password, $name = "") {
        $this->username = $username;
        $this->password = $this->hashPassword($password);
        $this->id = uniqid();
        $this->name = $name;

        $this->config = parse_ini_file("config.ini.php");
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
        $db = new Database();
        $mysqli = $db->getCon();
        
        $query = $mysqli->prepare("SELECT * FROM members WHERE user = ?");
        $query->bind_param('s', $this->username);
        $result = $query->execute();
        if($result->num_rows <= 0) {
            throw new Exception("User not found");
        }

        $fetch = $result->fetch_row();
        $storedPassword = $fetch['password'];
        
        $db->close();

        return password_verify($storedPassword, $this->password);
    }

    public function register() {
        $db = new Database('config.ini.php');
        $mysqli = $db->getCon();

        $query = $mysqli->prepare("SELECT * FROM members WHERE user = ?");
        $query->bind_param('s', $this->username);
        $result = $query->execute();
        if($result->num_rows > 0) {
            throw new Exception("User already exists");
        }

        $query = $mysqli->prepare("INSERT INTO members (id, user, password, name) VALUES(?, ?, ?, ?)");
        $query->bind_param('ssss', $this->id, $this->username, $this->password, $this->name);
        $result = $query->execute();
        if(!$result) {
            throw new Exception("User creation error");
        }

        $db->close();

        return true;
    }

    public function forgot() {
        $db = new Database('config.ini.php');
        $mysqli = $db->getCon();

        $query = $mysqli->prepare("SELECT * FROM members WHERE user = ?");
        $query->bind_param('s', $this->username);
        $result = $query->execute();
        if($result->num_rows <= 0) {
            throw new Exception("User not found");
        }
        
        $token = uniqid();
        $query = $mysqli->prepare("UPDATE members SET token = ?");
        $query->bind_param('s', $token);
        $result = $query->execute();

        $resetUrl = $this->config['website_url'] . "/forgot?token=" . $token;

        $email = new Email($this->user, $this->name);
        $email->setSubject("Password reset request");
        $email->setHtml(
                "<p>Hi " . $this->name . ",</p>
                <p>
                    We received a password reset request for your account
                    <br>
                    Click the following link to reset your password: <a href='" . $resetUrl . "'>" . $resetUrl . "</a>
                    <br><br>
                    If you didn't request the password reset, ignore this email
                </p>
                <p>Thanks, " . $this->config['from_name'] . "</p>"
            );

        try {
            $response = $email->send();
        } catch(Exception $e) {
            throw new Exception("Email send error >> " . $e->getMessage());
        }

        $db->close();

        return $response;
    }

}
