<?php

/**
 * StringUtils test class
 *
 * @package Test
 * @author  Pierre Feyssaguet <pfeyssaguet@gmail.com>
 */

namespace DspLib\Test;

use DspLib\StringUtils;

/**
 * StringUtils test class
 *
 * @package Test
 * @author  Pierre Feyssaguet <pfeyssaguet@gmail.com>
 */
class StringUtilsTest extends \PHPUnit_Framework_TestCase
{
    public function testBeginsWith()
    {
        $sTestString = "hello";

        $this->assertTrue(StringUtils::beginsWith($sTestString, 'h'));
        $this->assertTrue(StringUtils::beginsWith($sTestString, 'he'));
        $this->assertFalse(StringUtils::beginsWith($sTestString, 'zz'));
    }

    public function testEndsWith()
    {
        $sTestString = "hello";

        $this->assertTrue(StringUtils::endsWith($sTestString, 'o'));
        $this->assertTrue(StringUtils::endsWith($sTestString, 'lo'));
        $this->assertFalse(StringUtils::endsWith($sTestString, 'zz'));
    }

    public function providerCamelCase()
    {
        return array(
            array('snake_case_string', 'SnakeCaseString'),
            array('snake_case', 'SnakeCase'),
            array('invalidString', 'InvalidString'),
            array('word', 'Word'),
        );
    }

    /**
     * @dataProvider providerCamelCase
     */
    public function testToCamelCase($sTestString, $sExpected)
    {
        $sActual = StringUtils::toCamelCase($sTestString);

        $this->assertEquals($sExpected, $sActual);
    }

    public function providerSnakeCase()
    {
        return array(
            array('CamelCaseString', 'camel_case_string'),
            array('Invalid_String', 'invalid_string'),
            array('Word', 'word'),
        );
    }

    /**
     * @dataProvider providerSnakeCase
     */
    public function testToSnakeCase($sTestString, $sExpected)
    {
        $sActual = StringUtils::toSnakeCase($sTestString);

        $this->assertEquals($sExpected, $sActual);
    }

    public function testStripAccents()
    {
        $sTestString = "à â ä é è ê ë î ï ô ö ù û ü Â À Ä É È Ê Ë Î Ï Ô Ö Ù Û Ü";
        $sResult = StringUtils::stripAccents($sTestString);

        $this->assertEquals("a a a e e e e i i o o u u u A A A E E E E I I O O U U U", $sResult);
    }
}
