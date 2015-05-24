<?php

class X {

  /**
   * @param int bar that is wrong
   * @return array
   */
  private function mismatch(Foo $bar) {
    return [];
  }

}
