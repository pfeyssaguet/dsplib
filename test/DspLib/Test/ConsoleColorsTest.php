<?php

namespace DspLib\Test;

use DspLib\ConsoleColors;

class ConsoleColorsTest extends \PHPUnit_Framework_TestCase
{
    private $consoleColors;

    public function setUp()
    {
        $this->consoleColors = new ConsoleColors();
    }

    /**
     * @dataProvider providerColoredString
     */
    public function testColoredString($testString, $fgColor, $bgColor, $options, $expectedOutput)
    {
        $actualOutput = $this->consoleColors->getString($testString, $fgColor, $bgColor, $options);
        $this->assertEquals($expectedOutput, $actualOutput);
    }

    public function providerColoredString()
    {
        return array(
            array(
                'blanc sur fond rouge',
                ConsoleColors::FG_WHITE,
                ConsoleColors::BG_RED,
                null,
                "\033[1;37m\033[41mblanc sur fond rouge\033[0m"),
            array(
                'blanc gras sur fond rouge',
                ConsoleColors::FG_WHITE,
                ConsoleColors::BG_RED,
                ConsoleColors::OPT_BOLD,
                "\033[1;37m\033[41m\033[1mblanc gras sur fond rouge\033[0m",
            ),
            array(
                'vert sur fond bleu',
                ConsoleColors::FG_GREEN,
                ConsoleColors::BG_BLUE,
                null,
                "\033[0;32m\033[44mvert sur fond bleu\033[0m",
            ),
            array(
                'vert souligné sur fond par défaut',
                ConsoleColors::FG_GREEN,
                null,
                ConsoleColors::OPT_UNDERSCORE,
                "\033[0;32m\033[4mvert souligné sur fond par défaut\033[0m",
            ),
            array(
                'noir clignotant sur fond cyan',
                ConsoleColors::FG_BLACK,
                ConsoleColors::BG_CYAN,
                ConsoleColors::OPT_BLINK,
                "\033[0;30m\033[46m\033[5mnoir clignotant sur fond cyan\033[0m",
            ),
        );
    }

    public function testShowError()
    {
        $actualOutput = $this->consoleColors->showError("Y a un problème ??");
        $expectedOutput = "\033[1;37m\033[41m                        \033[0m" . PHP_EOL;
        $expectedOutput .= "\033[1;37m\033[41m   Y a un problème ??   \033[0m" . PHP_EOL;
        $expectedOutput .= "\033[1;37m\033[41m                        \033[0m";

        $this->assertEquals($expectedOutput, $actualOutput);
    }
}
