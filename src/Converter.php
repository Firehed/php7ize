<?php

namespace Firehed\PHP7ize;

class Converter {

  // Suppress warnings and errors?
  private $is_quiet = false;
  // Output buffer
  private $output;
  // Source file
  private $source_file;
  // Transformers
  private $transformers = [];

  public function addTransformer(TransformerInterface $t) {
    $this->transformers[] = $t;
    return $this;
  }

  public function setIsQuiet($is_quiet) {
    $this->is_quiet = $is_quiet;
    return $this;
  }

  public function setSource($source_file) {
    $this->source_file = $source_file;
    return $this;
  }

  public function convert() {
    $tokens = array_map(function($raw_token) {
      return new Token($raw_token);
    }, token_get_all(file_get_contents($this->source_file)));

    foreach ($this->transformers as $transformer) {
      $tokens = $transformer->transform($tokens);
    }

    foreach ($tokens as $t) {
      $this->add($t);
    }
    return $this->output;
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

