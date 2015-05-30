<?php

namespace Firehed\PHP7ize;

interface TransformerInterface {

  /**
   * @param array<Token> tokens
   * @return array<Token> transformed tokens
   */
  public function transform(array $tokens);

}
