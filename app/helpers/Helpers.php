<?php

namespace QuoteEnd;


use ExpiredException;
use JWT;
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
   * Creates an error and halts the Slim application returning the application JSON.
   *
   * @param int $code The error code (status)
   * @param String $description The error description
   * @param Slim $app Reference to the slim application
   */
  static function error($code, $description, $app)
  {
    $error = new Error();
    $error->setStatus($code);
    $error->setDescription($description);
    $app->halt($error->getStatus(), json_encode($error));
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
      Helpers::error(400, "Missing token", $app);
    }
    try {
      $decode = JWT::decode($token, $secret, array('HS256'));
      return $decode;
    } catch (SignatureInvalidException $ex) {
      Helpers::error(403, "Invalid token", $app);
    } catch (ExpiredException $ex) {
      Helpers::error(403, "Expired token", $app);
    } catch (UnexpectedValueException $ex) {
      Helpers::error(400, "Something wrong will trying to decode the token", $app);
    }
    return null;
  }

  /**
   * Tries to extract the user token for any available source,
   * Post param, Query string, or Json body.
   * @param Slim $app
   * @return String|null
   */
  static function getUserToken($app)
  {
    $request = $app->request();
    $token = $request->post("token") == null
      ? $app->request->get("token")
      : $app->request->post("token");

    if ($token == null && strcmp($request->getContentType(), 'application/json') == 0) {
      $body = json_decode($request->getBody());
      $token = $body->{'token'};
    }
    return $token;
  }
}