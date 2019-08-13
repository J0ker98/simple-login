<?php

class Facebook {

    private $appId          = '';
    private $appSecret      = '';
    private $redirectUri    = '';
    private $scope          = 'public_profile,email';
    private $code           = '';
    private $context        = '';
    private $accessToken    = null;
    private $homePage       = '';

    function __construct(array $config = null){
        $this->appId        = $config['app_id'];
        $this->redirectUri  = urlencode($config['redirect_uri']);
        $this->appSecret    = $config['app_secret'];
        $this->homePage     = $config['home_page'];

        $this->context = stream_context_create(array(
            'http' => array('ignore_errors' => true)
        ));
    }

    function login() {
        header("Location: https://www.facebook.com/dialog/oauth?client_id={$this->appId}&redirect_uri={$this->redirectUri}&scope={$this->scope}");
        return;
    }

    function exchangeCode($code) {
        $this->code = $code;
        $exchangeUrl = "https://graph.facebook.com/v2.3/oauth/access_token?client_id={$this->appId}&redirect_uri={$this->redirectUri}&client_secret={$this->appSecret}&code={$this->code}";
        $response = json_decode(file_get_contents($exchangeUrl, false, $this->context));
        if (isset($response->access_token)) {
            $this->accessToken = $response->access_token;
            header("Location: {$this->homePage}?access_token={$this->accessToken}");
        } else {
            echo json_encode($response);
        }
    }

    public static function __callStatic($name,$arguments) {
        switch ($name){
            case 'getUserInfo':
                $accessToken = $arguments[0];
                if (!$accessToken){
                    return false;
                }
                $response = json_decode(file_get_contents("https://graph.facebook.com/v2.2/me/?access_token={$accessToken}&fields=id,name,email"));
                if (!isset($response->id)){
                    return false;
                }
                return $response;
            default:
                die("x_x");
        }
    }

}
