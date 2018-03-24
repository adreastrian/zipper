<?php

use Symfony\Component\Process\Process;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Die and dump output to the buffer.
 *
 * @return void
 */
function dd()
{
    $output = new ConsoleOutput;

    foreach (func_get_args() as $value) {
        $output->write($value);
    }

    die;
}

/**
 * Output the given warning text to the console.
 *
 * @param  string $output
 * @return void
 */
function warning($output)
{
    dd('<fg=red>'.$output.'</>');
}

/**
 * Output the given text to the console.
 *
 * @param  string $output
 * @return void
 */
function info($output)
{
    dd('<info>'.$output.'</info>');
}

/**
 * Check if it's a directory.
 *
 * @param  string  $dir
 * @return boolean
 */
function isDirEmpty($dir)
{
    if (!is_readable($dir)) {
        return null;
    }

    return (count(scandir($dir)) == 2);
}

/**
 * Make a directory.
 *
 * @param  string  $path
 * @param  integer $mode
 * @return void
 */
function makeDir($path, $mode = 0755)
{
    mkdir($path, $mode, true);
}

/**
 * Run the given command.
 *
 * @param  string   $command
 * @param  callable $onError
 * @return string
 */
function runCommand($command, callable $onError = null)
{
    $onError = $onError ?: function () {};

    $process = new Process($command);

    $processOutput = '';
    $process->setTimeout(null)->run(function ($type, $line) use (&$processOutput) {
        $processOutput .= $line;
    });

    if ($process->getExitCode() > 0) {
        $onError($process->getExitCode(), $processOutput);
    }

    return $processOutput;
}

/**
 * Get the last item of the array.
 *
 * @param  array $val
 * @return mixed
 */
function arr_end($val = [])
{
    return end($val);
}
