<?php

namespace DspLib\Test;

use DspLib\Template;

class TemplateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Check that an InvalidArgumentException is thrown if wrong file is supplied to constructor
     */
    public function testUnableToFindTemplateFile()
    {
        $this->setExpectedException('InvalidArgumentException');
        $oTemplate = new Template('unexisting.tpl');
    }

    public function testSimpleParam()
    {
        $sFilePath = tempnam('.', 'test.tpl.');
        file_put_contents($sFilePath, '{test}');

        $oTemplate = new Template($sFilePath);
        $oTemplate->setParam('test', 'test value');

        $sResult = $oTemplate->render();

        unlink($sFilePath);

        $this->assertEquals('test value', $sResult);
    }

    public function testArrayParam()
    {
        $sFilePath = tempnam('.', 'test.tpl.');
        file_put_contents($sFilePath, '<!-- BEGIN array -->{array.test}<!-- END array -->');

        $oTemplate = new Template($sFilePath);
        $aData = array(
            array('test' => 'val0'),
            array('test' => 'val1'),
            array('test' => 'val2'),
        );
        $oTemplate->setParam('array', $aData);

        $sResult = $oTemplate->render();

        unlink($sFilePath);

        $this->assertEquals('val0val1val2', $sResult);
    }

    public function testIfTrue()
    {
        $sFilePath = tempnam('.', 'test.tpl.');
        file_put_contents($sFilePath, '<!-- IF condition -->true<!-- ENDIF condition -->');

        $oTemplate = new Template($sFilePath);
        $oTemplate->setParam('condition', true);

        $sResult = $oTemplate->render();

        unlink($sFilePath);

        $this->assertEquals('true', $sResult);
    }

    public function testIfFalse()
    {
        $sFilePath = tempnam('.', 'test.tpl.');
        file_put_contents($sFilePath, '<!-- IF condition -->true<!-- ENDIF condition -->');

        $oTemplate = new Template($sFilePath);
        $oTemplate->setParam('condition', false);

        $sResult = $oTemplate->render();

        unlink($sFilePath);

        $this->assertEquals('', $sResult);
    }

    public function testIfElseTrue()
    {
        $sFilePath = tempnam('.', 'test.tpl.');
        file_put_contents($sFilePath, '<!-- IF condition -->true<!-- ELSE condition -->false<!-- ENDIF condition -->');

        $oTemplate = new Template($sFilePath);
        $oTemplate->setParam('condition', true);

        $sResult = $oTemplate->render();

        unlink($sFilePath);

        $this->assertEquals('true', $sResult);
    }

    public function testIfElseFalse()
    {
        $sFilePath = tempnam('.', 'test.tpl.');
        file_put_contents($sFilePath, '<!-- IF condition -->true<!-- ELSE condition -->false<!-- ENDIF condition -->');

        $oTemplate = new Template($sFilePath);
        $oTemplate->setParam('condition', false);

        $sResult = $oTemplate->render();

        unlink($sFilePath);

        $this->assertEquals('false', $sResult);
    }
}
