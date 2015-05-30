<?php

const PASS_LENGTH = 4;
require_once '../vendor/autoload.php';
require_once '../generated-conf/config.php';

use Quote\Quote;
use Quote\QuoteQuery;
use Quote\User;
use Quote\UserQuery;
use QuoteEnd\Helpers;
use Slim\Slim;
use Symfony\Component\Yaml\Yaml;

$app = new Slim();
$config = Yaml::parse(file_get_contents("../config.yaml"));

$secret = $config['secret'];

$request = $app->request;
$token = $request->post("token") == null
  ? $app->request->get("token")
  : $app->request->post("token");

$app->get('/quote/:id', function ($id) use ($app, $token, $secret) {
  Helpers::validateToken($token, $secret, $app);
  $quoteQuery = new QuoteQuery();
  $quote = $quoteQuery->findPk($id);

  if ($quote == null) {
    Helpers::error(404, "Quote does not exist yes!", $app);
  }

  $app->response()->setBody($quote->toJSON());
});

$app->get('/quote', function () use ($app, $token, $secret) {
  Helpers::validateToken($token, $secret, $app);

  $quotes = QuoteQuery::create()->orderById()->find();
  $app->response()->setBody($quotes->toJSON());
});

$app->post('/quote', function () use ($app, $token, $secret) {
  Helpers::validateToken($token, $secret, $app);

  $request = $app->request;
  $title = $request->post("title");
  $quote_body = $request->post("quote");

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

$app->post("/register", function () use ($app) {
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

  $app->response()->setBody(json_encode($result));

});

$app->post("/login", function () use ($app, $secret) {
  $request = $app->request;
  $username = $request->post("username");
  $password = $request->post("password");

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

  $app->response()->setBody(json_encode($result));
});

$app->response->header("Content-Type", "application/json");
$app->run();