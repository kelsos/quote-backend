<?php

namespace QuoteEnd;


class Helpers
{

  /**
   * Checks if a passed variable is null or empty
   * @param $question
   * @return bool
   */
  static function isNullOrEmpty($question){
    return (!isset($question) || trim($question)==='');
  }
}