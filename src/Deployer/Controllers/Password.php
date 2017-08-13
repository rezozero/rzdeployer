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

        return $process->getOutput();
    }

    /**
     * @param int $length
     * @return mixed
     */
    public function generate($length = 8)
    {
        //cat /dev/urandom| tr -dc 'a-zA-Z0-9' | fold -w 12| head -n 1

        $process = new Process("openssl rand -base64 36 | tr -dc 'a-zA-Z0-9\-\_\\$\@' | fold -w ".(int)$length." | head -n 1");
        $process->run();
        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        return $process->getOutput();
    }
}
