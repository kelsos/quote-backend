<?php

require_once '../vendor/autoload.php';
require_once '../generated-conf/config.php';

use Quote\Quote;
use Quote\QuoteQuery;
use QuoteEnd\Error;
use QuoteEnd\Helpers;
use Slim\Slim;

$app = new Slim();


$app->get('/quote/:id', function ($id)  use ($app) {
  $quoteQuery = new QuoteQuery();
  $quote = $quoteQuery->findPk($id);

  if ($quote == null) {
    $app->response->setStatus(404);
    $error = new Error();
    $app->halt($error->getStatus(), json_encode($error));
  }

  $app->response()->setBody($quote->toJSON());
});

$app->get('/quote', function () use ($app) {
  $quotes = QuoteQuery::create()->orderById()->find();
  $app->response()->setBody($quotes->toJSON());
});

$app->post('/quote', function() use ($app) {

  $request = $app->request;
  $title = $request->post("title");
  $quote_body = $request->post("quote");

  if (Helpers::isNullOrEmpty($title) || Helpers::isNullOrEmpty($quote_body)) {
    $error = new Error();
    $error->setStatus(400);
    $error->setDescription("Invalid data");
    $app->halt($error->getStatus(), json_encode($error));
  }

  date_default_timezone_set("UTC");
  $published = date("Y-m-d H:i:s", time());

  $quote = new Quote();
  $quote->setTitle($title);
  $quote->setQuote($quote_body);
  $quote->setPublished($published);
  $rowAffected = $quote->save();

  $result = [
    "success" => $rowAffected > 0
  ];

  $app->response()->setBody(json_encode($result));

});

$app->response->header("Content-Type", "application/json");
$app->run();