<?php

/**
 * XMLCodeBeautifier test class
 *
 * @package Test
 * @author  Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since   10 avr. 2013 11:14:48
 */
namespace DspLib\Test;

use DspLib\XMLCodeBeautifier;

/**
 * XMLCodeBeautifier test class
 *
 * @package Test
 * @author  Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since   10 avr. 2013 11:14:48
 */

class XMLCodeBeautifierTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Ensures that the 'formatCode' method works as intended
     */
    public function testFormatCode()
    {
        $s = file_get_contents(__DIR__ . '/Database/table1.xml');
        $sActual = XMLCodeBeautifier::formatCode($s);
        $sExpected = file_get_contents(__DIR__ . '/expected.txt');

        $this->assertEquals($sExpected, $sActual);
    }
}
