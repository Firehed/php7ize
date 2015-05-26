<?php

namespace Firehed\PHP7ize;

class Function_ implements StringlikeInterface {

  private static $no_return_type_methods = [
    '__construct',
    '__destruct',
    '__clone',
  ];

  private $depth = 0;
  private $docblock;
  private $has_started = false;
  private $name = '';
  private $no_op = false;
  private $track = true;

private $body = [];
private $head = [];

  private $return_type = null;
  private $param_types = [];

  public function addToken(Token $token) {
    switch ($token->getType()) {
      // This block is basically a whitelist of token types permissible before
      // seeing a T_FUNCTION. If we hit anything else, the docblock isn't
      // attached to something we care about.
    case T_DOC_COMMENT:
      $this->docblock = $token;
      // fall through
    case T_WHITESPACE:
    case T_PUBLIC: case T_PROTECTED: case T_PRIVATE:
    case T_STATIC:
    case T_ABSTRACT:
      break;
    case Token::SINGLE_CHARACTER:
      if ($token->is('{')) {
        $this->has_started = true;
        $this->depth++;
      }
      elseif ($token->is('}')) {
        $this->depth--;
      }
      break;
    case T_FUNCTION:
      $this->track = false;
      break;
    case T_STRING:
      if (!$this->has_started) {
        $this->name = $token->getValue();
      }
      // fall through
    default:
      if ($this->track) {
        $this->no_op = true;
      }
      break;
    }
    if ($this->has_started) {
      $this->body[] = $token;
    } else {
      $this->head[] = $token;
    }
    return $this;
  }

  public function isComplete() {
    return $this->has_started && !$this->depth ;
  }

  private function parseDocblock() {
    $docblock = (string)$this->docblock;
    preg_match_all('#@return\s*([\\\\\w]+)#', $docblock, $return_annotations);
    $matches = $return_annotations[1];
    switch (count($matches)) {
    case 0:
      // No return type annotation
      break;
    case 1:
      $this->return_type = $matches[0];
      break;
    default:
      // Ambiguous, should raise a warning
      break;
    }

    // Escaping hell, the actual group is ([\\\w]+), meaning A-Za-z\
    preg_match_all('#@param\s*([\\\\\w]+)#', $docblock, $param_annotations);
    $this->param_types = $param_annotations[1];
  }


  private function renderHead() {
    if ($this->no_op) {
      return implode('', $this->head);
    }
    $this->parseDocblock();

    $buffer = '';
    $arg = null;
    $argc = 0;


    $arglist = new ArgumentList($this->param_types);
    $in_args = false;
    $buf = '';
    foreach ($this->head as $token) {
      if ($token->is('(')) {
        $in_args = true;
        $buf .= (string)$token;
        continue;
      }
      elseif ($token->is(')')) {
        $buf .= (string)$arglist;
        $in_args = false;
        $buf .= $token;
        $buf .= $this->buildReturnType();
        continue;
      }

      if ($in_args) {
        $arglist->addToken($token);
      }
      else {
        $buf .= (string)$token;
      }
    }
    //var_dump($buf);
    return $buf;


  }
  private function renderBody() {
    return implode('', $this->body);
  }

  /**
   * @return string
   */
  private function buildReturnType() {
    if (!$this->return_type) {
      return '';
    }
    if (in_array(strtolower($this->name), self::$no_return_type_methods)) {
      return '';
    }
    if ($type = TypeFixer::fixType($this->return_type)) {
      return ': '.$type;
    }
    return '';
  }


  public function __toString() {
    return $this->renderHead().$this->renderBody();
  }

}
