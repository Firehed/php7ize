<?php

namespace Firehed\PHP7ize;

use Firehed\Plow\CommandInterface;
use Firehed\Plow\CommandTrait;
use Firehed\Plow\Option;

class Runner implements CommandInterface {

  use CommandTrait;

  /**
   * Run the command
   * @return int CLI exit code
   */
  public function execute() {
    $source_file = $this->getOperand(0);
    if (!$source_file) {
      $this->usageError('Source file is required');
    }
    $output_file = $this->getOption('o');
    $transformed = (new Converter())
      ->setSource($source_file)
      ->setOutputFile($output_file)
      ->addTransformer(new Transformers\FunctionAnnotater())
      ->convert();
    if ($this->getOption('stdout')) {
      $this->output->writeLine($transformed);
    }
  }

  /**
   * Generate the 'usage' banner. Can include a single %s for the command
   * name. Does NOT require a trailing newline. An empty return value will
   * cause the default banner to be used.
   *
   * Default: 'Usage: %s [options] [operands]'
   *
   * @return string The usage banner
   */
  public function getBanner() {
    return 'Usage: %s [options] source_file';
  }

  /**
   * @return array<string>|string The command, excluding "plow"
   */
  public function getCommandName() {
    return 'convert';
  }

  /**
   * Command description. It should look roughly like a git commit message,
   * where the first line is a short synopsis, with the message body
   * containing more detailed information.
   *
   * @return string The description
   */
  public function getDescription() {
    return 'Convert stuff to PHP7!';
  }

  /**
   * Desired CLI options. Plow uses the Getopt library, so this function must
   * return an array of Getopt Options.
   *
   * The following options will automatically be added, and must not be
   * included:
   * -h/--help
   * -v/--verbose
   * -q/--quiet
   * -V/--version
   *
   * The \Firehed\Plow\Option class provides factory methods around the Getopt
   * library for convenience.
   *
   * @see http://ulrichsg.github.io/getopt-php/advanced/option-descriptions.html
   * @return array<\Ulrichsg\Getopt\Option>
   */
  public function getOptions() {
    return [
      Option::withRequiredValue('o', 'output-file')
        ->setDescription('Write output to designated file'),
      Option::withCount(null, 'stdout')
        ->setDescription('Output converted text to STDOUT'),
    ];
  }

}
