<?php
class User {

    private $username;
    private $password;
    private $name;
    private $id;

    private $config;

    public function __construct($username, $password, $name) {
        $this->username = strtolower($username);
        $this->password = $password;
        $this->name = $name;
    
        $this->config = parse_ini_file("config.ini.php");
    }

    public function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public function getUsername() { return $this->username; }
    public function getPassword() { return $this->password; }
    public function getId() { return $this->id; }

    public function login() {
        $db = new Database();
        $mysqli = $db->getCon();
        
        $query = $mysqli->prepare("SELECT * FROM members WHERE user = ?");
        $query->bind_param('s', $this->username);
        $query->execute();
        $result = $query->get_result();
        if($result->num_rows <= 0) {
            throw new Exception("User not found");
        }
        $query->close();

        $fetch = $result->fetch_array();
        $this->id = $fetch['id'];
        $this->username = $fetch['username'];
        $this->name = $fetch['name'];
        
        
        $db->close();

        return password_verify($this->password, $fetch['password']);
    }

    public function register() {
        $db = new Database();
        $mysqli = $db->getCon();

        if(empty($this->name)) {
            throw new Exception("Name cannot be empty on register method call");
        }

        $query = $mysqli->prepare("SELECT * FROM `members` WHERE `user` = ?");

        $query->bind_param('s', $this->username);
        $query->execute();
        $result = $query->get_result();
        if($result->num_rows > 0) {
            throw new Exception("User already exists");
        }
        $query->close();

        $query = $mysqli->prepare("INSERT INTO `members` (`id`, `user`, `password`, `name`) VALUES(?, ?, ?)");
        $query->bind_param('ssss', $this->id, $this->username, $this->hashPassword($this->password), $this->name);
        $query->execute();
        $result = $query->get_result();
        if(!$result) {
            throw new Exception("User creation error");
        }
        $query->close();

        $db->close();

        return true;
    }

    public function forgot() {
        $db = new Database();
        $mysqli = $db->getCon();

        $query = $mysqli->prepare("SELECT * FROM members WHERE user = ?");
        $query->bind_param('s', $this->username);
        $query->execute();
        $result = $query->get_result();
        if($result->num_rows <= 0) {
            throw new Exception("User not found");
        }
        $query->close();
        
        $token = uniqid();
        $query = $mysqli->prepare("UPDATE members SET token = ? WHERE user = ?");
        $query->bind_param('ss', $token, $this->user);
        $query->execute();
        $result = $query->get_result();
        $query->close();

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
