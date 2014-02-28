<?php

/**
 * Test of the funcUrl method from Template class
 *
 * @package Test
 * @author  Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since   7 avr. 2013 10:03:00
 */

namespace DspLib\Test;

use DspLib\Template;

/**
 * Test of the funcUrl method from Template class
 *
 * @package Test
 * @author  Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since   7 avr. 2013 10:03:00
 */
class TemplateFuncUrlTest extends \PHPUnit_Framework_TestCase
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

    public function testFuncUrl()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '{url:abc=123,def=456}');

        $oTemplate = new Template($sFilePath);

        $sActualResult = $oTemplate->render();

        $sExpectedResult = 'index.php?abc=123&def=456';

        $this->assertEquals($sExpectedResult, $sActualResult);
    }

    public function testFuncUrlWithParam()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '{url:{param}}');

        $oTemplate = new Template($sFilePath);
        $oTemplate->setParam('param', 'abc');

        $sActualResult = $oTemplate->render();

        $sExpectedResult = 'index.php?abc';

        $this->assertEquals($sExpectedResult, $sActualResult);
    }

    public function testFuncUrlCallback()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '{url:abc=123}');

        $oTemplate = new Template($sFilePath);

        $oTemplate->setCallbackUrl(array(__CLASS__, 'customCallback'));

        $sActualResult = $oTemplate->render();

        $sExpectedResult = '==abc=123==';

        $this->assertEquals($sExpectedResult, $sActualResult);
    }

    public static function customCallback($sParam)
    {
        return '=='.$sParam.'==';
    }
}
