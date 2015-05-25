<?php

namespace Firehed\PHP7ize;

class Converter {

  private $source_file;
  private $output_file;
  private $should_echo;
  private $is_quiet = false;

  public function setIsQuiet($is_quiet) {
    $this->is_quiet = $is_quiet;
    return $this;
  }

  public function setOutputFile($output_file) {
    $this->output_file = $output_file;
    return $this;
  }

  public function setEcho($should_echo) {
    $this->should_echo = $should_echo;
    return $this;
  }

  public function setSource($source_file) {
    $this->source_file = $source_file;
    return $this;
  }

  public function convert() {
    $tokens = token_get_all(file_get_contents($this->source_file));

    foreach ($tokens as $raw_token) {
      $token = new Token($raw_token);
      if ($this->near_function) {
        if ($token->is('(')) {
          $this->add($token);
          $this->startParamCapture();
        }
        elseif ($this->capture_function_params) {
          $this->function_params[] = $token;
          if ($token->is(')')) {
            $this->endFunctionMode();
          }
        }
        else {
          // This is the actual function name, or whitespace near it
          $this->add($token);
        }
      }
      else {
        $this->handleToken($token);
      }
    } // Token loop

    // render and output
    echo ($this->output);
  }

  private $capture_function_params = false;
  private function startParamCapture() {
    $this->capture_function_params = true;
  }

  private function endFunctionMode() {
    $this->addFunctionParams();
    $this->addReturnAnnotation();
    $this->near_function = false;
    $this->capture_function_params= false;
    // Done, clean up for the next function
    $this->current_return_type = '';
    $this->current_param_types = [];
    $this->function_params = [];

  }

  private function addFunctionParams() {
    $param_parts = [];
    $param_no = 0;
    foreach ($this->function_params as $tok) {
      // Loop over the tokens, break into params
      if ($tok->is(',') || $tok->is(')')) {
        // process param
        $this->mungeParam($param_parts, $param_no);
        $this->add($tok);
        $param_parts = [];
        $param_no++;
      } else {
        $param_parts[] = $tok;
      }
    }
  }

  private function mungeParam($parts, $number) {
    $seen_var = false;
    $has_annotation = false;
    if (isset($this->current_param_types[$number])) {
      $typehint = $this->current_param_types[$number];
    }
    else {
      $this->warn("No typehint in annotation");
      array_map(function($part) { $this->add($part); }, $parts);
      return;
    }

    foreach ($parts as $part) {
      if ($seen_var) {
        $this->add($part);
      }
      elseif ($part->getType() === T_VARIABLE) {
        if (!$has_annotation) {
          $this->addDocblockAnnotation($typehint);
        }
        $seen_var = true;
        $this->add($part);
      }
      elseif ($part->getType() === T_WHITESPACE) {
        $this->add($part);
      }
      else {
        $has_annotation = true;
        $this->add($part);
        if ($part->getValue() !== $typehint) {
          // Issue warning
          $this->warn(
            "Docblock type '%s' does not match function signature type '%s'",
            $typehint,
            $part
          );
        }
      }
    }
  }

  private $function_params = [];
  private function handleToken(Token $token) {
    switch ($token->getType()) {
    case T_DOC_COMMENT:
      $this->parseDocblock($token);
      break;
    case T_FUNCTION:
      $this->handleFunction($token);
      break;
    case T_WHITESPACE: // fall through
    default:
      $this->add($token);
    }
  } // handleToken

  private function addReturnAnnotation() {
    if (!$this->current_return_type) {
      return;
    }
    $return_annotation = sprintf(': %s', $this->current_return_type);
    $tok = new Token($return_annotation);
    $this->add($tok);
  }

  private $near_function = false;
  private function handleFunction($funstr) {
    $this->near_function = true;
    $this->add($funstr);
  }

  private $current_return_type = '';
  private $current_param_types = [];
  private function parseDocblock(Token $token) {
    $docblock = (string)$token;

    preg_match('#@return\s*([\\\\\w]+)#', $docblock, $return_annotation);
    $this->current_return_type = $return_annotation ? $return_annotation[1] : '';

    // Escaping hell, the actual group is ([\\\w]+), meaning A-Za-z\
    preg_match_all('#@param\s*([\\\\\w]+)#', $docblock, $param_annotations);
    $this->current_param_types = $param_annotations[1];

    $this->add($token);
  }

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
  private static $coercions = [
    'integer' => 'int',
    'double' => 'float',
    'boolean' => 'bool',
    'this' => 'self',
  ];
  private function addDocblockAnnotation($annotation_str) {
    // We're going to make a rather stupid assumption where if there's
    // a capital letter, the script wanted a class of this name. Naming a class
    // as such is a bad idea, but we're going to assume that's what you wanted.
    if (in_array($annotation_str, self::$blacklisted_typehints)) {
      $this->warn("Skipping blacklisted annotation '%s'", $annotation_str);
      return;
    }
    if (isset(self::$coercions[$annotation_str])) {
      $annotation_str = self::$coercions[$annotation_str];
    }
    $tok = new Token(sprintf('%s ', $annotation_str));
    $this->add($tok);
  }

  private $output;
  /**
   * @param Token thing to putput
   * @return this
   */
  private function add(Token $tok) {
    $this->output .= (string)$tok;
    return $this;
  }

  /**
   * @param string sprintf-style format string
   * @param mixed variables to be substitued in placeholders
   *
   * @return void
   */
  private function warn($msg, ...$vars) {
    if ($this->is_quiet) {
      return;
    }
    $yellow = "\033[0;33m";
    $black = "\033[0m";
    $format_str = sprintf("%s%s%s%s\n",
      $yellow,
      "WARNING: ",
      $black,
      $msg);
    fwrite(STDERR, vsprintf($format_str, $vars));
  }

}

