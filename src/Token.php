<?php

namespace Firehed\PHP7ize;

class Token implements StringlikeInterface {

  const SINGLE_CHARACTER = 0;

  private $type = self::SINGLE_CHARACTER;
  private $value = '';
  private $name = '';

  /**
   * @param mixed token from token_get_all
   * @return this
   */
  public function __construct($token) {
    if (is_array($token)) {
      $this->parseToken($token);
    }
    else {
      $this->value = $token;
//      $this->name = 'F_NONSTANDARD_TOKEN';
    }
  }

  /**
   * @param array token from token_get_all
   * @return void
   */
  private function parseToken(array $token) {
    list($this->type, $this->value) = $token;
    $this->name = token_name($this->type);
  }

  /**
   * @return int
   */
  public function getType() {
    return $this->type;
  }

  /**
   * @return string
   */
  public function getValue() {
    return $this->value;
  }

  /**
   * @return string
   */
  public function getName() {
    return token_name($this->type);
    return $this->name;
  }

  /**
   * @param string Value to check against
   * @return bool
   */
  public function is($str) {
    return $str === $this->value;
  }

  /**
   * @return string
   */
  public function __toString() {
    return $this->getValue();
  }

}
