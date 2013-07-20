<?php

namespace DspLib;

class ConsoleColors
{
	const FG_BLACK = '0;30';
	const FG_DARK_GRAY = '1;30';
	const FG_BLUE = '0;34';
	const FG_LIGHT_BLUE = '1;34';
	const FG_GREEN = '0;32';
	const FG_LIGHT_GREEN = '1;32';
	const FG_CYAN = '0;36';
	const FG_RED = '0;31';
	const FG_LIGHT_RED = '1;31';
	const FG_PURPLE = '0;35';
	const FG_LIGHT_PURPLE = '1;35';
	const FG_BROWN = '0;33';
	const FG_YELLOW = '1;33';
	const FG_LIGHT_GRAY = '0;37';
	const FG_WHITE = '1;37';

	const BG_BLACK = '30';
	const BG_RED = '41';
	const BG_GREEN = '42';
	const BG_YELLOW = '43';
	const BG_BLUE = '44';
	const BG_MAGENTA = '45';
	const BG_CYAN = '46';
	const BG_LIGHT_GRAY = '47';

	const OPT_BOLD = '1';
	const OPT_DIM = '2';
	const OPT_UNDERSCORE = '4';
	const OPT_BLINK = '5';
	const OPT_REVERSE = '7';

	public static function getString($string, $fgColor = null, $bgColor = null, $option = null)
	{
		$coloredString = "";

		if (isset($fgColor)) {
			$coloredString .= "\033[" . $fgColor . "m";
		}

		if (isset($bgColor)) {
			$coloredString .= "\033[" . $bgColor . "m";
		}

		if (isset($option)) {
			$coloredString .= "\033[" . $option . "m";
		}

		$coloredString .=  $string;

		if (isset($fgColor) || isset($bgColor)) {
			$coloredString .= "\033[0m";
		}

		return $coloredString;
	}

	public static function showError($message)
	{
		$size = strlen(utf8_decode($message)) + 6;
		$inter = str_repeat(" ", $size);

		$ret = ConsoleColors::getString($inter, ConsoleColors::FG_WHITE, ConsoleColors::BG_RED) . PHP_EOL;
		$ret .= ConsoleColors::getString("   " . $message . "   ", ConsoleColors::FG_WHITE, ConsoleColors::BG_RED) . PHP_EOL;
		$ret .= ConsoleColors::getString($inter, ConsoleColors::FG_WHITE, ConsoleColors::BG_RED);

		return $ret;
	}
}
