<?php

namespace Firehed\PHP7ize;

class TypeFixer {

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
    // In the event there code has an actual Scalar class, no typehint will be
    // added
    'scalar',
    // These are reserved keywords which theoretically are valid, but completely
    // pointless as they're a specific value
    'null',
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

  /**
   * @param string original type
   * @return string fixed type
   */
  public static function fixType($original_type) {
    $type = $original_type;
    if (isset(self::$coercions[$type])) {
      $type = self::$coercions[$type];
    }

    if (in_array($type, self::$blacklisted_typehints)) {
      return '';
    }

    return $type;
  } // fixType

}
