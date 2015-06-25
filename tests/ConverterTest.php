<?php

namespace Firehed\PHP7ize;

class ConverterTest extends \PHPUnit_Framework_TestCase {

  public function getFiles() {
    $pattern = __DIR__.DIRECTORY_SEPARATOR.'fixtures/**/*.php5';
    $files = [];
    foreach (glob($pattern) as $file) {
      $files[] = [$file];
    }
    return $files;
  }

  /**
   * @dataProvider getFiles
   */
  public function testConversion($input_file) {
    $output_file = substr($input_file, 0, -1).'7';
    $converted = $this->convert($input_file);
    $this->assertStringEqualsFile($output_file,
      $converted,
      "Generated output was incorrect for $input_file");
  }

  private function convert($file) {
    $converter = new Converter();
    return $converter
      ->setSource($file)
      ->addTransformer(new Transformers\FunctionAnnotater())
      ->convert();
  }

}
