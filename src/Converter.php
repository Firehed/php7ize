<?php

namespace Firehed\PHP7ize;

class Converter {

  // Suppress warnings and errors?
  private $is_quiet = false;
  // Output buffer
  private $output;
  // Destination file
  private $output_file;
  // Render to STDOUT?
  private $should_echo;

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

    $fn_block = null;
    foreach ($tokens as $raw_token) {
      $token = new Token($raw_token);

      // Effectively, capture everything from the start of a docblock until
      // a closing bracket. Bracket depth is managed by the function capture
      // object.
      if ($token->getType() === T_DOC_COMMENT) {
        $fn_block = new Function_();
      }
      if ($fn_block) {
        $fn_block->addToken($token);
        // When it considers itself done, add it to the output buffer and move
        // on to the next one
        if ($fn_block->isComplete()) {
          $this->add($fn_block);
          $fn_block = null;
        }
      }
      else {
        $this->add($token);
      }
    } // Token loop

    // render and output
    echo ($this->output);
  }

  /**
   * @param StringlikeInterface thing to putput
   * @return this
   */
  private function add(StringlikeInterface $tok) {
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

