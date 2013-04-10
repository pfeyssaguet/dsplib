<?php

/**
 * Test of the funcDate method from Template class
 *
 * @package Test
 * @author Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since  7 avr. 2013 09:43:00
 */

namespace DspLib\Test;

use DspLib\Template;

/**
 * Test of the funcDate method from Template class
 *
 * @package Test
 * @author Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since  7 avr. 2013 09:43:00
 */
class TemplateFuncDateTest extends \PHPUnit_Framework_TestCase
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

    public function testFuncDate()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '{date:2013-04-07 09:47:36}');

        $oTemplate = new Template($sFilePath);

        $sActualResult = $oTemplate->render();

        $sExpectedResult = '07/04/2013 9:47';

        $this->assertEquals($sExpectedResult, $sActualResult);
    }

    public function testFuncDateWithNoMatchingDateFormat()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '{date:not a date}');

        $oTemplate = new Template($sFilePath);

        $sActualResult = $oTemplate->render();

        $sExpectedResult = 'not a date';

        $this->assertEquals($sExpectedResult, $sActualResult);
    }

    public function testFuncDateWithParam()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '{date:{paramDate}}');

        $oTemplate = new Template($sFilePath);
        $oTemplate->setParam('paramDate', '2013-04-07 09:47:36');

        $sActualResult = $oTemplate->render();

        $sExpectedResult = '07/04/2013 9:47';

        $this->assertEquals($sExpectedResult, $sActualResult);
    }


    public function testFuncDateWithFormat()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '{date:2013-04-07 09:47:36,YmdHis}');

        $oTemplate = new Template($sFilePath);

        $sActualResult = $oTemplate->render();

        $sExpectedResult = '20130407094736';

        $this->assertEquals($sExpectedResult, $sActualResult);
    }

    public function testFuncDateCallback()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '{date:2013-04-07 09:47:36}');

        $oTemplate = new Template($sFilePath);

        $oTemplate->setCallbackDate(array(__CLASS__, 'customCallback'));

        $sActualResult = $oTemplate->render();

        $sExpectedResult = '==2013-04-07 09:47:36==';

        $this->assertEquals($sExpectedResult, $sActualResult);
    }

    public static function customCallback($sDate)
    {
        return '=='.$sDate.'==';
    }
}
