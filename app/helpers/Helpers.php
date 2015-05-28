<?php

namespace QuoteEnd;


use JWT;
use SignatureInvalidException;
use Slim\Slim;

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

  static function validateToken($token, $secret, $app)
  {
    try {
      $decoded = JWT::decode($token, $secret, array('HS256'));
      $expired = $decoded["exp"];

      if ($expired < time()) {
        Helpers::error(403, "Token expired", $app);
      }
    } catch (SignatureInvalidException $ex) {
      Helpers::error(403, "Invalid token", $app);
    }
  }
}