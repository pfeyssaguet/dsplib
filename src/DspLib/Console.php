<?php

namespace DspLib;

class Console
{
    public function getUserInput($silent = false)
    {
        if ($silent) {
            shell_exec('stty -echo');
        }

        $userInput = fgets(STDIN);

        if ($silent) {
            shell_exec('stty echo');
        }

        return $userInput;
    }
}
