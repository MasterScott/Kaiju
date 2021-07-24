<?php

require_once 'KaijuRequest.php';

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

    public $pdoConnection = null;

    function __construct($clientId, $redirectUrl, $secretId) {

        $this->clientId = $clientId;
        $this->redirectUrl = $redirectUrl;
        $this->secretId = $secretId;

        // To prevent CSRF attacks
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = md5($this->generateRandomString(12));
        }
    }

    /**
     * @throws \Exception
     */
    public function ConnectDatabase($DBHOST, $DBNAME, $DBUSER, $DBPASS) {
        if (empty($DBHOST) || empty($DBNAME) || empty($DBUSER)) {
            throw new Exception("You must specify the database information and your credentials in 'Include.php'.");
        }

        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $dsn = "mysql:host=$DBHOST;dbname=$DBNAME;charset=utf8mb4";

        try {
            $this->pdoConnection = new \PDO($dsn, $DBUSER, $DBPASS, $options);
            return true;
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    /**
     * @throws Exception
     */
    public function GenerateUrl(): string
    {
        if ($this->clientId == null) {
            throw new Exception('You must specify the client_id (include.php)');
        } else if ($this->redirectUrl == null) {
            throw new Exception('You must specify the redirectUrl (include.php)');
        } else if (!isset($_SESSION['csrf_token'])) {
            throw new Exception("Session invalid, please re-initialize the client (reload the page)");
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

    /**
     * @throws Exception
     */
    public function LogIn($_get): bool|string
    {
        if ($this->pdoConnection == null) {
            throw new PDOException("You must connect the database, please check the documentation in the Kaiju repository for more information.");
        }

        if (!isset($_SESSION['csrf_token'])
        || $_SESSION['csrf_token'] !== $_get['state']) {
            return "Invalid CSRF-Token @ Try reload the page.";
        }

        $Url = $this->DiscordUrl . '/api/oauth2/token';

        $BodyRequest = array(
            "client_id" =>  $this->clientId,
            "client_secret" => $this->secretId,
            "grant_type" => "authorization_code",
            "code" => $_get['code'],
            "redirect_uri" => $this->redirectUrl
        );

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
                throw new PDOException('Error searching the user on the database');
            }

            if ($UserCountQuery->fetchColumn() < 1) { # User is not verified yet

                # Adding user to the Database

                $VerificationCode = $this->generateRandomString();

                $InsertAccount = $this->pdoConnection->prepare('INSERT INTO users (access_token, verification_code, account_id) VALUES (?,?,?)');

                if (!$InsertAccount->execute([$this->accessToken, $VerificationCode, $this->UserInfo['Id']])) {
                    throw new PDOException('Error adding the discord account');
                }

                $this->VerificationKey = $VerificationCode;

            } else { # Already verified
                return "You are already verified on the server!";
            }

            return true;
        }

        return "A problem has occurred, please try again.";
    }

    /**
     * @throws Exception
     */
    public function GetUserInfo() : array
    {
        if ($this->UserInfo == null)
        {
            throw new Exception('Discord session not started');
        }

        return $this->UserInfo;
    }

    /**
     * @throws Exception
     */
    protected function GetUserDiscordInformation() : array
    {
        if ($this->accessToken == null) {
            throw new Exception("Invalid AccessToken");
        }

        $requestHeader = array(
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Bearer ' . $this->accessToken);

        $requestUserInfo = curl_init();

        curl_setopt($requestUserInfo, CURLOPT_URL, $this->DiscordUrl . '/api/users/@me');
        curl_setopt($requestUserInfo, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($requestUserInfo, CURLOPT_HTTPHEADER, $requestHeader);

        $RequestMessageResponse = curl_exec($requestUserInfo);

        curl_close($requestUserInfo);

        $UserInfo = json_decode($RequestMessageResponse, true);

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
    }

    // Thanks to https://stackoverflow.com/a/13212994
    public function generateRandomString($length = 10): string
    {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyz', ceil($length/strlen($x)) )),1,$length);
    }
}