<?php

namespace QuoteEnd;


use Symfony\Component\Yaml\Yaml;

class StateManager
{

  private $secret;
  private $mail;
  private $environment;
  private $tokenData;

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
  public function isDevelopment() {
    return strcmp($this->environment, 'development') !== false;
  }

  private static $instance;

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
   * Protected constructor to prevent creating a new instance of the
   * *Singleton* via the `new` operator from outside of this class.
   */
  protected function __construct()
  {
    $config = Yaml::parse(file_get_contents("../config.yaml"));
    $secret = $config['secret'];
    $mail = $config['mail'];
    $development = strcmp($config['environment'], 'development') !== false;
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