<?php

require_once '../vendor/autoload.php';

use helpers\QuoteMailer;

$worker = new GearmanWorker();
$worker->addServer();
$worker->addFunction("sendConfirmation", "send_mail");
$worker->addFunction("notifyAdmin", "notify");

while ($worker->work());

function send_mail($job) {
  echo "sending mail\n". $job->workload()."\n";
  $args = json_decode($job->workload());

  $address = $args->address;
  $url = $args->url;
  $sender = $args->sender;
  echo  "$address $url $sender";
  QuoteMailer::getInstance()->sendConfirmationMail($address, $url, $sender);
}

function notify($job) {
  echo "notifying\n". $job->workload()."\n";
  $args = json_decode($job->workload());
  QuoteMailer::getInstance()->sendAdminMail($args->mail, $args->username, $args->sender);
}
