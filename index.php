<?php

session_start();

require_once 'Classes/Kaiju.php';
require_once 'Include.php';

$KaijuHandler = new Kaiju(Client_Id, RedirectUrl, Secret_Id);

$DBConnect = $KaijuHandler->ConnectDatabase(DATABASE_HOST, DATABASE_NAME, DATABASE_USERNAME, DATABASE_PASSWORD);

if (!$DBConnect) {
    die('Need Database Connection');
}

$errorMessage = null;
$IsLogged = false;

if (isset($_GET['code']) && isset($_GET['state']))
{
    $KaijuLogIn = $KaijuHandler->LogIn($_GET);

    if ($KaijuLogIn) {

        $userInfo = $KaijuHandler->GetUserInfo();

        # With this Token you can make the user enter a server, this can be used in case your server is suspended
        $AccessToken = $userInfo['accessToken'];

        $Username = $userInfo['Username'];
        $Discriminator = $userInfo['Discrim'];
        $accountId = $userInfo['Id'];
        $AvatarUrl = $userInfo['AvatarUrl'];
        $Locale = $userInfo['Locale'];

        $IsLogged = true;
    }
    else {

        // Failed log in
        $errorMessage = 'Please refresh the page to try again.';
    }
}
else {
    try {
        $LogInUrl = $KaijuHandler->GenerateUrl();
    }
    catch (NullBotParameter $e) { # will throw this exception if a bot configuration is missing
        $errorMessage = $e->getMessage(); # Custom Message
    }
    catch (Exception $e) {
        echo $e->getMessage();
    }
}

?>


<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300&display=swap" rel="stylesheet">

    <title>Discord LogIn</title>
</head>

<body style="background-color: #101010">

<style>
    h4 {
        color: white;
        font-family: 'Roboto', sans-serif;
    }
    h3 {
        font-family: 'Roboto', sans-serif;
        color: white;
    }
    p {
        font-family: 'Roboto', sans-serif;
        color: white;
    }
    a {
        color: white;
    }
    ol {
        color: white;
    }

    .center {
        text-align: center;
        list-style-position: inside;
    }

    .footer {
        position: fixed;
        left: 0;
        bottom: 0;
        width: 100%;
        background-color: #212020;
        color: white;
        text-align: center;
    }
</style>

<br />
<br />
<br />
<br />
<br />

<h3 align="center">Steps to log into the server: Discord Server Test:</h3>
<h4 class="center">
    <ol>
        <li>Login with discord through this page (check the login link: https://discord.com/).</li>
        <li>After logging in with the account you want to verify, a unique token will appear, copy that key.</li>
        <li>Join on the server and type <i>>verify Your Token</i> in the verification channel.</li>
        <li>You will already be verified on the server! Remember to follow the rules imposed on it.</li>
    </ol>
</h4>

<br />

<center>

    <?php if ($IsLogged): ?>

        <h3>Logged In!</h3>
        <p>Username: <?php echo $Username; ?></p>
        <p>Discrim: <?php echo $Discriminator; ?></p>
        <p>AccountId: <?php echo $accountId; ?></p>
        <p>Avatar Url: <?php echo $AvatarUrl; ?></p>
        <p>Locale: <?php echo $Locale; ?></p>

    <?php elseif ($errorMessage == null): ?>

        <h3><a href="<?php echo $LogInUrl ?>">Click Here to Log In</a></h3>

        <br />

        <p>
            Kaiju will only collect your profile information (Id, Username, Discriminator) <br />
            and the permission to put your account on a server, this is only for special <br />
            cases in which the server you are logged into is suspended.
        </p>

    <?php else: ?>

        <p>Error: <?php echo $errorMessage ?></p>

    <?php endif ?>

    <br />
    <br />

</center>

<div class="footer">
    <p>Kaiju 1.0.0 - <a href="https://github.com/biitez/Kaiju" target="_blank">See it on GitHub</a></p>
</div>

</body>
</html>

