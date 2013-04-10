<?php

/**
 * Test of the funcDataSource method from Template class
 *
 * @author Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since  7 avr. 2013 10:03:00
 */

namespace DspLib\Test;

use DspLib\Template;
use DspLib\DataSource\DataSourceArray;
use DspLib\Language;
use DspLib\DataSource\DataSource;

class TemplateFuncDataSourceTest extends \PHPUnit_Framework_TestCase
{
    private $aTempFiles = array();
    private $oDataSource = null;

    private function getTempFile()
    {
        $sFilePath = tempnam('.', 'test.Template.');
        $this->aTempFiles[] = $sFilePath;
        return $sFilePath;
    }

    public function setUp()
    {
        $sFilePath = __DIR__ . '/fr.xml';
        $this->aTempFiles[] = $sFilePath;

        $sXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<language name="Français">
    <term name="RECORDS">Lignes</term>
</language>
XML;
        file_put_contents($sFilePath, $sXml);

        $sFilePath = __DIR__ . '/en.xml';
        $this->aTempFiles[] = $sFilePath;

        $sXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<language name="English">
    <term name="RECORDS">Rows</term>
</language>
XML;
        file_put_contents($sFilePath, $sXml);

        Language::addPath(__DIR__);

        $oTemplateDisplayTable = new Template();
        $sData = <<<HTML
<!-- BEGIN Titles -->
<th>{Titles.Value}</th>
<!-- END Titles -->
</tr>
</thead>
<tbody>
<!-- BEGIN Row -->
<tr>
<!-- BEGIN Row.Cell -->
<td>{Row.Cell.Value}</td>
<!-- END Row.Cell -->
</tr>
<!-- END Row -->
<p>{NumRows} {lang:RECORDS}</p>
HTML;
        $oTemplateDisplayTable->setData($sData);

        DataSource::setTplDisplayTable($oTemplateDisplayTable);

        $aData = array(
            array('a' => 1, 'b' => 2, 'c' => 3),
            array('a' => 4, 'b' => 5, 'c' => 6),
        );
        $this->oDataSource = new DataSourceArray($aData);
    }

    public function tearDown()
    {
        Template::clearDefaultParams();
        foreach ($this->aTempFiles as $sFilePath) {
            unlink($sFilePath);
        }
    }

    public function testFuncDataSource()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '{ds:param}');

        $oTemplate = new Template($sFilePath);
        $oTemplate->setParam('param', $this->oDataSource);

        $sActualResult = $oTemplate->render();

        $sExpectedResult = <<<HTML

<th>a</th>

<th>b</th>

<th>c</th>

</tr>
</thead>
<tbody>

<tr>

<td>1</td>

<td>2</td>

<td>3</td>

</tr>

<tr>

<td>4</td>

<td>5</td>

<td>6</td>

</tr>

<p>2 Lignes</p>
HTML;

        $this->assertEquals($sExpectedResult, $sActualResult);
    }

    public function testFuncDataSourceWithArray()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '{ds:param}');

        $oTemplate = new Template($sFilePath);
        $oTemplate->setParam('param', $this->oDataSource->getRows());

        $sActualResult = $oTemplate->render();

        $sExpectedResult = <<<HTML

<th>a</th>

<th>b</th>

<th>c</th>

</tr>
</thead>
<tbody>

<tr>

<td>1</td>

<td>2</td>

<td>3</td>

</tr>

<tr>

<td>4</td>

<td>5</td>

<td>6</td>

</tr>

<p>2 Lignes</p>
HTML;

        $this->assertEquals($sExpectedResult, $sActualResult);
    }

    public function testFuncDataSourceWithoutParam()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '{ds:param}');

        $oTemplate = new Template($sFilePath);

        $sActualResult = $oTemplate->render();

        $sExpectedResult = "";

        $this->assertEquals($sExpectedResult, $sActualResult);
    }

    public function testFuncDataSourceWithDecorator()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '{ds:param,\DspLib\Test\DataSource\DataSourceDecoratorSimple}');

        $oTemplate = new Template($sFilePath);
        $oTemplate->setParam('param', $this->oDataSource);

        $sActualResult = $oTemplate->render();

        $sExpectedResult = <<<HTML

<th>_a_</th>

<th>_b_</th>

<th>_c_</th>

</tr>
</thead>
<tbody>

<tr>

<td>_1_</td>

<td>_2_</td>

<td>_3_</td>

</tr>

<tr>

<td>_4_</td>

<td>_5_</td>

<td>_6_</td>

</tr>

<p>2 Lignes</p>
HTML;

        $this->assertEquals($sExpectedResult, $sActualResult);
    }

    public function testFuncDataSourceWithDecoratorWhichDoesNotExistsTriggerError()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '{ds:param,DataSourceDecoratorWhichDoesNotExists}');

        $oTemplate = new Template($sFilePath);
        $oTemplate->setParam('param', $this->oDataSource);

        $this->setExpectedException('PHPUnit_Framework_Error_Notice');
        $oTemplate->render();
    }

    public function testFuncDataSourceWithDecoratorWhichDoesNotExistsAfterTriggerError()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '{ds:param,DataSourceDecoratorWhichDoesNotExists}');

        $oTemplate = new Template($sFilePath);
        $oTemplate->setParam('param', $this->oDataSource);

        $sActualResult = @$oTemplate->render();
        $sExpectedResult = <<<HTML

<th>a</th>

<th>b</th>

<th>c</th>

</tr>
</thead>
<tbody>

<tr>

<td>1</td>

<td>2</td>

<td>3</td>

</tr>

<tr>

<td>4</td>

<td>5</td>

<td>6</td>

</tr>

<p>2 Lignes</p>
HTML;

        $this->assertEquals($sExpectedResult, $sActualResult);
    }
}
