<?php

require_once '../Include.php';
require_once '../Classes/DatabaseControl.php';

header('Content-Type: application/json');

$DatabaseHandler = new DatabaseControl(DATABASE_HOST, DATABASE_NAME, DATABASE_USERNAME, DATABASE_PASSWORD);

$DatabaseConnection = $DatabaseHandler->GetPdoConnection();

$RequestMethod = $_SERVER['REQUEST_METHOD'];

if ($RequestMethod == "POST") {
    if (!isset($_POST['SecretKey']) || !isset($_POST['Method']) || !isset($_POST['UserId'])) {
        JsonPrint('INVALID_PARAMETERS', 400);
    }

    $SecretKey = $_POST['SecretKey'];

    if ($SecretKey !== APISecretKey) {
        JsonPrint('INVALID_API_SECRET_KEY', 401);
    }

    # If you want to extend this API, here you can put the methods you want to handle from the Discord bot.
    $AvailablesMethods = array(
        'REMOVE_USER',
        'VERIFY_USER'
    );

    $KaijuMethod = $_POST['Method'];

    # Method not found in array
    if (!in_array($KaijuMethod, $AvailablesMethods)) {
        JsonPrint('UNFOUND_KAIJU_METHOD', 404);
    }

    $DiscordUserId = $_POST['UserId'];

    if (empty($DiscordUserId)) {
        JsonPrint('EMPTY_USER_ID', 400);
    }

    # Here you can add your owns API management
    switch ($KaijuMethod) {
        case 'REMOVE_USER':
            $RemoveUserQuery = $DatabaseConnection->prepare('DELETE FROM users WHERE account_id = ?');
            if (!$RemoveUserQuery->execute([$DiscordUserId])) {
                JsonPrint('INTERNAL_DATABASE_ERROR', 500);
            }
            JsonPrint('USER_REMOVE', 200);
            break;

        case 'VERIFY_USER':

            if (!isset($_POST['Token']) || empty($_POST['Token'])) {
                JsonPrint('INVALID_TOKEN', 400);
            }
            $VerificationToken = $_POST['Token'];

            $SearchTokenExistence = $DatabaseConnection->prepare('SELECT COUNT(*) FROM users WHERE verification_code = ?');

            if (!$SearchTokenExistence->execute([$VerificationToken])) {
                JsonPrint('INTERNAL_DATABASE_ERROR', 500);
            }

            if ($SearchTokenExistence->fetchColumn() < 1) { # Not exist
                JsonPrint('UNFOUND_TOKEN', 404);
            }

            $GetTokenUserId = $DatabaseConnection->prepare('SELECT account_id FROM users WHERE verification_code = ?');

            if (!$GetTokenUserId->execute([$VerificationToken])) {
                JsonPrint('INTERNAL_DATABASE_ERROR', 500);
            }

            $BindedUserId = $GetTokenUserId->fetchColumn();

            if ($BindedUserId !== $DiscordUserId) {
                # The Token does not belong to the user who tries to verify his account on the server
                JsonPrint('NOT_BINDED', 401);
            }

            # All checks were successful, so the user can be verified on the server
            JsonPrint('USER_VERIFIED', 200);

            break;

        default:
            JsonPrint('UNHANDLED_API_METHOD', 500);
            break;
    }
}
else {
    JsonPrint('BAD_REQUEST_METHOD', 400);
}

function JsonPrint($HttpResponseCode, $StatusCode) {
    http_response_code($StatusCode);
    die(json_encode(array(
        'StatusServer' => $HttpResponseCode,
        'HttpStatusCode' => $StatusCode
    ), JSON_PRETTY_PRINT));
}