<?php

require_once '../Classes/KaijuRequest.php';

class KaijuMethods
{
    protected $botToken;
    protected $GuildId;

    public function __construct($botToken, $GuildId)
    {
        if (empty($botToken)) {
            die('Empty Discord Bot Token');
        }

        if (empty($GuildId)) {
            die('Invalid Guild Id');
        }

        $this->botToken = $botToken;
        $this->GuildId = $GuildId;
    }

    public function JoinGuild($AccessToken, $UserId) : bool
    {
        if (!empty($AccessToken) && !empty($UserId)) {

            $BodyJson = json_encode(array(
                "access_token" => $AccessToken
            ));

            $Url = "https://discord.com/api/guilds/$this->GuildId/members/$UserId";

            $Headers = array(
                'Content-Type: application/json',
                'Authorization: Bot ' . $this->botToken
            );

            $UserGuildJoinResponse = KaijuRequest::SendRequest($Url, false, $BodyJson, $Headers, "PUT");

            $StatusCode = $UserGuildJoinResponse['status_code'];

            return $StatusCode >= 200 && $StatusCode < 300;
        }

        return false;
    }
}