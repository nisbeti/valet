<?php

use Symfony\Component\Process\Process;

/**
 * Simple global function to run commands.
 */
function quietly($command)
{
    (new Process($command))->run();
}

/**
 * Retry the given function N times.
 *
 * @param  int  $retries
 * @param  callable  $retries
 * @param  int  $sleep
 * @return mixed
 */
function retry($retries, $fn, $sleep = 0)
{
    beginning:
    try {
        return $fn();
    } catch (Exception $e) {
        if (!$retries) {
            throw $e;
        }
        $retries--;
        if ($sleep > 0) {
            usleep($sleep * 1000);
        }
        goto beginning;
    }
}

/**
 * Check the system's compatibility with Valet.
 *
 * @return bool
 */
function should_be_compatible()
{
    // if (PHP_OS != 'Darwin') {
    //     echo 'Valet only supports the Mac operating system.'.PHP_EOL;

    //     exit(1);
    // }

    if (version_compare(PHP_VERSION, '5.5.9', '<')) {
        echo "Valet requires PHP 5.5.9 or later.";

        exit(1);
    }

    if (PHP_OS === 'Darwin' && exec('which brew') != '/usr/local/bin/brew') {
        echo 'Valet requires Brew to be installed on your Mac.';

        exit(1);
    }
}

/**
 * Verify that a command is being run as "sudo".
 *
 * @return void
 */
function should_be_sudo()
{
    if (! windows_os() && ! isset($_SERVER['SUDO_USER'])) {
        throw new Exception('This command must be run with sudo.');
    }
}

/**
 * Get the User's home path.
 *
 * @return string
 */
function home_path()
{
    if (! empty($_SERVER['HOME'])) {
        return $_SERVER['HOME'];
    } elseif (! empty($_SERVER['HOMEDRIVE']) && ! empty($_SERVER['HOMEPATH'])) {
        return $_SERVER['HOMEDRIVE'].$_SERVER['HOMEPATH'];
    } else {
        throw new Exception('Cannot determine home directory.');
    }
}

/**
 * Determine whether the current environment is Windows based.
 *
 * @return bool
 */
function windows_os()
{
    return strtolower(substr(PHP_OS, 0, 3)) === 'win';
}
