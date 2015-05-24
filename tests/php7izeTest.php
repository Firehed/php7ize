<?php

class php7izeTest extends \PHPUnit_Framework_TestCase {

  public function getFiles() {
    $pattern = __DIR__.DIRECTORY_SEPARATOR.'fixtures/*.php5';
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
    $project_root = dirname(__DIR__);
    $bin = $project_root.DIRECTORY_SEPARATOR.
      'bin'.DIRECTORY_SEPARATOR.
      'php7ize';
    $cmd = sprintf('%s --quiet --stdout %s',
      $bin,
      $file);
    $out = [];
    $ret = null;
    exec($cmd, $out, $ret);
    $out[] = ""; // Exec seems to swallow any trailing newline, breaking diffs
    if ($ret) {
      throw new \Exception("Command failed");
    }
    return implode("\n",$out);
  }

}
