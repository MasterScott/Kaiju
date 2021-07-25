<?php

class KaijuMethods
{

    protected string $AccessToken;
    protected string $botToken;

    public function __construct($AccessToken, $botToken)
    {
        $this->botToken = $botToken;
        $this->AccessToken = $AccessToken;
    }

    /**
     * @throws Exception
     */
    public function JoinGuild($GuildId)
    {
        $BodyJson = json_encode(array(
            "access_token" => $this->AccessToken ?? throw new Exception("The access-token cannot be null!")
        ));

        $Headers = array(
            'Content-Type: application/json',
            'Authorization: Bot ' . $this->botToken ?? throw new Exception('Null Bot Token')
        );
    }
}