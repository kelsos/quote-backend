<?php
namespace QuoteEnd;


class Error
{
  public $success = false;
  public $status = 404;
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
  public function getStatus()
  {
    return $this->status;
  }

  /**
   * @param int $status
   */
  public function setStatus($status)
  {
    $this->status = $status;
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