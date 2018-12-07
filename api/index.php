<?php
require 'db.php';
require '../vendor/autoload.php';
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

$app = new Slim\App;

$app->post('/login', function (ServerRequestInterface $request, ResponseInterface $response) {
    $user = $request->getParsedBody()['username'];
    $pwd = $request->getParsedBody()['password'];
    /*$user = "123";
    $pwd = "456";*/

    if (isset($user) && isset($pwd)) {
        $query = "SELECT user_id FROM user
          WHERE email_address=:username AND 
          password=:password";
        try {
            $db = getDB();
            $stmt = $db->prepare($query);
            $stmt->execute([':username' => $user, ':password' => $pwd]);
            $check = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($check)) {
                $userID = $check[0]['user_id'];
                $history = "SELECT name, rating, price_level, vicinity
                    FROM visited_place WHERE user_id=:userID 
                    ORDER BY visited_on DESC LIMIT 5";
                try {
                    $stmt = $db->prepare($history);
                    $stmt->execute([':userID' => $userID]);
                    $hist = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    // The user has history
                    if ($hist) {
                        $hist['user_id'] = $userID;
                        return $response->withJSON(json_encode($hist));
                    }
                    // The user does not have history
                    else {
                        return $response->withJSON(json_encode($check[0]));
                    }
                } catch (PDOException $e) {
                    echo '{"error":{"text":' . $e->getMessage() . '}}';
                }
            } else { // The user does not exist
                return $response->withStatus(401);
            }
        } catch (PDOException $e) {
            echo '{"error":{"text":' . $e->getMessage() . '}}';
        }
    } else { // No user info provided
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

            if ($status) {
                $query2 = "SELECT user_id FROM user
                  WHERE email_address=:username AND 
                  password=:password";
                $stmt = $db->prepare($query2);
                $stmt->execute([':username' => $user, ':password' => $pwd]);
                $check = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $user_id = json_encode($check[0]);
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

$app->post('/history', function (ServerRequestInterface $request, ResponseInterface $response) {
    $userID = $request->getParsedBody()['userID'];
    //$placeID = $request->getParsedBody()['restoID'];
    $name = $request->getParsedBody()['name'];
    $rating = $request->getParsedBody()['rating'];
    $price_level = $request->getParsedBody()['price_level'];
    $vicinity = $request->getParsedBody()['vicinity'];

    if (isset($userID) && isset($name) && isset($rating) && isset($price_level) && isset($vicinity)) {
        $query = "INSERT INTO visited_place (visited_on, user_id, name, rating, price_level, vicinity) 
            VALUES (?, ?, ?, ?, ?, ?)";
        $date = date("Y-m-d");
        try {
            $db = getDB();
            $stmt = $db->prepare($query);

            $status = $stmt->execute([
                $date,
                $userID,
                $name,
                $rating,
                $price_level,
                $vicinity
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