<?php
require 'db.php';
require '../vendor/autoload.php';
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

$app = new Slim\App;

$app->post('/login', function (ServerRequestInterface $request, ResponseInterface $response) {
    $user = $request->getParsedBody()['username'];
    $pwd = $request->getParsedBody()['password'];

    if (isset($user) && isset($pwd)) {
        $query = "SELECT user_id FROM user 
            WHERE email_address=:username AND 
            password=:password";
        try {
            $db = getDB();
            $stmt = $db->prepare($query);
            $stmt->execute([':username' => $user, ':password' => $pwd]);
            $user_id = $stmt->fetch();
            $user_id = json_encode($user_id);
            $db = null;

            if ($user_id) {
                return $response->withJSON($user_id);
            } else {
                return $response->withStatus(401);
            }
        } catch (PDOException $e) {
            echo '{"error":{"text":' . $e->getMessage() . '}}';
        }
    } else {
        return $response->withStatus(401);
    }
    return $response->withStatus(200);
});

$app->post('/register', function (ServerRequestInterface $request, ResponseInterface $response) {
    $user = $request->getParsedBody()['username'];
    $firstName = $request->getParsedBody()['firstName'];
    $lastName = $request->getParsedBody()['lastName'];
    $pwd = $request->getParsedBody()['password'];

    if (isset($user) && isset($pwd) && isset($firstName) && isset($lastName)) {
        $query = "INSERT INTO user (first_name, last_name, email_address, password) 
            VALUES (?, ?, ?, ?)";
        try {
            $db = getDB();
            $stmt = $db->prepare($query);
            $status = $stmt->execute([
                $firstName,
                $lastName,
                $user,
                $pwd
            ]);
            $db = null;

            if ($status) {
                return $response->withStatus(200);
            } else {
                return $response->withStatus(401);
            }
        } catch (PDOException $e) {
            echo '{"error":{"text":' . $e->getMessage() . '}}';
        }
    } else {
        return $response->withStatus(401);
    }
    return $response->withStatus(200);
});

$app->post('/history', function (ServerRequestInterface $request, ResponseInterface $response) {
    //$userID = $request->getParsedBody()['user_id'];
    $userID= "1";
    $placeID = $request->getParsedBody()['place_id'];

    if (isset($userID) && isset($placeID)) {
        $query = "INSERT INTO visited_place (visited_on, user_id, place_id) 
            VALUES (?, ?, ?)";
        $date = date("Y-m-d");
        try {
            $db = getDB();
            $stmt = $db->prepare($query);

            $status = $stmt->execute([
                $date,
                $userID,
                $placeID,
            ]);
            $db = null;

            if ($status) {
                return $response->withStatus(200);
            } else {
                return $response->withStatus(401);
            }
        } catch (PDOException $e) {
            echo '{"error":{"text":' . $e->getMessage() . '}}';
        }
    } else {
        return $response->withStatus(401);
    }
    return $response->withStatus(200);
});

$app->run();

?>