<?php

require("./sendgrid-php/sendgrid-php.php");

class Email {

    private $email;
    private $sendgrid;

    private $config;

    private $hasContent;
    private $hasSubject;

    public function __construct($toEmail, $toName) {
        $this->hasContent = false;
        $this->hasSubject = false;

        $this->config = parse_ini_file("config.ini.php");

        $this->email = new \SendGrid\Mail\Mail();
        $this->email->setFrom($this->config['from_email'], $this->config['from_name']);
        $this->email->setSubject("Sending with SendGrid is Fun");
        $this->email->addTo($toEmail, $toName);

        $this->sendgrid = new \SendGrid($this->config['SENDGRID_API_KEY']);
    }

    public function setPlain($plain) {
        $this->email->addContent(
            "text/plain", $plain
        );
    }
    
    public function setSubject($subject) {
        $this->email->setSubject($subject);
        $this->hasSubject = true;
    }

    public function setHtml($html) {
        $this->email->addContent(
            "text/html", $html
        );
        $this->hasContent = true;
    }

    public function send() {
        if(!$this->hasSubject || !$this->hasContent) {
            throw new Exception("Email HTML content and/or Subject missing!");
        }
        
        $response = $this->sendgrid->send($this->email);
        return $response;
    }
}
?>