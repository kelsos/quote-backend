<?php

require_once '../vendor/autoload.php';
require_once '../generated-conf/config.php';

use Quote\QuoteQuery;
use Error\Error;
use Slim\Slim;

$app = new Slim();


$app->get('/quote/:id', function ($id)  use($app) {
  $quoteQuery = new QuoteQuery();
  $quote = $quoteQuery->findPk($id);

  if ($quote == null) {
    $app->response->setStatus(404);
    $error = new Error();
    echo json_encode($error);
    exit;
  }

  echo $quote->toJSON();
});

$app->get('/quote', function () {
  $quotes = QuoteQuery::create()->orderById()->find();
  echo $quotes->toJSON();
});

$app->post('/quote', function() {

});

$app->response->header("Content-Type", "application/json");
$app->run();
