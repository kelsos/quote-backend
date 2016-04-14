<?php

const PASS_LENGTH = 4;

require_once '../vendor/autoload.php';
require_once '../generated-conf/config.php';

use Firebase\JWT\JWT;
use helpers\QuoteMailer;
use middleware\Authenticated;
use middleware\Authorized;
use middleware\ValidJson;
use Propel\Runtime\Map\TableMap;
use Quote\Confirmation;
use Quote\ConfirmationQuery;
use Quote\Quote;
use Quote\QuoteQuery;
use Quote\User;
use Quote\UserQuery;
use QuoteEnd\Constants;
use QuoteEnd\Error;
use QuoteEnd\Helpers;
use QuoteEnd\StateManager;
use Slim\Http\Request;
use Slim\Http\Response;

$config = [
  'settings' => [
    'displayErrorDetails' => true,

    'logger' => [
      'name' => 'slim-app',
      'level' => Monolog\Logger::DEBUG,
      'path' => __DIR__ . '/../logs/app.log',
    ],
  ],
];

$app = new Slim\App($config);
$development = StateManager::getInstance()->isDevelopment();

$app->get('/', function (Request $req, Response $resp, $args = []) use ($app) {

  $response = [
    'application' => 'Quote Backend',
    'version' => 1.0
  ];

  return $resp->withJson($response);
});

$app->get('/quote/{id}', function (Request $req, Response $resp, $args = []) {
  $quoteQuery = new QuoteQuery();
  $quote = $quoteQuery->findPk($args["id"]);

  if ($quote == null) {
    $resp->withStatus(Constants::NOT_FOUND);
    return $resp->withJson(new Error(false,Constants::NOT_FOUND, "Quote does not exist yes!"));
  } else {
    return $resp->withJson($quote->toArray(TableMap::TYPE_FIELDNAME));
  }

})->add(Authenticated::class);

$app->get('/quote', function (Request $req, Response $resp, $args = []){
  $limit = $req->getParam("limit");
  $offset = $req->getParam("offset");
  
  if (is_numeric($offset) && is_numeric($limit)) {
    $quoteQuery = QuoteQuery::create()->limit($limit)->offset($offset)->orderById();
    $quotes = $quoteQuery->find()->toArray(null, false, TableMap::TYPE_FIELDNAME, true);

    return $resp->withJson([
      'data'    => $quotes,
      'limit'   => $limit,
      'offset'  => $offset,
      'total'   => QuoteQuery::create()->count()
    ]);
  } else {
    $quotes = QuoteQuery::create()->orderById()->find()->toArray(null, false, TableMap::TYPE_FIELDNAME, true);

    return $resp->withJson(['data' => $quotes]);
  }

})->add(Authenticated::class);

$app->post('/quote', function (Request $req, Response $resp, $args = []) {

  $body = json_decode($req->getBody());

  $quote_body = $body->{'quote'};
  $title = $body->{'title'};

  if (Helpers::isNullOrEmpty($title) || Helpers::isNullOrEmpty($quote_body)) {
    $resp->withStatus(Constants::INVALID_PARAMETERS);
    return $resp->withJson(new Error(false, Constants::INVALID_PARAMETERS, "Invalid data"));
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

  return $resp->withJson($result);
})->add(ValidJson::class)->add(Authenticated::class);

$app->post("/register", function (Request $req, Response $resp, $args = []) {
  
  $body = json_decode($req->getBody());

  if ($body == null) {
    $resp->withStatus(Constants::INVALID_PARAMETERS);
    return $resp->withJson(new Error(false, Constants::INVALID_PARAMETERS, "Bad Request"));
  }

  $username = property_exists($body, 'username') ? $body->{"username"} : null;
  $password = property_exists($body, 'password') ? $body->{"password"} : null;

  if (Helpers::isNullOrEmpty($username) || Helpers::isNullOrEmpty($password)) {
    $resp->withStatus(Constants::INVALID_PARAMETERS);
    return $resp->withJson(new Error(false, Constants::INVALID_PARAMETERS, "Password / Username can't be empty"));
  }

  if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
    $resp->withStatus(Constants::INVALID_PARAMETERS);
    return $resp->withJson(new Error(false, Constants::INVALID_PARAMETERS, "Username must be a valid e-mail"));
  }

  if (strlen($password) < PASS_LENGTH) {
    $resp->withStatus(Constants::INVALID_PARAMETERS);
    return $resp->withJson(new Error(false, Constants::INVALID_PARAMETERS, "Password must be at least " . PASS_LENGTH . " characters long"));
  }

  $password_hash = password_hash($password, PASSWORD_DEFAULT);

  $user = UserQuery::create()->findByUsername($username)->getFirst();

  if ($user != null) {
    $resp->withStatus(Constants::CONFLICT);
    return $resp->withJson(new Error(false, Constants::CONFLICT, "User already exists"));
  }

  $user = new User();
  $user->setUsername($username);
  $user->setPassword($password_hash);
  $user->setApproved(false);
  $user->setAdmin(false);
  $user->setConfirmed(false);
  $rowsAffected = $user->save();

  $confirmation = new Confirmation();
  $confirmation->setUser($user);
  $confirmationCode = md5(uniqid(rand(), true));
  $confirmation->setCode($confirmationCode);
  $confirmation->save();

  QuoteMailer::getInstance()->sendMail($confirmationCode, $username);

  $result = [
    "success" => $rowsAffected > 0,
    "code" => Constants::SUCCESS
  ];

  return $resp->withJson($result);

})->add(ValidJson::class);

