<?php

/**
 * Template test class file
 *
 * @author  Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @package Test
 */
namespace DspLib\Test;

use DspLib\Template;
use DspLib\DataSource\DataSourceArray;

/**
 * Template test class
 *
 * @author  Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @package Test
 */
class TemplateTest extends \PHPUnit_Framework_TestCase
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
        Template::setRootPath('');
        foreach ($this->aTempFiles as $sFilePath) {
            unlink($sFilePath);
        }
    }

    /**
     * Check that an InvalidArgumentException is thrown if wrong file is supplied to constructor
     */
    public function testUnableToFindTemplateFile()
    {
        $this->setExpectedException('InvalidArgumentException');
        $oTemplate = new Template('unexisting.tpl');
    }

    public function testSetRootPath()
    {
        Template::setRootPath('test path');

        $sActualRootPath = Template::getRootPath();
        $sExpectedRootPath = 'test path';

        $this->assertEquals($sExpectedRootPath, $sActualRootPath);
    }

    public function testConstructWithCustomRootPath()
    {
        $sCustomRootPath = __DIR__ . '/testTemplateCustomRootPath';
        mkdir($sCustomRootPath);
        $sFilePath = tempnam($sCustomRootPath, 'test.Template.');

        file_put_contents($sFilePath, '{test}');

        Template::setRootPath($sCustomRootPath);

        $oTemplate = new Template(basename($sFilePath));
        $oTemplate->setParam('test', 'test value');

        $sActualResult = $oTemplate->render();
        $sExpectedResult = 'test value';

        $this->assertEquals($sExpectedResult, $sActualResult);

        unlink($sFilePath);
        rmdir($sCustomRootPath);
    }

    public function testSimpleParam()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '{test}');

        $oTemplate = new Template($sFilePath);
        $oTemplate->setParam('test', 'test value');

        $sActualResult = $oTemplate->render();
        $sExpectedResult = 'test value';

        $this->assertEquals($sExpectedResult, $sActualResult);
    }

    public function testSetParams()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '{test} {test2}');

        $oTemplate = new Template($sFilePath);
        $aParams = array(
            'test' => 'test value',
            'test2' => 'second test value',
        );
        $oTemplate->setParams($aParams);

        $sActualResult = $oTemplate->render();
        $sExpectedResult = 'test value second test value';

        $this->assertEquals($sExpectedResult, $sActualResult);
    }

    public function testAddParams()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '{test} {test2} {test3}');

        $oTemplate = new Template($sFilePath);
        $oTemplate->setParam('test', 'test value');

        $aParams = array(
            'test2' => 'second test value',
            'test3' => 'third test value',
        );
        $oTemplate->addParams($aParams);

        $sActualResult = $oTemplate->render();
        $sExpectedResult = 'test value second test value third test value';

        $this->assertEquals($sExpectedResult, $sActualResult);
    }

    public function testDefaultParam()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '{test}');

        Template::addDefaultParam('test', 'test value');

        $oTemplate = new Template($sFilePath);

        $sActualResult = $oTemplate->render();
        $sExpectedResult = 'test value';

        $this->assertEquals($sExpectedResult, $sActualResult);
    }

    public function testArrayParam()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '<!-- BEGIN array -->{array.test}<!-- END array -->');

        $oTemplate = new Template($sFilePath);
        $oTemplate->setList('testList', array('a', 'b', 'c'));

        $aData = array(
            array('test' => 'val0'),
            array('test' => 'val1'),
            array('test' => 'val2'),
        );
        $oTemplate->setParam('array', $aData);

        $sResult = $oTemplate->render();

        $this->assertEquals('val0val1val2', $sResult);
    }

    public function testArrayParamWithNotArrayParam()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '<!-- BEGIN array -->{array.test}<!-- END array -->');

        $oTemplate = new Template($sFilePath);
        $oTemplate->setParam('array', array('test'));

        $this->setExpectedException('\InvalidArgumentException');
        $oTemplate->render();
    }

    public function testArrayParamWithoutParam()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '<!-- BEGIN array -->{array.test}<!-- END array -->');

        $oTemplate = new Template($sFilePath);

        $sResult = $oTemplate->render();

        $this->assertEquals('', $sResult);
    }

    public function testDataSourceParam()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '<!-- BEGIN array -->{array.test}<!-- END array -->');

        $oTemplate = new Template($sFilePath);
        $aData = array(
            array('test' => 'val0'),
            array('test' => 'val1'),
            array('test' => 'val2'),
        );
        $oDataSource = new DataSourceArray($aData);
        $oTemplate->setParam('array', $oDataSource);

        $sResult = $oTemplate->render();

        $this->assertEquals('val0val1val2', $sResult);
    }

    public function testSimpleArrayParams()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '{test.test1} {test.test2}');

        $oTemplate = new Template($sFilePath);
        $oTemplate->setParam(
            'test',
            array(
                'test1' => 'test value',
                'test2' => 'second test value',
            )
        );

        $sActualResult = $oTemplate->render();
        $sExpectedResult = 'test value second test value';

        $this->assertEquals($sExpectedResult, $sActualResult);
    }

    public function testIfTrue()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '<!-- IF condition -->true<!-- ENDIF condition -->');

        $oTemplate = new Template($sFilePath);
        $oTemplate->setParam('condition', true);

        $sResult = $oTemplate->render();

        $this->assertEquals('true', $sResult);
    }

    public function testIfNotTrue()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '<!-- IFNOT condition -->true<!-- ENDIF condition -->');

        $oTemplate = new Template($sFilePath);
        $oTemplate->setParam('condition', true);

        $sResult = $oTemplate->render();

        $this->assertEquals('', $sResult);
    }

    public function testIfWithoutParam()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '<!-- IF condition -->true<!-- ENDIF condition -->');

        $oTemplate = new Template($sFilePath);

        $sResult = $oTemplate->render();

        $this->assertEquals('', $sResult);
    }

    public function testIfNotWithoutParam()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '<!-- IFNOT condition -->true<!-- ENDIF condition -->');

        $oTemplate = new Template($sFilePath);

        $sResult = $oTemplate->render();

        $this->assertEquals('true', $sResult);
    }

    public function testIfFalse()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '<!-- IF condition -->true<!-- ENDIF condition -->');

        $oTemplate = new Template($sFilePath);
        $oTemplate->setParam('condition', false);

        $sResult = $oTemplate->render();

        $this->assertEquals('', $sResult);
    }

    public function testIfNotFalse()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '<!-- IFNOT condition -->true<!-- ENDIF condition -->');

        $oTemplate = new Template($sFilePath);
        $oTemplate->setParam('condition', false);

        $sResult = $oTemplate->render();

        $this->assertEquals('true', $sResult);
    }

    public function testIfElseTrue()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '<!-- IF condition -->true<!-- ELSE condition -->false<!-- ENDIF condition -->');

        $oTemplate = new Template($sFilePath);
        $oTemplate->setParam('condition', true);

        $sResult = $oTemplate->render();

        $this->assertEquals('true', $sResult);
    }

    public function testIfElseFalse()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '<!-- IF condition -->true<!-- ELSE condition -->false<!-- ENDIF condition -->');

        $oTemplate = new Template($sFilePath);
        $oTemplate->setParam('condition', false);

        $sResult = $oTemplate->render();

        $this->assertEquals('false', $sResult);
    }
}
