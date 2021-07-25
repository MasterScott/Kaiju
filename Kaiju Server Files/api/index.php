<?php

require_once '../Include.php';
require_once '../Classes/DatabaseControl.php';
require_once '../Classes/KaijuMethods.php';

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
        'VERIFY_USER',
        'MIGRATE_USERS',
        'VERIFY_ENTRY'
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
        case 'VERIFY_ENTRY':
            $SearchUserInDatabase = $DatabaseConnection->prepare('SELECT COUNT(*) FROM users WHERE account_id = ?');
            if (!$SearchUserInDatabase->execute([$DiscordUserId])) {
                JsonPrint('INTERNAL_DATABASE_ERROR', 500);
            }
            $UsersCount = $SearchUserInDatabase->fetchColumn();

            if ($UsersCount < 1) { # Not found, the user need verification
                JsonPrint('USER_NOT_VERIFIED', 404);
            }

            # Already verified
            JsonPrint('USER_IN_DATABASE', 202);

            break;

        case 'MIGRATE_USERS': # Move all users on the database to the new server

            if (!isset($_POST['ServerId'])) {
                JsonPrint('SERVER_ID_REQUIRED', 400);
            }

            if (empty(Bot_Token)) {
                JsonPrint('BAD_TOKEN_DISCORD_BOT_INCLUDED', 404);
            }

            $TotalUsers = $DatabaseConnection->prepare('SELECT COUNT(*) FROM users');

            if (!$TotalUsers->execute()) {
                JsonPrint('INTERNAL_DATABASE_ERROR', 500);
            }

            $TotalUsersCount = $TotalUsers->fetchColumn();

            if ($TotalUsersCount < 1) {
                JsonPrint('NO_USERS', 404);
            }

            $MethodInitialization = new KaijuMethods(Bot_Token, $_POST['ServerId']);

            $GetAllUsersQuery = $DatabaseConnection->prepare('SELECT access_token,account_id FROM users');

            $Success = 0;
            $Fails = 0;

            $GetAllUsersQuery->execute();

            while ($dbRow = $GetAllUsersQuery->fetch(PDO::FETCH_NUM)) {
                if (empty($dbRow[0]) || empty($dbRow[1])) continue;
                if ($MethodInitialization->JoinGuild($dbRow[0], $dbRow[1])) {
                    $Success++;
                } else {
                    $Fails++;
                }
            }

            # Check the status code to handle this
            JsonPrint("$TotalUsersCount|$Success|$Fails", 200);

            break;

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