<?php
/**
 * Created by PhpStorm.
 * User: kelsos
 * Date: 4/13/16
 * Time: 12:53 AM
 */

namespace helpers;


class BaseResponse
{
  private $success = false;
  private $code = 0;
  private $description = "";

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