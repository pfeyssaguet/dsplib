<?php

namespace DspLib;

class Console
{
	public static function getUserInput($silent = false)
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

