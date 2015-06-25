<?php

namespace Firehed\PHP7ize\Transformers;

use Firehed\PHP7ize\TransformerInterface;
use Firehed\PHP7ize\Token;
use Firehed\PHP7ize\Function_;

class FunctionAnnotater
  implements TransformerInterface {


  public function transform(array $tokens) {

    $fn_block = null;
    foreach ($tokens as $token) {
      // Effectively, capture everything from the start of a docblock until
      // a closing bracket. Bracket depth is managed by the function capture
      // object.
      if ($token->getType() === T_DOC_COMMENT) {
        // If we already had an incomplete docblock, it probably came from
        // a file- or class-level docblock. Just append as-is and reset for the
        // next one.
        if ($fn_block) {
          $this->addTokens($fn_block->getTokens());
          $fn_block = null;
        }
        $fn_block = new Function_();
      }
      if ($fn_block) {
        $fn_block->addToken($token);
        // When it considers itself done, add it to the output buffer and move
        // on to the next one
        if ($fn_block->isComplete()) {
          $this->addTokens($fn_block->getTokens());
          $fn_block = null;
        }
      }
      else {
        $this->addToken($token);
      }
    } // Token loop
    // Handle any dangling blocks
    if ($fn_block) {
      $this->addTokens($fn_block->getTokens());
    }

    return $this->getTokens();



  }

  private function addTokens(array $tokens) {
    foreach ($tokens as $token) {
      $this->addToken($token);
    }
    return $this;
  }

  private function addToken(Token $t) {
    $this->tokens[] = $t;
  }
  private function getTokens() {
    return $this->tokens;
  }

}
