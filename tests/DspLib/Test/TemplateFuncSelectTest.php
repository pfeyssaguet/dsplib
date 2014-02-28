<?php

/**
 * Test of the funcSelect method from Template class
 *
 * @package Test
 * @author Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since  7 avr. 2013 09:05:27
 */

namespace DspLib\Test;

use DspLib\Template;

/**
 * Test of the funcSelect method from Template class
 *
 * @package Test
 * @author  Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since   7 avr. 2013 09:05:27
 */
class TemplateFuncSelectTest extends \PHPUnit_Framework_TestCase
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

    public function testFuncSelectWithoutListTriggerError()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '{select:testSelect,Fruits,2}');

        $oTemplate = new Template($sFilePath);

        $this->setExpectedException('PHPUnit_Framework_Error_Warning');
        $oTemplate->render();
    }

    public function testFuncSelectWithoutListAfterTriggerError()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '{select:testSelect,Fruits,2}');

        $oTemplate = new Template($sFilePath);

        $sActualValue = @$oTemplate->render();
        $sExpectedValue = '2';
        $this->assertEquals($sExpectedValue, $sActualValue);
    }

    public function testFuncSelect()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '{select:testSelect,Fruits,2}');

        $oTemplate = new Template($sFilePath);
        $aFruits = array(
            '1' => 'Apples',
            '2' => 'Pears',
            '3' => 'Peaches',
        );

        $oTemplate->setList('Fruits', $aFruits);

        $sActualResult = $oTemplate->render();

        $sExpectedResult = <<<HTML
        <select id="testSelect" name="testSelect">
        <option  value="1">Apples</option>
        <option selected="selected" value="2">Pears</option>
        <option  value="3">Peaches</option>
        </select>

HTML;

        $this->assertEquals($sExpectedResult, $sActualResult);
    }

    public function testFuncSelectWithOnChange()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '{select:testSelect,Fruits,2,testOnChange()}');

        $oTemplate = new Template($sFilePath);
        $aFruits = array(
            '1' => 'Apples',
            '2' => 'Pears',
            '3' => 'Peaches',
        );

        $oTemplate->setList('Fruits', $aFruits);

        $sActualResult = $oTemplate->render();

        $sExpectedResult = <<<HTML
        <select id="testSelect" name="testSelect" onchange="testOnChange()">
        <option  value="1">Apples</option>
        <option selected="selected" value="2">Pears</option>
        <option  value="3">Peaches</option>
        </select>

HTML;

        $this->assertEquals($sExpectedResult, $sActualResult);
    }

    public function testFuncSelectCallback()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '{select:testSelect,Fruits,2,testOnChange()}');

        $oTemplate = new Template($sFilePath);
        $aFruits = array(
            '1' => 'Apples',
            '2' => 'Pears',
            '3' => 'Peaches',
        );

        $oTemplate->setList('Fruits', $aFruits);

        $oTemplate->setCallbackSelect(array(__CLASS__, 'customCallback'));

        $sActualResult = $oTemplate->render();

        $sExpectedResult = 'testSelect => Pears';

        $this->assertEquals($sExpectedResult, $sActualResult);
    }

    public static function customCallback($sField, array $aList, $sDefault)
    {
        return $sField . ' => ' . $aList[$sDefault];
    }
}
