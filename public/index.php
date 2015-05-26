<?php

const PASS_LENGTH = 4;
require_once '../vendor/autoload.php';
require_once '../generated-conf/config.php';

use Quote\Quote;
use Quote\QuoteQuery;
use Quote\User;
use Quote\UserQuery;
use QuoteEnd\Error;
use QuoteEnd\Helpers;
use Slim\Slim;

$app = new Slim();


$app->get('/quote/:id', function ($id)  use ($app) {
  $quoteQuery = new QuoteQuery();
  $quote = $quoteQuery->findPk($id);

  if ($quote == null) {
    $app->response->setStatus(404);
    $error = new Error();
    $app->halt($error->getStatus(), json_encode($error));
  }

  $app->response()->setBody($quote->toJSON());
});

$app->get('/quote', function () use ($app) {
  $quotes = QuoteQuery::create()->orderById()->find();
  $app->response()->setBody($quotes->toJSON());
});

$app->post('/quote', function() use ($app) {

  $request = $app->request;
  $title = $request->post("title");
  $quote_body = $request->post("quote");

  if (Helpers::isNullOrEmpty($title) || Helpers::isNullOrEmpty($quote_body)) {
    $error = new Error();
    $error->setStatus(400);
    $error->setDescription("Invalid data");
    $app->halt($error->getStatus(), json_encode($error));
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

$app->post("/register", function() use ($app) {
  $request = $app->request;
  $username = $request->post("username");
  $password = $request->post("password");

  if (Helpers::isNullOrEmpty($username) || Helpers::isNullOrEmpty($password)) {
    $error = new Error();
    $error->setStatus(400);
    $error->setDescription("Password / Username can't be empty");
    $app->halt($error->getStatus(), json_encode($error));
  }

  if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
    $error = new Error();
    $error->setStatus(400);
    $error->setDescription("Username must be a valid e-mail");
    $app->halt($error->getStatus(), json_encode($error));
  }

  if (strlen($password) < PASS_LENGTH) {
    $error = new Error();
    $error->setStatus(400);
    $error->setDescription("Password must be at least ". PASS_LENGTH . " characters long");
    $app->halt($error->getStatus(), json_encode($error));
  }

  $password_hash = password_hash($password, PASSWORD_DEFAULT);

  $user = UserQuery::create()->findByUsername($username);

  if ($user != null) {
    $error = new Error();
    $error->setStatus(409);
    $error->setDescription("User already exists");
    $app->halt($error->getStatus(), json_encode($error));
  }

  $user = new User();
  $user->setUsername($username);
  $user->setPassword($password_hash);
  $user->setApproved(false);
  $user->setAdmin(false);
  $user->save();
});

$app->post("/login", function() use ($app) {
  $request = $app->request;
  $username = $request->post("username");
  $password = $request->post("password");

  if (Helpers::isNullOrEmpty($username) || Helpers::isNullOrEmpty($password)) {
    $error = new Error();
    $error->setStatus(400);
    $error->setDescription("Password / Username can't be empty");
    $app->halt($error->getStatus(), json_encode($error));
  }

  $user = UserQuery::create()->findByUsername($username)->getFirst();

  if ($user == null) {
    $error = new Error();
    $error->setStatus(400);
    $error->setDescription("Invalid username or password");
    $app->halt($error->getStatus(), json_encode($error));
  }

  $password_hash = password_hash($password, PASSWORD_DEFAULT);

  if ($user->getPassword() != $password_hash) {
    $error = new Error();
    $error->setStatus(400);
    $error->setDescription("Invalid username or password");
    $app->halt($error->getStatus(), json_encode($error));
  }


});

$app->response->header("Content-Type", "application/json");
$app->run();