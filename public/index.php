<?php

const PASS_LENGTH = 4;

require_once '../vendor/autoload.php';
require_once '../generated-conf/config.php';

use Propel\Runtime\Map\TableMap;
use Quote\Quote;
use Quote\QuoteQuery;
use Quote\User;
use Quote\UserQuery;
use QuoteEnd\StateManager;
use QuoteEnd\Constants;
use QuoteEnd\Helpers;
use Slim\Route;
use Slim\Slim;

$app = new Slim();

$request = $app->request;

/**
 * Checks and validates the user's request by validating the provided jwt token.
 * @param Route $route
 */
function authenticate(Route $route)
{
  $app = Slim::getInstance();
  $token = Helpers::getUserToken($app);
  $secret = StateManager::getInstance()->getSecret();
  $validated = Helpers::validateToken($token, $secret, $app);
  StateManager::getInstance()->setTokenData($validated);
}

function authorize(Route $route)
{
  $app = Slim::getInstance();
  $user_id = StateManager::getInstance()->getTokenData()->{'id'};
  $active_user = UserQuery::create()->findOneById($user_id);

  if (!$active_user->isAdmin()) {
    Helpers::error(Constants::UNAUTHORIZED, "You have not administrative access.", $app);
  }
}

$app->get('/', function () use ($app) {

  $response = [
    'application' => 'Quote Backend',
    'version' => 1.0
  ];

  $app->response()->setBody(json_encode($response));
});

$app->get('/quote/:id', 'authenticate', function ($id) {
  $app = Slim::getInstance();

  $quoteQuery = new QuoteQuery();
  $quote = $quoteQuery->findPk($id);

  if ($quote == null) {
    Helpers::error(Constants::NOT_FOUND, "Quote does not exist yes!", $app);
  }

  $app->response()->setBody(json_encode($quote->toArray(TableMap::TYPE_FIELDNAME)));
});

$app->get('/quote', 'authenticate', function () {
  $app = Slim::getInstance();
  $quotes = QuoteQuery::create()->orderById()->find()->toArray(null, false, TableMap::TYPE_FIELDNAME, true);
  $app->response()->setBody(json_encode($quotes));
});

$app->post('/quote', 'authenticate', function () {
  $app = Slim::getInstance();
  $request = $app->request();

  Helpers::checkForJsonRequest($app);

  $body = json_decode($request->getBody());

  $quote_body = $body->{'quote'};
  $title = $body->{'title'};

  if (Helpers::isNullOrEmpty($title) || Helpers::isNullOrEmpty($quote_body)) {
    Helpers::error(Constants::INVALID_PARAMETERS, "Invalid data", $app);
  }

  date_default_timezone_set("UTC");
  $published = date("Y-m-d H:i:s", time());

  $quote = new Quote();
  $quote->setTitle($title);
  $quote->setQuote($quote_body);
  $quote->setPublished($published);
  $rowAffected = $quote->save();

  $result = [
    "success" => $rowAffected > 0,
    "code" => Constants::SUCCESS
  ];

  $app->response()->setBody(json_encode($result));

});

$app->post("/register", function () use ($app) {
  $request = $app->request;

  $mail = StateManager::getInstance()->getMail();
  Helpers::checkForJsonRequest($app);

  $body = json_decode($request->getBody());

  if ($body == null) {
    Helpers::error(Constants::INVALID_PARAMETERS, "Bad Request", $app);
  }

  $username = property_exists($body, 'username') ? $body->{"username"} : null;
  $password = property_exists($body, 'password') ? $body->{"password"} : null;

  if (Helpers::isNullOrEmpty($username) || Helpers::isNullOrEmpty($password)) {
    Helpers::error(Constants::INVALID_PARAMETERS, "Password / Username can't be empty", $app);
  }

  if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
    Helpers::error(Constants::INVALID_PARAMETERS, "Username must be a valid e-mail", $app);
  }

  if (strlen($password) < PASS_LENGTH) {
    Helpers::error(Constants::INVALID_PARAMETERS, "Password must be at least " . PASS_LENGTH . " characters long", $app);
  }

  $password_hash = password_hash($password, PASSWORD_DEFAULT);

  $user = UserQuery::create()->findByUsername($username)->getFirst();

  if ($user != null) {
    Helpers::error(Constants::CONFLICT, "User already exists", $app);
  }

  $user = new User();
  $user->setUsername($username);
  $user->setPassword($password_hash);
  $user->setApproved(false);
  $user->setAdmin(false);
  $rowsAffected = $user->save();

  $result = [
    "success" => $rowsAffected > 0,
    "code" => Constants::SUCCESS
  ];

  if ($rowsAffected > 0) {
    try {
      mail($mail, "New user at Quote", "A new user (" . $username . ") has been registered and awaiting approval");
    } catch (Exception $ex) {

    }
  }

  $app->response()->setBody(json_encode($result));

});

