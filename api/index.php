<?php
require 'db.php';
require '../vendor/autoload.php';
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

$app = new Slim\App;

//https://stackoverflow.com/questions/2040240/php-function-to-generate-v4-uuid
function generateUuid () {
    return sprintf("%04x%04x-%04x-%04x-%04x-%04x%04x%04x",
        // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

        // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

        // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}

function emailExists ($emailAddress) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT EXISTS(
            SELECT
                *
            FROM
                user
            WHERE
                emailAddress = :emailAddress
        ) AS `exists`
    ");
    $stmt->execute([":emailAddress" => $emailAddress]);
    $result = $stmt->fetchObject();
    if (!$result) {
        return false;
    }
    return $result->exists;
}

function getUser ($authenticationToken) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT
            user.userId,
            user.displayName,
            authenticationToken.authenticationToken
        FROM
            authenticationToken
        JOIN
            user
        ON
            user.userId = authenticationToken.userId
        WHERE
            authenticationToken.authenticationToken = :authenticationToken
    ");
    $stmt->execute([":authenticationToken" => $authenticationToken]);
    $user = $stmt->fetchObject();
    if (!$user) {
        return null;
    }
    $stmt = $db->prepare("
        SELECT
            placeId,
            name,
            rating,
            priceLevel,
            vicinity,
            matchedAt
        FROM
            `match`
        WHERE
            userId = :userId
        ORDER BY
            matchedAt DESC
    ");
    $stmt->execute([":userId" => $user->userId]);
    $user->matches = $stmt->fetchAll();
    return $user;
}

function logOut ($authenticationToken) {
    $db = getDB();
    $stmt = $db->prepare("
        DELETE FROM
            authenticationToken
        WHERE
            authenticationToken.authenticationToken = :authenticationToken
    ");
    $stmt->execute([":authenticationToken" => $authenticationToken]);
}

function getOrCreateAuthenticationToken ($userId) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT
            authenticationToken.authenticationToken
        FROM
            authenticationToken
        WHERE
            userId = :userId
        LIMIT 1
    ");
    $stmt->execute([ ":userId" => $userId ]);
    $existing = $stmt->fetchObject();
    if ($existing !== false) {
        return $existing->authenticationToken;
    }

    $authenticationToken = generateUuid();
    $stmt = $db->prepare("
        INSERT INTO
            authenticationToken (userId, authenticationToken)
        VALUES (
            :userId,
            :authenticationToken
        )
    ");
    $stmt->execute([
        "userId" => $userId,
        "authenticationToken" => $authenticationToken,
    ]);
    return $authenticationToken;
}

function logIn ($emailAddress, $password) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT
            userId,
            hashedPassword
        FROM
            user
        WHERE
            emailAddress = :emailAddress
    ");
    $stmt->execute([
        ":emailAddress" => $emailAddress,
    ]);
    $user = $stmt->fetchObject();
    if (!$user) {
        return null;
    }
    if (!password_verify($password, $user->hashedPassword)) {
        return null;
    }
    $authenticationToken = getOrCreateAuthenticationToken($user->userId);
    return getUser($authenticationToken);
}

function register ($emailAddress, $password, $displayName) {
    if (emailExists($emailAddress)) {
        return null;
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT, [ "cost" => 14 ]);

    $db = getDB();
    $stmt = $db->prepare("
        INSERT INTO
            user (emailAddress, hashedPassword, displayName)
        VALUES (
            :emailAddress, :hashedPassword, :displayName
        )
    ");
    $stmt->execute([
        ":emailAddress" => $emailAddress,
        ":hashedPassword" => $hashedPassword,
        ":displayName" => $displayName,
    ]);
    return logIn($emailAddress, $password);
}

function match ($authenticationToken, $match) {
    $db = getDB();
    $stmt = $db->prepare("
        INSERT INTO
            `match` (userId, placeId, name, rating, priceLevel, vicinity)
        SELECT
            authenticationToken.userId,
            :placeId,
            :name,
            :rating,
            :priceLevel,
            :vicinity
        FROM
            authenticationToken
        WHERE
            authenticationToken.authenticationToken = :authenticationToken
    ");
    $stmt->execute([
        ":authenticationToken" => $authenticationToken,
        ":placeId" => $match["placeId"],
        ":name" => $match["name"],
        ":rating" => $match["rating"],
        ":priceLevel" => $match["priceLevel"],
        ":vicinity" => $match["vicinity"],
    ]);
    if ($stmt->rowCount() != 1) {
        return null;
    }
    return $match;
}

$app->post("/register", function ($req, $res) {
    try {
        $body = $req->getParsedBody();
        $emailAddress = $body["emailAddress"];
        $password = $body["password"];
        $displayName = $body["displayName"];
        if (!is_string($emailAddress)) {
            return $res
                ->withStatus(400)
                ->withJson([
                    "errorMessage" => "Expected a string for email address"
                ]);
        }
        if (strlen($emailAddress) > 191) {
            return $res
                ->withStatus(400)
                ->withJson([
                    "errorMessage" => "Email address must be 191 characters or less"
                ]);
        }
        if (!preg_match("/^.+@.+$/", $emailAddress)) {
            return $res
                ->withStatus(400)
                ->withJson([
                    "errorMessage" => "Please enter a valid email address"
                ]);
        }
        if (!is_string($password)) {
            return $res
                ->withStatus(400)
                ->withJson([
                    "errorMessage" => "Expected a string for password"
                ]);
        }
        if (strlen($password) < 10) {
            return $res
                ->withStatus(400)
                ->withJson([
                    "errorMessage" => "Password must be 10 characters or more"
                ]);
        }
        if (!is_string($displayName)) {
            return $res
                ->withStatus(400)
                ->withJson([
                    "errorMessage" => "Expected a string for display name"
                ]);
        }
        if (strlen($displayName) < 5) {
            return $res
                ->withStatus(400)
                ->withJson([
                    "errorMessage" => "Display name must be 5 characters or more"
                ]);
        }
        $user = register(
            $emailAddress,
            $password,
            $displayName
        );
        if (is_null($user)) {
            return $res
                ->withStatus(400)
                ->withJson([
                    "errorMessage" => "User already exists"
                ]);
        }
        return $res->withJson($user);
    } catch (Exception $ex) {
        return $res
            ->withStatus(500)
            ->withJson([
                "errorMessage" => $ex->getMessage()
            ]);
    }
});
$app->post("/log-in", function ($req, $res) {
    try {
        $body = $req->getParsedBody();
        $emailAddress = $body["emailAddress"];
        $password = $body["password"];
        if (!is_string($emailAddress)) {
            return $res
                ->withStatus(400)
                ->withJson([
                    "errorMessage" => "Expected a string for email address"
                ]);
        }
        if (!is_string($password)) {
            return $res
                ->withStatus(400)
                ->withJson([
                    "errorMessage" => "Expected a string for password"
                ]);
        }
        $user = logIn(
            $emailAddress,
            $password
        );
        if (is_null($user)) {
            return $res
                ->withStatus(400)
                ->withJson([
                    "errorMessage" => "Invalid email address or password"
                ]);
        }
        return $res->withJson($user);
    } catch (Exception $ex) {
        return $res
            ->withStatus(500)
            ->withJson([
                "errorMessage" => $ex->getMessage()
            ]);
    }
});
$app->post("/me", function ($req, $res) {
    try {
        $authenticationToken = $req->getHeader("Authorization");
        if (!is_array($authenticationToken) || count($authenticationToken) == 0) {
            return $res
                ->withStatus(400)
                ->withJson([
                    "errorMessage" => "Please log in"
                ]);
        }
        $user = getUser(
            $authenticationToken[0]
        );
        if (is_null($user)) {
            return $res
                ->withStatus(401)
                ->withJson([
                    "errorMessage" => "Please log in"
                ]);
        }
        return $res->withJson($user);
    } catch (Exception $ex) {
        return $res
            ->withStatus(500)
            ->withJson([
                "errorMessage" => $ex->getMessage()
            ]);
    }
});
/*
    TODO Not trust the client with the place information.
    Use the Place API on the server to gather this data.
*/
$app->post("/me/place/{placeId}/match", function ($req, $res, $args) {
    try {
        $authenticationToken = $req->getHeader("Authorization");
        if (!is_array($authenticationToken) || count($authenticationToken) == 0) {
            return $res
                ->withStatus(400)
                ->withJson([
                    "errorMessage" => "Please log in"
                ]);
        }
        $body = $req->getParsedBody();
        $body["placeId"] = $args["placeId"];

        $placeId = $body["placeId"];
        $name = $body["name"];
        $rating = $body["rating"];
        $priceLevel = $body["priceLevel"];
        $vicinity = $body["vicinity"];
        if (!is_string($placeId)) {
            return $res
                ->withStatus(400)
                ->withJson([
                    "errorMessage" => "Expected a string for placeId"
                ]);
        }
        if (!is_string($name)) {
            return $res
                ->withStatus(400)
                ->withJson([
                    "errorMessage" => "Expected a string for name"
                ]);
        }
        if (
            !is_null($rating) &&
            (!is_float($rating) || $rating < 1 || $rating > 5)
        ) {
            return $res
                ->withStatus(400)
                ->withJson([
                    "errorMessage" => "Expected [1.0, 5.0]|null for rating"
                ]);
        }
        if (
            !is_null($priceLevel) &&
            (!is_int($priceLevel) || $priceLevel < 0 || $priceLevel > 4)
        ) {
            return $res
                ->withStatus(400)
                ->withJson([
                    "errorMessage" => "Expected 0|1|2|3|4|null for price level"
                ]);
        }
        if (!is_string($vicinity)) {
            return $res
                ->withStatus(400)
                ->withJson([
                    "errorMessage" => "Expected a string for vicinity"
                ]);
        }
        $match = match(
            $authenticationToken[0],
            $body
        );
        if (is_null($match)) {
            return $res
                ->withStatus(400)
                ->withJson([
                    "errorMessage" => "Already matched"
                ]);
        }
        return $res->withJson($match);
    } catch (Exception $ex) {
        return $res
            ->withStatus(500)
            ->withJson([
                "errorMessage" => $ex->getMessage()
            ]);
    }
});
$app->get('/', function ($req, $res) {
    return $res->withJson([
        "hello" => "world!"
    ]);
});

$app->run();

?>
