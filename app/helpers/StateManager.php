<?php

namespace QuoteEnd;


use Symfony\Component\Yaml\Yaml;

class StateManager
{

  private static $instance;
  private $secret;
  private $mail;
  private $environment;
  private $tokenData;
  private $smtpUsername;
  private $smtpPassword;
  private $smtpHostname;
  private $smtpPort;
  private $domain;

  /**
   * Protected constructor to prevent creating a new instance of the
   * *Singleton* via the `new` operator from outside of this class.
   */
  protected function __construct()
  {
    $config = Yaml::parse(file_get_contents("../config.yaml"));
    $this->secret = $config['secret'];
    $this->mail = $config['mail'];
    $this->environment = $config['environment'];
    $this->smtpHostname = $config['smtp_server'];
    $this->smtpPort = $config['smtp_port'];
    $this->smtpUsername = $config['smtp_username'];
    $this->smtpPassword = $config['smtp_password'];
    $this->domain = $config['domain'];

  }

  /**
   * Gives access to the single instance of {@link StateManager}
   * @return StateManager
   */
  public static function getInstance()
  {
    if (null == static::$instance) {
      static::$instance = new static();
    }

    return static::$instance;
  }

  /**
   * @return mixed
   */
  public function getTokenData()
  {
    return $this->tokenData;
  }

  /**
   * Sets the validated token data
   * @param mixed $tokenData
   */
  public function setTokenData($tokenData)
  {
    $this->tokenData = $tokenData;
  }

  /**
   * @return String
   */
  public function getSecret()
  {
    return $this->secret;
  }

  /**
   * @return String
   */
  public function getMail()
  {
    return $this->mail;
  }

  /**
   * @return String
   */
  public function getEnvironment()
  {
    return $this->environment;
  }

  /**
   * The method will return true when the configuration points on development
   * @return bool
   */
  public function isDevelopment()
  {
    return strcmp($this->environment, 'development') !== false;
  }

  /**
   * Returns the username of the user on the smtp server used to send e-mails
   * @return String
   */
  public function getSmtpUsername()
  {
    return $this->smtpUsername;
  }

  /**
   * @return String
   */
  public function getSmtpPassword()
  {
    return $this->smtpPassword;
  }

  /**
   * @return String
   */
  public function getSmtpHostname()
  {
    return $this->smtpHostname;
  }

  /**
   * @return int
   */
  public function getSmtpPort()
  {
    return $this->smtpPort;
  }

  public function getDomain() {
    return $this->domain;
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