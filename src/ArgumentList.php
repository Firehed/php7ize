<?php

namespace Firehed\PHP7ize;

class ArgumentList {

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
    if ($type = TypeFixer::fixType($provided_hint)) {
      $this->tokens[] = $type.' ';
    }
  }

  public function __toString() {
    return implode('', $this->tokens);
  }

}
