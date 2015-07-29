<?php

namespace QuoteEnd;


use ExpiredException;
use JWT;
use Quote\UserQuery;
use SignatureInvalidException;
use Slim\Slim;
use Symfony\Component\Console\Helper\Helper;
use UnexpectedValueException;

class Helpers
{

  /**
   * Checks if a passed variable is null or empty
   * @param $question
   * @return bool
   */
  static function isNullOrEmpty($question)
  {
    return (!isset($question) || trim($question) === '');
  }

  /**
   * Used before calls that require authentication to make sure that the
   * user performing the request has a valid non expired token. In addition
   * the recovery token must have a recovery property set to true.
   *
   * @param String $token The token used by the user for the request
   * @param String $secret The application secret used to verify the token.
   * @param Slim $app A reference to the Slim application.
   *
   * @return null|object Returns the decoded array included in the token or null.
   */
  static function validateRecoveryToken($token, $secret, $app)
  {
    $token = Helpers::validateToken($token, $secret, $app);
    if (!property_exists($token, 'recovery')) {
      $token = null;
    }
    return $token;
  }

  /**
   * Used before calls that require authentication to make sure that the
   * user performing the request has a valid non expired token
   *
   * @param String $token The token used by the user for the request
   * @param String $secret The application secret used to verify the token.
   * @param Slim $app A reference to the Slim application.
   *
   * @return null|object Returns the decoded array included in the token or null.
   */
  static function validateToken($token, $secret, $app)
  {
    if ($token == null) {
      Helpers::error(Constants::INVALID_PARAMETERS, "Missing token", $app);
    }
    try {
      $decode = JWT::decode($token, $secret, array('HS256'));
      //Check if it is a recovery token and throw 403
      return $decode;
    } catch (SignatureInvalidException $ex) {
      Helpers::error(Constants::UNAUTHORIZED, "Invalid token", $app);
    } catch (ExpiredException $ex) {
      Helpers::error(Constants::UNAUTHORIZED, "Expired token", $app);
    } catch (UnexpectedValueException $ex) {
      Helpers::error(Constants::UNAUTHORIZED, "Something wrong will trying to decode the token", $app);
    }
    return null;
  }

  /**
   * Creates an error and halts the Slim application returning the application JSON.
   *
   * @param int $code The error code (status)
   * @param String $description The error description
   * @param Slim $app Reference to the slim application
   */
  static function error($code, $description, $app)
  {
    $error = new Error();
    $error->setCode($code);
    $error->setDescription($description);
    $app->halt($error->getCode(), json_encode($error));
  }

  /**
   * Checks the header for the token
   *
   * Post param, Query string, or Json body.
   * @param Slim $app
   * @return String|null
   */
  static function getUserToken($app)
  {
    $request = $app->request();
    $token = "";

    $authorization_header = $request->headers("Authorization");
    if (strpos($authorization_header, "Bearer") !== false) {
      $token = explode(" ", $authorization_header)[1];
    }

    return $token;
  }

  /**
   * Changes the user's password
   *
   * @param object $body The decoded request body.
   * @param Slim $app A reference to the slim application
   * @param object $validatedToken A token that has passed the validation check
   * @throws \Propel\Runtime\Exception\PropelException
   */
  static function changePassword($body, $app, $validatedToken)
  {
    if (!property_exists($body, 'username') || !property_exists($body, 'password')) {
      Helpers::error(Constants::INVALID_PARAMETERS, "Missing parameters", $app);
    }

    $password = $body->{'password'};

    $user = UserQuery::create()->findOneById($validatedToken->{'id'});

    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $user->setPassword($password_hash);

    $rowsAffected = $user->save();

    $app->response()->setBody(json_encode([
        'success' => $rowsAffected > 0,
        'code' => Constants::SUCCESS
    ]));
  }

  /**
   * Checks if the content type of the incoming request is 'application/json' and if not
   * returns a 400 code.
   *
   * @param Slim $app
   */
  static function checkForJsonRequest($app)
  {
    $request = $app->request();
    if (strpos($request->getContentType(), 'application/json') === false) {
      Helpers::error(Constants::INVALID_PARAMETERS, "Invalid request" , $app);
    }
  }
}