<?php

require_once 'KaijuRequest.php';
require_once 'DatabaseControl.php';

class Kaiju
{
    protected $clientId;
    protected $redirectUrl;
    protected $secretId;

    protected $accessToken;

    # This is to save user information after login
    public $UserInfo = null;
    public $VerificationKey = null;

    protected $DiscordScope = "guilds.join identify";
    protected $DiscordOAuthUrl = "https://discordapp.com/oauth2/authorize";
    protected $DiscordUrl = "https://discord.com";

    private $pdoConnection = null;

    function __construct($clientId, $redirectUrl, $secretId) {

        $this->clientId = $clientId;
        $this->redirectUrl = $redirectUrl;
        $this->secretId = $secretId;

        // To prevent CSRF attacks
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = md5($this->generateRandomString(12));
        }
    }

    public function ConnectDatabase($DBHOST, $DBNAME, $DBUSER, $DBPASS) {
        $databaseControl = new DatabaseControl($DBHOST, $DBNAME, $DBUSER, $DBPASS);
        $this->pdoConnection = $databaseControl->GetPdoConnection();
    }

    public function GenerateUrl(): string
    {
        if ($this->clientId == null) {
            die('You must specify the client_id (include.php)');
        } else if ($this->redirectUrl == null) {
            die('You must specify the redirectUrl (include.php)');
        } else if (!isset($_SESSION['csrf_token'])) {
            die("Session invalid, please re-initialize the client (reload the page)");
        }

        $QueryParams = array(
            'response_type'=>'code',
            'client_id'=> $this->clientId,
            'redirect_uri'=> $this->redirectUrl,
            'scope'=> $this->DiscordScope,
            'state' => $_SESSION['csrf_token']);

        $queryString =  http_build_query($QueryParams);
        $OAuthUrl = $this->DiscordOAuthUrl . '?' . $queryString;

        return urldecode($OAuthUrl);
    }

    public function LogIn($_get): bool|string
    {
        if ($this->pdoConnection == null) {
            die("You must connect the database, please check the documentation in the Kaiju repository for more information.");
        }

        if (!isset($_SESSION['csrf_token'])
        || $_SESSION['csrf_token'] !== $_get['state']) {
            return "Invalid CSRF-Token @ Try reload the page.";
        }

        $Url = $this->DiscordUrl . '/api/oauth2/token';

        $BodyRequest = http_build_query(array(
            "client_id" =>  $this->clientId,
            "client_secret" => $this->secretId,
            "grant_type" => "authorization_code",
            "code" => $_get['code'],
            "redirect_uri" => $this->redirectUrl
        ));

        $RequestResponse = KaijuRequest::SendRequest($Url, true, $BodyRequest);

        $HttpStatusCode = $RequestResponse['status_code'];
        $responseString = $RequestResponse['responseString'];

        // Validation successfully status code
        if ($HttpStatusCode >= 200
            && $HttpStatusCode < 300) {

            $Result = json_decode($responseString, true);

            $this->accessToken = $Result['access_token'];

            $this->UserInfo = $this->GetUserDiscordInformation();

            # Searching if the user is already verified on the server

            $UserCountQuery = $this->pdoConnection->prepare('SELECT COUNT(*) FROM users WHERE account_id = ?');

            if (!$UserCountQuery->execute([$this->UserInfo['Id']])) {
                die('Error searching the user on the database');
            }

            if ($UserCountQuery->fetchColumn() < 1) { # User is not verified yet

                # Adding user to the Database

                $VerificationCode = $this->generateRandomString();

                $InsertAccount = $this->pdoConnection->prepare('INSERT INTO users (access_token, verification_code, account_id) VALUES (?,?,?)');

                if (!$InsertAccount->execute([$this->accessToken, $VerificationCode, $this->UserInfo['Id']])) {
                    die('Error adding the discord account');
                }

                $this->VerificationKey = $VerificationCode;

            } else { # Already verified
                return "You are already verified on the server!";
            }

            return true;
        }

        return "A problem has occurred, please try again.";
    }

    public function GetUserInfo() : array
    {
        if ($this->UserInfo == null)
        {
            die('Discord session not started');
        }

        return $this->UserInfo;
    }

    /**
     * @return array
     */
    protected function GetUserDiscordInformation() : array
    {
        if ($this->accessToken == null) {
            die("Invalid AccessToken");
        }

        $requestHeader = array(
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Bearer ' . $this->accessToken);

        $RequestMessageResponseArray = KaijuRequest::SendRequest($this->DiscordUrl . '/api/users/@me', false, null, $requestHeader);

        $HttpStatusCode = $RequestMessageResponseArray['status_code'];
        $HttpResponseString = $RequestMessageResponseArray['responseString'];

        if ($HttpStatusCode >= 200 && $HttpStatusCode < 300) {
            $UserInfo = json_decode($HttpResponseString, true);
            $AvatarId = $UserInfo['avatar'];
            $Extension = substr($AvatarId, 0, 2);

            return array(
                'accessToken' => $this->accessToken,
                'Username' => $UserInfo['username'],
                'Discrim' => $UserInfo['discriminator'],
                'Id' => $UserInfo['id'],
                'AvatarUrl' => 'https://cdn.discordapp.com/avatars/' . $UserInfo['id'] . '/' . $AvatarId . '.' . ($Extension == "a_" ? "gif" : "png"),
                'Locale' => $UserInfo['locale']
            );
        } else {
            // Fail Fetching User Info
            die('There was a problem getting the necessary information from the discord account, please verify that you have the correct credentials.');
        }
    }

    // Thanks to https://stackoverflow.com/a/13212994
    public function generateRandomString($length = 10): string
    {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyz', ceil($length/strlen($x)) )),1,$length);
    }
}