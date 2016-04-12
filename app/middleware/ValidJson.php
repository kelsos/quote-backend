<?php

namespace middleware;


use QuoteEnd\Constants;
use QuoteEnd\Error;
use Slim\Http\Request;
use Slim\Http\Response;

class ValidJson
{
  public function __invoke(Request $request, Response $response, $next)
  {
    if (strpos($request->getContentType(), 'application/json') === false) {
      $response->withStatus(Constants::INVALID_PARAMETERS);
      $response->withJson(new Error(false, Constants::INVALID_PARAMETERS, "Invalid request"));
    } else {
      $response = $next($request, $response);
    }

    return $response;
  }
}