$app->get("/confirm/{code}", function (Request $req, Response $resp, $args = []) {
  $confirmationQuery = ConfirmationQuery::create();
  $confirm = $confirmationQuery->findOneByCode($args['code']);

  $result_code = Constants::NOT_FOUND;

  if ($confirm != null) {
    $userQuery = UserQuery::create();
    $user = $userQuery->findOneById($confirm->getUserId());

    if ($user != null) {
      $user->setConfirmed(true);
      $user->save();
      $confirm->delete();
      $result_code = Constants::SUCCESS;
    }
  }

  $result = [
    "code" => $result_code
  ];

  $resp->withJson($result);
});

$app->post("/login", function (Request $req, Response $resp, $args = []) {
  $body = json_decode($req->getBody());

  if ($body == null) {
    $error = new Error(false, Constants::INVALID_PARAMETERS, "Bad request");
    return $resp->withJson($error, $error->getCode());
  }

  $username = property_exists($body, 'username') ? $body->{"username"} : null;
  $password = property_exists($body, 'password') ? $body->{"password"} : null;

  if (Helpers::isNullOrEmpty($username) || Helpers::isNullOrEmpty($password)) {
    $error = new Error(false, Constants::INVALID_PARAMETERS, "Password / Username can't be empty");
    return $resp->withJson($error, $error->getCode());
  }

  $user = UserQuery::create()->findByUsername($username)->getFirst();

  if ($user == null) {
    $error = new Error(false, Constants::INVALID_PARAMETERS, "Invalid username or password");
    return $resp->withJson($error, $error->getCode());
  }

  if (!$user->isApproved()) {
    $error = new Error(false, Constants::INVALID_PARAMETERS, "User has not yet been approved by a system administrator");
    return $resp->withJson($error, $error->getCode());
  }

  if (!password_verify($password, $user->getPassword())) {
    $error = new Error(false, Constants::INVALID_PARAMETERS, "Invalid username or password");
    return $resp->withJson($error, $error->getCode());
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

  return $resp->withJson($result);

})->add(ValidJson::class);

$app->group('/admin', function () {

  $this->get('/users', function (Request $req, Response $resp, $args = []) {

    $users = UserQuery::create()->orderById()->find()->toArray(null, false, TableMap::TYPE_FIELDNAME, true);

    foreach ($users as &$user) {
      unset($user['password']);
    }

    return $resp->withJson($users);
  })->add(Authenticated::class)->add(Authorized::class);

  $this->post('/users', function (Request $req, Response $resp, $args = []) {
    $body = json_decode($req->getBody());

    $user_id = $body->{'id'};
    $approved = boolval($req->getParam('approved'));

    if (!is_int($user_id) || $user_id <= 0 || !is_bool($approved)) {
      $error = new Error(false, Constants::INVALID_PARAMETERS, "Missing or invalid parameters");
      return $resp->withJson($error, $error->getCode());
    }

    $user = UserQuery::create()->findOneById($user_id);

    if ($user == null) {
      $error = new Error(false, Constants::NOT_FOUND, "Invalid user");
      return $resp->withJson($error, $error->getCode());
    }

    $user->setApproved($approved);
    $rowsAffected = $user->save();

    return $resp->withJson([
      'success' => $rowsAffected > 0,
      'code'    => Constants::SUCCESS
    ]);
  })->add(ValidJson::class)->add(Authenticated::class)->add(Authorized::class);;
});

$app->group("/password", function() {

  $this->post('/recovery', function (Request $req, Response $resp, $args = []) {

    $body = json_decode($req->getBody());

    $email = $body->{'username'};

    $user = UserQuery::create()->findByUsername($email)->getFirst();

    $token = array(
      "iat" => time(),
      "nbf" => time(),
      "exp" => time() + 300,
      "id" => $user->getId(),
      "recovery" => true
    );

    $stateManager = StateManager::getInstance();
    $jwt = JWT::encode($token, $stateManager->getSecret());

    return $resp->withJson([
      'success' => true,
      'token' => $jwt,
      'code' => Constants::SUCCESS
    ]);
  })->add(ValidJson::class);

  $this->post('/change', function (Request $req, Response $resp, $args = []) {
    $stateManager = StateManager::getInstance();
    $body = json_decode($req->getBody());

    if (property_exists($body, 'recovery')) {
      if (!property_exists($body, 'token')) {
        $error = new Error(false, Constants::INVALID_PARAMETERS, "Missing recovery token");
        return $resp->withJson($error, $error->getCode());
      }

      $recoveryToken = $body->{'token'};

      if (empty($recoveryToken)) {
        $error = new Error(false, Constants::UNAUTHORIZED, "Not authorized");
        return $resp->withJson($error, $error->getCode());
      }

      $result = Helpers::validateRecoveryToken($recoveryToken, $stateManager->getSecret());

      if ($result instanceof Error || $result == null) {
        $error = new Error(false, Constants::UNAUTHORIZED, "Invalid Token");
        return $resp->withJson($error, $error->getCode());
      }

      $operationResult = Helpers::changePassword($body, $result);
      return $resp->withJson($operationResult, $operationResult->getCode());
    } else {
      $secret = $stateManager->getSecret();
      $token = Helpers::getUserToken($req);
      $result = Helpers::validateToken($token, $secret);
      $operationResult = Helpers::changePassword($body, $result);
      return $resp->withJson($operationResult, $operationResult->getCode());
    }
  })->add(ValidJson::class);

});


$cont = $app->getContainer();
$cont['notFoundHandler'] = function ($c) {
  return function (Request $request, Response $response) use ($c) {
    $error = new Error(false, Constants::NOT_FOUND, "Invalid Path");
    return $c['response']->withJson($error, $error->code);
  };
};

$cont['errorHandler'] = function ($c) use ($development) {
  return function ($request, $response, $exception) use ($c, $development) {
    $error = new Error(false, Constants::SERVER_ERROR, "Internal server error");
    if ($development) {
      return $c['response']->withStatus(Constants::SERVER_ERROR)->write($exception);
    }
    return $c['response']->withJson($error, $error->getCode());
  };
};

$app->add(function (Request $request, Response $response, $next) {
  $response->withHeader("Content-Type", "application/json");
  return $next($request, $response);
});

if ($development) {

  $app->add(function (Request $request, Response $response, $next) {
    $response->withHeader("Access-Control-Allow-Origin", '*');
    $response->withHeader("Access-Control-Allow-Methods", "PUT, GET, POST, DELETE, OPTIONS");
    $response->withHeader("Access-Control-Allow-Headers", "Origin, Authorization, Content-Type, X-Requested-With");

    return $next($request, $response);
  });
}

if ($development) {
  $app->options("/(:name+)", function (Request $request, Response $response, $next) {
    $response->withHeader("Access-Control-Allow-Origin", '*');
    $response->withHeader("Access-Control-Allow-Methods", "PUT, GET, POST, DELETE, OPTIONS");
    $response->withHeader("Access-Control-Allow-Headers", "Origin, Authorization, Content-Type, X-Requested-With");
    return $next($request, $response);
  });
}

$app->run();
