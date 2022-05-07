<?php declare(strict_types=1);

namespace user\auth;

use elements\ErrorTag;
use elements\IElement;
use elements\LiteralElement;
use elements\SubmitConstantButton;

class GitHub implements IAuthMethod
{
    private string $state;
    private ?string $token = null;
    private ?string $tokenType = null;

    private static string $clientID = "154d6d89f8cf32b9275e"; //GH OAuth Client ID
    public function __construct()
    {
        $this->state = randomStr();
    }

    function getAuthButton(): IElement
    {

        $ghLogin = new LiteralElement(<<<END
<div style="width: 224px;">
    <img alt="GH logo" src="/assets/GitHub-Mark-64px.png" width="64" height="64">
    <img src="/assets/GitHub_Logo.png" alt="GitHub" width="150">
</div>
END
        );
        $params = array();
        $params['client_id'] = self::$clientID;
        $params['redirect_uri'] = "https://emotes.kosmx.dev/u/auth/gh";
        $params['state'] = $this->state;
        $params['scope'] = "read:user";#, user:email";
        return new SubmitConstantButton($ghLogin, $params, "get", "https://github.com/login/oauth/authorize");
    }

    function authCallback(): mixed
    {
        if ($this->token != null){// && $this->tokenType != null) {
            return true;
        }
        if (isset($_GET['code'])) {
            $state = $_GET['state'] ?? '';
            if ($state == $this->state) {

                $url = 'https://github.com/login/oauth/access_token';
                $data = array('client_id' => self::$clientID, 'client_secret' => file_get_contents('gh.token'), 'code' => $_GET['code']);

                $options = array('http' => array(
                    'header' => "Content-type: application/x-www-form-urlencoded\r\nAccept: application/json\r\n",
                    'method' => 'POST',
                    'content' => http_build_query($data)));

                $result = file_get_contents($url, false, stream_context_create($options));
                if ($result === false) {
                    return false;
                }
                $result = json_decode($result, true);
                if (isset($result['error'])) {
                    $msg = match ($result['error']){
                        "incorrect_client_credentials" => "Server is failing GitHub authentication, please try another login method",
                        "redirect_uri_mismatch" => "Someone wanted to redirect you to somewhere else. This is emotes.kosmx.dev",
                        "bad_verification_code" => $result['error_description'],
                        default => "Unknown error occurred:$result[error],$result[error_description]"
                    };
                    return new ErrorTag(new LiteralElement($msg));
                }

                if (isset($result['access_token'])) {
                    $this->token = $result['access_token']; // :D Auth success!
                    $this->tokenType = $result['token_type'];
                    return true;
                } else return false;

            } else {
                throw new IllegalStateException("GitHub");
            }

        }
        return false;
    }

    function getVerifiedUserID(): int
    {
        if ($this->token === null) throw new IllegalStateException("UserID without token");
        $url = 'https://api.github.com/user';
        $token = $this->token;
        var_dump($token);
        $tokenType = 'Bearer';
        $get = array(
            'http' => array(
                'header' => "Content-type: application/x-www-form-urlencoded\r\nAccept: application/json\r\nAuthorization: $tokenType $token\r\nuser-agent: php8\r\n",
                'method' => 'GET',
                'content' => http_build_query(array())
            )
        );
        $result = file_get_contents($url, false, stream_context_create($get));
        var_dump($result);
        $result = json_decode($result, true);
        var_dump($result);
        return (int)$result['id'];
    }
}