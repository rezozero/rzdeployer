<?php
/**
 * Copyright REZO ZERO 2014
 *
 *
 *
 *
 * @file Password.php
 * @copyright REZO ZERO 2014
 * @author Ambroise Maupate
 */
namespace rezozero\Deployer\Controllers;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Password
{
    /**
     * @param $password
     * @return string
     */
    public function encrypt($password)
    {
        $process = new Process('openssl passwd -crypt "'.$password.'"');
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return trim($process->getOutput());
    }

    /**
     * @param int $length
     * @return mixed
     */
    public function generate($length = 8)
    {
        $process = new Process("openssl rand -base64 " . ($length*5) . " | tr -dc \"a-zA-Z0-9\-\_\\$\@\" | fold -w " . $length . " | head -n 1");
        $process->run();
        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        return trim($process->getOutput());
    }
}
