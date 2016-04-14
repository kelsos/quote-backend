<?php

require_once '../vendor/autoload.php';

use helpers\QuoteMailer;

$worker = new GearmanWorker();
$worker->addServer();
$worker->addFunction("sendConfirmation", "send_mail");
$worker->addFunction("notifyAdmin", "notify");
$worker->addFunction("sendPasswordReset", "send_password_reset_mail");

while ($worker->work());

function send_mail(GearmanJob $job) {
  echo "sending mail\n". $job->workload()."\n";
  $args = json_decode($job->workload());

  $address = $args->address;
  $url = $args->url;
  $sender = $args->sender;
  echo  "$address $url $sender";
  QuoteMailer::getInstance()->sendConfirmationMail($address, $url, $sender);
}

function notify(GearmanJob $job) {
  echo "notifying\n". $job->workload()."\n";
  $args = json_decode($job->workload());
  QuoteMailer::getInstance()->sendAdminMail($args->mail, $args->username, $args->sender);
}

function send_password_reset_mail(GearmanJob $job) {
  $args = json_decode($job->workload());
  $serviceMail = $args->sender;
  $username = $args->username;
  $temporaryUrl = $args->temporary_url;
  echo $username;
  QuoteMailer::getInstance()->sendPasswordMail($serviceMail, $username, $temporaryUrl);
}