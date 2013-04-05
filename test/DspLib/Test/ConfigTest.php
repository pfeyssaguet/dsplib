<?php

namespace DspLib\Test;

use DspLib\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
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
