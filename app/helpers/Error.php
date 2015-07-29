<?php
namespace QuoteEnd;


class Error
{
  public $success = false;
  public $code = 404;
  public $description = "Not found";

  /**
   * @return boolean
   */
  public function isSuccess()
  {
    return $this->success;
  }

  /**
   * @param boolean $success
   */
  public function setSuccess($success)
  {
    $this->success = $success;
  }

  /**
   * @return int
   */
  public function getCode()
  {
    return $this->code;
  }

  /**
   * @param int $code
   */
  public function setCode($code)
  {
    $this->code = $code;
  }

  /**
   * @return string
   */
  public function getDescription()
  {
    return $this->description;
  }

  /**
   * @param string $description
   */
  public function setDescription($description)
  {
    $this->description = $description;
  }
}