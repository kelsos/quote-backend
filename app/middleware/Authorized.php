<?php

namespace middleware;

use Quote\UserQuery;
use QuoteEnd\Constants;
use QuoteEnd\Error;
use QuoteEnd\StateManager;
use Slim\Http\Request;
use Slim\Http\Response;

class Authorized
{
  public function __invoke(Request $request, Response $response, $next)
  {
    $user_id = StateManager::getInstance()->getTokenData()->{'id'};
    $active_user = UserQuery::create()->findOneById($user_id);

    if (!$active_user->isAdmin()) {
      $response->withStatus(Constants::UNAUTHORIZED);
      $response->withJson(new Error(false, Constants::UNAUTHORIZED, "You have not administrative access."));
    } else {
      $response = $next($request, $response);
    }
    
    return $response;
  }
}