$app->post("/login", function () use ($app) {
  $request = $app->request();

  Helpers::checkForJsonRequest($app);

  $body = json_decode($request->getBody());

  if ($body == null) {
    Helpers::error(Constants::INVALID_PARAMETERS, "Bad request", $app);
  }

  $username = property_exists($body, 'username') ? $body->{"username"} : null;
  $password = property_exists($body, 'password') ? $body->{"password"} : null;

  if (Helpers::isNullOrEmpty($username) || Helpers::isNullOrEmpty($password)) {
    Helpers::error(Constants::INVALID_PARAMETERS, "Password / Username can't be empty", $app);
  }

  $user = UserQuery::create()->findByUsername($username)->getFirst();

  if ($user == null) {
    Helpers::error(Constants::INVALID_PARAMETERS, "Invalid username or password", $app);
  }

  if (!$user->isApproved()) {
    Helpers::error(Constants::INVALID_PARAMETERS, "User has not yet been approved by a system administrator", $app);
  }

  if (!password_verify($password, $user->getPassword())) {
    Helpers::error(Constants::INVALID_PARAMETERS, "Invalid username or password", $app);
  }

  $token = array(
    "iat" => time(),
    "nbf" => time(),
    "exp" => time() + 172800,
    "id" => $user->getId()
  );
  $secret = StateManager::getInstance()->getSecret();

  $jwt = JWT::encode($token, $secret);

  $result = [
    "token" => $jwt,
    "code" => Constants::SUCCESS
  ];

  $app->response()->setBody(json_encode($result));
});

$app->get('/admin/users', 'authenticate', 'authorize', function () use ($app) {

  $users = UserQuery::create()->orderById()->find()->toArray(null, false, TableMap::TYPE_FIELDNAME, true);

  foreach ($users as &$user) {
    unset($user['password']);
  }

  $app->response()->setBody(json_encode($users));
});

$app->post('/admin/users/', 'authenticate', 'authorize', function () {
  $app = Slim::getInstance();

  Helpers::checkForJsonRequest($app);

  $body = json_decode($app->request()->getBody());

  $user_id = $body->{'id'};
  $approved = boolval($app->request()->post('approved'));

  if (!is_int($user_id) || $user_id <= 0 || !is_bool($approved)) {
    Helpers::error(Constants::INVALID_PARAMETERS, "Missing or invalid parameters", $app);
  }

  $user = UserQuery::create()->findOneById($user_id);

  if ($user == null) {
    Helpers::error(Constants::NOT_FOUND, "Invalid user", $app);
  }

  $user->setApproved($approved);
  $rowsAffected = $user->save();

  $app->response()->setBody(json_encode([
    'success' => $rowsAffected > 0,
    'code' => Constants::SUCCESS
  ]));
});

$app->notFound(function () use ($app) {
  Helpers::error(Constants::NOT_FOUND, "Invalid Path", $app);
});

$app->error(function (\Exception $e) use ($app) {
  Helpers::error(Constants::SERVER_ERROR, "Internal server error", $app);
});

$app->post('/password/forgot', function () use ($app) {
  $app = Slim::getInstance();
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

  $jwt = JWT::encode($token, StateManager::getInstance()->getSecret());

  $success = true;

  try {
    mail($email, "Password reset requests", "here " . $jwt, null, '-f' . $mail);
  } catch (Exception $ex) {
    $success = false;
  }

  $app->response()->setBody(json_encode([
    'success' => $success,
    'token' => $jwt,
    'code' => Constants::SUCCESS
  ]));
});

$app->post('/password/change', function () use ($app) {
  Helpers::checkForJsonRequest($app);

  $body = json_decode($app->request()->getBody());

  if (property_exists($body, 'recovery')) {
    if (!property_exists($body, 'token')) {
      Helpers::error(Constants::INVALID_PARAMETERS, "Missing recovery token", $app);
    }

    $recoveryToken = $body->{'token'};

    if (empty($recoveryToken)) {
      Helpers::error(Constants::UNAUTHORIZED, "Not authorized", $app);
    }

    $validatedToken = Helpers::validateRecoveryToken($recoveryToken, $secret, $app);

    if ($validatedToken == null) {
      Helpers::error(Constants::UNAUTHORIZED, "Invalid Token", $app);
    }

    Helpers::changePassword($body, $app, $validatedToken);

  } else {
    $validToken = Helpers::validateToken($token, $secret, $app);
    Helpers::changePassword($body, $app, $validToken);
  }
});

$response = $app->response();

$response->headers()->set("Content-Type", "application/json");

$development = StateManager::getInstance()->isDevelopment();

if ($development) {
  $response->headers()->set("Access-Control-Allow-Origin", '*');
  $response->headers()->set("Access-Control-Allow-Methods", "PUT, GET, POST, DELETE, OPTIONS");
  $response->headers()->set("Access-Control-Allow-Headers", "Origin, Authorization, Content-Type, X-Requested-With");
}

if ($development) {
  $app->options("/(:name+)", function () use ($app) {
    $response = $app->response();
    $response->headers()->set("Access-Control-Allow-Origin", '*');
    $response->headers()->set("Access-Control-Allow-Methods", "PUT, GET, POST, DELETE, OPTIONS");
    $response->headers()->set("Access-Control-Allow-Headers", "Origin, Authorization, Content-Type, X-Requested-With");
    $response->setBody("");
  });
}

$app->run();
