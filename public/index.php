<?php

const PASS_LENGTH = 4;
require_once '../vendor/autoload.php';
require_once '../generated-conf/config.php';

use Propel\Runtime\Map\TableMap;
use Quote\Quote;
use Quote\QuoteQuery;
use Quote\User;
use Quote\UserQuery;
use QuoteEnd\Helpers;
use Slim\Slim;
use Symfony\Component\Yaml\Yaml;

$config = Yaml::parse(file_get_contents("../config.yaml"));

/**
 * Development only code to help with CORS issues when sending requests from
 * the Grunt server
 */
$development = strcmp($config['environment'], 'development');

if ($development) {
  if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']) && (
            $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'POST' ||
            $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'DELETE' ||
            $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'PUT' )) {
      header('Access-Control-Allow-Origin: *');
      header("Access-Control-Allow-Credentials: true");
      header('Access-Control-Allow-Headers: X-Requested-With');
      header('Access-Control-Allow-Headers: Content-Type');
      header('Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT'); // http://stackoverflow.com/a/7605119/578667
      header('Access-Control-Max-Age: 86400');
    }
    exit;
  }
}

$app = new Slim();

$secret = $config['secret'];
$mail = $config['mail'];

$request = $app->request;
$token = Helpers::getUserToken($app);

$app->get('/', function () use ($app) {

  $response = [
      'application' => 'Quote Backend',
      'version' => 1.0
  ];

  $app->response()->setBody(json_encode($response));
});

$app->get('/quote/:id', function ($id) use ($app, $token, $secret) {
  Helpers::validateToken($token, $secret, $app);
  $quoteQuery = new QuoteQuery();
  $quote = $quoteQuery->findPk($id);

  if ($quote == null) {
    Helpers::error(404, "Quote does not exist yes!", $app);
  }

  $app->response()->setBody(json_encode($quote->toArray(TableMap::TYPE_FIELDNAME)));
});

$app->get('/quote', function () use ($app, $token, $secret) {
  Helpers::validateToken($token, $secret, $app);
  $quotes = QuoteQuery::create()->orderById()->find()->toArray(null, false, TableMap::TYPE_FIELDNAME, true);
  $app->response()->setBody(json_encode($quotes));
});

$app->post('/quote', function () use ($app, $token, $secret) {
  Helpers::validateToken($token, $secret, $app);
  $request = $app->request();

  Helpers::checkForJsonRequest($app);

  $body = json_decode($request->getBody());

  $quote_body = $body->{'quote'};
  $title = $body->{'title'};

  if (Helpers::isNullOrEmpty($title) || Helpers::isNullOrEmpty($quote_body)) {
    Helpers::error(400, "Invalid data", $app);
  }

  date_default_timezone_set("UTC");
  $published = date("Y-m-d H:i:s", time());

  $quote = new Quote();
  $quote->setTitle($title);
  $quote->setQuote($quote_body);
  $quote->setPublished($published);
  $rowAffected = $quote->save();

  $result = [
      "success" => $rowAffected > 0
  ];

  $app->response()->setBody(json_encode($result));

});

$app->post("/register", function () use ($app, $mail) {
  $request = $app->request;

  Helpers::checkForJsonRequest($app);

  $body = json_decode($request->getBody());

  $username = $body->{"username"};
  $password = $body->{"password"};

  if (Helpers::isNullOrEmpty($username) || Helpers::isNullOrEmpty($password)) {
    Helpers::error(400, "Password / Username can't be empty", $app);
  }

  if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
    Helpers::error(400, "Username must be a valid e-mail", $app);
  }

  if (strlen($password) < PASS_LENGTH) {
    Helpers::error(400, "Password must be at least " . PASS_LENGTH . " characters long", $app);
  }

  $password_hash = password_hash($password, PASSWORD_DEFAULT);

  $user = UserQuery::create()->findByUsername($username)->getFirst();

  if ($user != null) {
    Helpers::error(409, "User already exists", $app);
  }

  $user = new User();
  $user->setUsername($username);
  $user->setPassword($password_hash);
  $user->setApproved(false);
  $user->setAdmin(false);
  $rowsAffected = $user->save();

  $result = [
      "success" => $rowsAffected > 0
  ];

  if ($rowsAffected > 0) {
    try {
      mail($mail, "New user at Quote", "A new user (" . $username . ") has been registered and awaiting approval");
    } catch (Exception $ex) {

    }
  }

  $app->response()->setBody(json_encode($result));

});

