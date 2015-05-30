<?php

namespace QuoteEnd;


use ExpiredException;
use JWT;
use SignatureInvalidException;
use Slim\Slim;
use Symfony\Component\Console\Helper\Helper;

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
   */
  static function validateToken($token, $secret, $app)
  {
    try {
      JWT::decode($token, $secret, array('HS256'));
    } catch (SignatureInvalidException $ex) {
      Helpers::error(403, "Invalid token", $app);
    } catch (ExpiredException $ex) {
      Helpers::error(403, "Expired token", $app);
    }
  }
}