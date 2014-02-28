<?php

/**
 * Config test class
 *
 * @package Test
 * @author Pierre Feyssaguet <pfeyssaguet@gmail.com>
 */

namespace DspLib\Test;

use DspLib\Config;

/**
 * Config test class
 *
 * @package Test
 * @author Pierre Feyssaguet <pfeyssaguet@gmail.com>
 */
class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Ensures that setParam and getParam methods do their job
     */
    public function testSetAndGetParam()
    {
        $oConfig = Config::getInstance();

        $oConfig->setParam('test', 'value');

        $sTest = $oConfig->getParam('test');
        $this->assertEquals('value', $sTest);

        $sTest2 = $oConfig->getParam('test2', 'blah');
        $this->assertEquals('blah', $sTest2);

        $sTest3 = $oConfig->getParam('test3');
        $this->assertNull($sTest3);
    }
}
