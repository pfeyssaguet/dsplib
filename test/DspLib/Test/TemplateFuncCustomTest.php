<?php

/**
 * Test of the funcCustom method from Template class
 *
 * @author Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since  7 avr. 2013 10:03:00
 */

namespace DspLib\Test;

use DspLib\Template;

class TemplateFuncCustomTest extends \PHPUnit_Framework_TestCase
{
    private $aTempFiles = array();

    private function getTempFile()
    {
        $sFilePath = tempnam('.', 'test.Template.');
        $this->aTempFiles[] = $sFilePath;
        return $sFilePath;
    }

    public function tearDown()
    {
        Template::clearDefaultParams();
        foreach ($this->aTempFiles as $sFilePath) {
            unlink($sFilePath);
        }
    }

    public function testFuncCustom()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '{myCustomTest:abracadabra}');

        $oTemplate = new Template($sFilePath);
        Template::addCallbackCustom('myCustomTest', array(__CLASS__, 'customCallback'));

        $sActualResult = $oTemplate->render();

        $sExpectedResult = '==abracadabra==';

        $this->assertEquals($sExpectedResult, $sActualResult);
    }

    public function testFuncCustomWithoutExistingFunctionTriggerError()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '{unexistingCustom:abracadabra}');

        $oTemplate = new Template($sFilePath);

        $this->setExpectedException('\PHPUnit_Framework_Error_Notice');
        $oTemplate->render();
    }

    public function testFuncCustomWithoutExistingFunctionAfterTriggerError()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '{unexistingCustom:abracadabra}');

        $oTemplate = new Template($sFilePath);

        $sActual = @$oTemplate->render();
        $sExpected = 'abracadabra';

        $this->assertEquals($sExpected, $sActual);
    }

    public static function customCallback($sParam)
    {
        return '=='.$sParam.'==';
    }
}
