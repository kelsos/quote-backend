<?php

const PASS_LENGTH = 4;
require_once '../vendor/autoload.php';
require_once '../generated-conf/config.php';

use Quote\Quote;
use Quote\QuoteQuery;
use Quote\User;
use Quote\UserQuery;
use QuoteEnd\Helpers;
use Propel\Runtime\Map\TableMap;
use Slim\Slim;
use Symfony\Component\Yaml\Yaml;

$app = new Slim();
$config = Yaml::parse(file_get_contents("../config.yaml"));

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
  if (!strcmp($request->getContentType(), 'application/json')) {
    Helpers::error(400, "Invalid request", $app);
  }

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
  $username = $request->post("username");
  $password = $request->post("password");

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

$app->post("/login", function () use ($app, $secret) {
  $request = $app->request();
  $contentType = $request->getContentType();

  if (strcmp($contentType, 'application/json') != 0) {
    Helpers::error(400, "Invalid request body", $app);
  }

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

  $app->setCookie("access_token", $jwt, $token['exp'], null, 'quote.inertia-blues.net', null, true);
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

  $contentType = $app->request()->getContentType();

  if (strcmp($contentType, 'application/json') != 0) {
    Helpers::error(400, "Invalid request body", $app);
  }

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

$app->response->header("Content-Type", "application/json");
$app->run();