$app->post("/login", function () use ($app, $secret, $config) {
  $request = $app->request();

  Helpers::checkForJsonRequest($app);

  $body = json_decode($request->getBody());

  $username = $body->{"username"};
  $password = $body->{"password"};

  if (Helpers::isNullOrEmpty($username) || Helpers::isNullOrEmpty($password)) {
    Helpers::error(400, "Password / Username can't be empty", $app);
  }

  $user = UserQuery::create()->findByUsername($username)->getFirst();

  if ($user == null) {
    Helpers::error(400, "Invalid username or password", $app);
  }

  if (!$user->isApproved()) {
    Helpers::error(400, "User has not yet been approved by a system administrator", $app);
  }

  if (!password_verify($password, $user->getPassword())) {
    Helpers::error(400, "Invalid username or password", $app);
  }

  $token = array(
      "iat" => time(),
      "nbf" => time(),
      "exp" => time() + 172800,
      "id" => $user->getId()
  );

  $jwt = JWT::encode($token, $secret);

  $result = [
      "token" => $jwt
  ];

  $app->setCookie("access_token", $jwt, $token['exp'], null, $config['domain'], null, true);
  $app->response()->setBody(json_encode($result));
});

$app->get('/admin/users', function () use ($app, $token, $secret) {
  $token_content = Helpers::validateToken($token, $secret, $app);
  $user_id = $token_content->{'id'};
  $active_user = UserQuery::create()->findOneById($user_id);

  if (!$active_user->isAdmin()) {
    Helpers::error(403, "You have not administrative access.", $app);
  }

  $users = UserQuery::create()->orderById()->find()->toArray(null, false, TableMap::TYPE_FIELDNAME, true);

  foreach ($users as &$user) {
    unset($user['password']);
  }

  $app->response()->setBody(json_encode($users));
});

$app->post('/admin/users/', function () use ($app, $token, $secret) {
  $token_content = Helpers::validateToken($token, $secret, $app);
  $user_id = $token_content->{'id'};
  $active_user = UserQuery::create()->findOneById($user_id);

  if (!$active_user->isAdmin()) {
    Helpers::error(403, "You have not administrative access.", $app);
  }

  Helpers::checkForJsonRequest($app);

  $body = json_decode($app->request()->getBody());

  $user_id = $body->{'id'};
  $approved = boolval($app->request()->post('approved'));

  if (!is_int($user_id) || $user_id <= 0 || !is_bool($approved)) {
    Helpers::error(400, "Missing or invalid parameters", $app);
  }

  $user = UserQuery::create()->findOneById($user_id);

  if ($user == null) {
    Helpers::error(404, "Invalid user", $app);
  }

  $user->setApproved($approved);
  $rowsAffected = $user->save();

  $app->response()->setBody(json_encode([
      'success' => $rowsAffected > 0
  ]));
});

$app->notFound(function () use ($app) {
  Helpers::error(404, "Invalid Path", $app);
});

$app->error(function (\Exception $e) use ($app) {
  Helpers::error(500, "Internal server error", $app);
});

$app->post('/password/forgot', function () use ($app, $token, $secret, $mail) {
  Helpers::checkForJsonRequest($app);

  $body = json_decode($app->request()->getBody());

  $email = $body->{'username'};

  $user = UserQuery::create()->findByUsername($email)->getFirst();

  $token = array(
      "iat" => time(),
      "nbf" => time(),
      "exp" => time() + 300,
      "id" => $user->getId(),
      "recovery" => true
  );

  $jwt = JWT::encode($token, $secret);

  $success = true;

  try {
    mail($email, "Password reset requests", "here " . $jwt, null, '-f' . $mail);
  } catch (Exception $ex) {
    $success = false;
  }

  $app->response()->setBody(json_encode([
      'success' => $success,
      'token' => $jwt
  ]));
});

$app->post('/password/change', function () use ($app, $secret, $token) {
  Helpers::checkForJsonRequest($app);

  $body = json_decode($app->request()->getBody());

  if (property_exists($body, 'recovery')) {
    if (!property_exists($body, 'token')) {
      Helpers::error(400, "Missing recovery token", $app);
    }

    $recoveryToken = $body->{'token'};

    if (empty($recoveryToken)) {
      Helpers::error(403, "Not authorized", $app);
    }

    $validatedToken = Helpers::validateRecoveryToken($recoveryToken, $secret, $app);

    if ($validatedToken == null) {
      Helpers::error(403, "Invalid Token", $app);
    }

    Helpers::changePassword($body, $app, $validatedToken);

  } else {
    $validToken = Helpers::validateToken($token, $secret, $app);
    Helpers::changePassword($body, $app, $validToken);
  }
});

$app->response->header("Content-Type", "application/json");

if ($development) {
  $app->response->header("Access-Control-Allow-Origin", '*');
  $app->response->header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");
}

$app->run();