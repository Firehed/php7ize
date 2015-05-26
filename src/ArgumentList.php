<?php

namespace Firehed\PHP7ize;

class ArgumentList {

  /**
   * A list of reserved keywords that should never be allowed as typehints for
   * parameters or return values.
   *
   * Based off of this:
   * http://php.net/manual/en/reserved.other-reserved-words.php
   *
   */
  private static $blacklisted_typehints = [
    // Reserved keywords not implemented in STH
    'mixed',
    'resource',
    'numeric',
    'object',
    // Non-reserved, but has a chance of becoming so. Preventative measure.
    // This will break compatibility if there's a legit TH to a Scalar class
    'scalar',
    'null', // Meaningless
    'false',
    'true',
  ];

  /**
   * A list of aliases commonly seen in type hints
   */
  private static $coercions = [
    'integer' => 'int',
    'double' => 'float',
    'boolean' => 'bool',
    'this' => 'self',
  ];


  private $tokens = [];
  private $provided_hints = [];
  private $args = [];

  private $argno = 0;
  private $current_th = '';

  public function __construct(array $hints) {
    $this->provided_hints = $hints;
  }

  public function addToken(Token $token) {
    switch ($token->getType()) {
    case T_WHITESPACE:
      break;
    case T_VARIABLE:
      if ($this->current_th) {
        //var_dump($this->current_th);
      }
      else {
        $this->addProvidedTypehint();
      }
      break;
    default:
      $this->current_th .= $token;
      break;
    }
    //echo "Arg $this->argno token is $token\n\n";
    $this->tokens[] = $token;
    if ($token->is(',')) {
      $this->argno++;
      $this->current_th = '';
    }
  }

  private function addProvidedTypehint() {
    if (!isset($this->provided_hints[$this->argno])) {
      return;
    }
    $provided_hint = $this->provided_hints[$this->argno];
    if (isset(self::$coercions[$provided_hint])) {
      $provided_hint = self::$coercions[$provided_hint];
    }
    if (!in_array($provided_hint, self::$blacklisted_typehints)) {
      $this->tokens[] = $provided_hint.' ';
    }
  }

  public function __toString() {
    return implode('', $this->tokens);
  }

}
