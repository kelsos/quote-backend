<?php

namespace QuoteEnd;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;
use helpers\BaseResponse;
use Quote\UserQuery;
use Slim\Http\Request;
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
   * @return null|object Returns the decoded array included in the token or null.
   *
   */
  static function validateRecoveryToken($token, $secret)
  {
    $token = Helpers::validateToken($token, $secret);
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
   *
   * @return null|Error Returns the decoded array included in the token or an error.
   */
  static function validateToken($token, $secret)
  {
    $error = null;
    if ($token == null) {
      $error = new Error(false, Constants::UNAUTHORIZED, "User is not authenticated to use the api");
    } else {
      try {
        $decode = JWT::decode($token, $secret, array('HS256'));
        //Check if it is a recovery token and throw 403
        return $decode;
      } catch (SignatureInvalidException $ex) {
        $error = new Error(false, Constants::UNAUTHORIZED, "Invalid token");
      } catch (ExpiredException $ex) {
        $error = new Error(false, Constants::UNAUTHORIZED, "Expired token");
      } catch (UnexpectedValueException $ex) {
        $error = new Error(false, Constants::UNAUTHORIZED, "Something wrong will trying to decode the token");
      }
    }

    return $error;
  }

  /**
   * Checks the header for the token
   *
   * @param Request $request the incoming request
   * @return String|null
   */
  static function getUserToken(Request $request)
  {
    $token = "";
    $authorization_header = $request->getHeaderLine("Authorization");
    if (strpos($authorization_header, "Bearer") !== false) {
      $token = explode(" ", $authorization_header)[1];
    }

    return $token;
  }

  /**
   * Changes the user's password
   *
   * @param object $body The decoded request body.
   * @param object $validatedToken A token that has passed the validation check
   * @return BaseResponse|Error
   * @throws \Propel\Runtime\Exception\PropelException
   */
  static function changePassword($body, $validatedToken)
  {
    if (!property_exists($body, 'username') || !property_exists($body, 'password')) {
      return new Error(false,Constants::INVALID_PARAMETERS , "Missing parameters");
    }

    $password = $body->{'password'};
    $user = UserQuery::create()->findOneById($validatedToken->{'id'});
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $user->setPassword($password_hash);
    $rowsAffected = $user->save();

    return new BaseResponse($rowsAffected > 0, Constants::SUCCESS);
  }
}