<?php

namespace DspLib\Test;

use DspLib\StringUtils;

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

    public function testToCamelCase()
    {
        $sTestString = "snake_case_string";
        $sResult = StringUtils::toCamelCase($sTestString);

        $this->assertEquals("SnakeCaseString", $sResult);
    }

    public function testToSnakeCase()
    {
        $sTestString = "CamelCaseString";
        $sResult = StringUtils::toSnakeCase($sTestString);

        $this->assertEquals("camel_case_string", $sResult);
    }

    public function testStripAccents()
    {
        $sTestString = "à â ä é è ê ë î ï ô ö ù û ü Â À Ä É È Ê Ë Î Ï Ô Ö Ù Û Ü";
        $sResult = StringUtils::stripAccents($sTestString);

        $this->assertEquals("a a a e e e e i i o o u u u A A A E E E E I I O O U U U", $sResult);
    }
}
