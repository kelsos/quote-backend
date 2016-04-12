<?php

namespace middleware;

use QuoteEnd\Error;
use QuoteEnd\Helpers;
use QuoteEnd\StateManager;
use Slim\Http\Request;
use Slim\Http\Response;

class Authenticated
{
  public function __invoke(Request $request, Response $response, $next)
  {
    $token = Helpers::getUserToken($request);
    $secret = StateManager::getInstance()->getSecret();
    $result = Helpers::validateToken($token, $secret);

    if ($result instanceof Error) {
      $response->withStatus($result->getCode());
      $response->withJson($result);
    } else {
      StateManager::getInstance()->setTokenData($result);
      $response = $next($request, $response);
    }

    return $response;
  }
}