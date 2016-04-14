<?php

namespace helpers;


use GearmanClient;
use PHPMailer;
use QuoteEnd\StateManager;

class QuoteMailer
{
  private static $instance;
  private $mail;

  /**
   * Protected constructor to prevent creating a new instance of the
   * *Singleton* via the `new` operator from outside of this class.
   */
  protected function __construct()
  {
    $mail = new PHPMailer();
    $state = StateManager::getInstance();
    $this->mail = $mail;
    $mail->isSMTP();
    $mail->Host = $state->getSmtpHostname();
    $mail->Port = $state->getSmtpPort();
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = 'ssl';
    $mail->SMTPAutoTLS = true;
    $mail->SMTPDebug = 3;
    $mail->Username = $state->getSmtpUsername();
    $mail->Password = $state->getSmtpPassword();
  }

  /**
   * Gives access to the single instance of {@link QuoteMailer}
   * @return QuoteMailer
   */
  public static function getInstance()
  {
    if (null == static::$instance) {
      static::$instance = new static();
    }

    return static::$instance;
  }


  /**
   * Sends a e-mail to the specified address containing a link with the specified token
   * @param $address
   * @param $url
   * @param $sender
   * @return bool
   * @throws \phpmailerException
   */
  public function sendConfirmationMail($address, $url, $sender) {
    $this->mail->addAddress($address);
    $this->mail->isHTML(true);
    $body = file_get_contents("../app/templates/confirm_email.html");
    $body = str_replace('%username%', $address, $body);
    $body = str_replace('%confirmation_link%', $url, $body);
    $this->mail->msgHTML($body);
    $this->mail->setFrom($sender, "Quote");
    $this->mail->Subject = "Confirm Address On Quote";
    $this->mail->AltBody =strip_tags($body);
    return $this->mail->send();
  }

  /**
   * @param $mail String The recipient of the e-mail message (the address of the service admin)
   * @param $username String The username of the new user logged
   * @param $sender String The service e-mail address
   * @return bool
   * @throws \phpmailerException
   */
  public function sendAdminMail($mail, $username, $sender) {
    $this->mail->addAddress($mail);
    $this->mail->isHTML(true);
    $body = file_get_contents("../app/templates/admin.html");
    $body = str_replace('%login_username%', $username, $body);
    $this->mail->msgHTML($body);
    $this->mail->setFrom($sender, "Quote");
    $this->mail->Subject = "New quote user";
    $this->mail->AltBody =strip_tags($body);

    return $this->mail->send();
  }


  public function sendConfirmation($address, $url, $sender) {
    $client = new GearmanClient();
    $client->addServer();

    $arguments = [
      'address' => $address,
      'url' => $url,
      'sender' => $sender
    ];

    $client->addTaskBackground("sendConfirmation", json_encode($arguments));
    $client->runTasks();
  }

  public function notifyAdmin($mail, $username, $sender) {
    $client = new GearmanClient();
    $client->addServer();

    $arguments = [
      'mail' => $mail,
      'username' => $username,
      'sender' => $sender
    ];

    $client->addTaskBackground("notifyAdmin", json_encode($arguments));
    $client->runTasks();
  }

  /**
   * @param $confirmationCode
   * @param $username
   */
  function sendMail($confirmationCode, $username)
  {
    $serviceProtocol = $_SERVER['SERVER_PORT'] == 443 ? "https://" : "http://";
    $serviceUrl = $serviceProtocol . $_SERVER['SERVER_NAME'] . "/confirm/" . $confirmationCode;

    $adminMail = StateManager::getInstance()->getAdminMail();
    $serviceMail = StateManager::getInstance()->getMail();
    
    $this->sendConfirmation($username, $serviceUrl, $serviceMail);
    $this->notifyAdmin($adminMail, $username, $serviceMail);
  }


  /**
   * Private clone method to prevent cloning of the instance of the
   * *Singleton* instance.
   *
   * @return void
   */
  private function __clone()
  {
  }

  /**
   * Private unserialize method to prevent unserializing of the *Singleton*
   * instance.
   *
   * @return void
   */
  private function __wakeup()
  {
  }


}