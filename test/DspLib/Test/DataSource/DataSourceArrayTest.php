<?php

namespace DspLib\Test\DataSource;

use DspLib\DataSource\DataSourceArray;
use DspLib\DataSource\DataSource;
use DspLib\Template;

class DataSourceArrayTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $this->setExpectedException('InvalidArgumentException');
        $odsArray = new DataSourceArray(array('a', 'b', 'c'));
    }

    public function testCount()
    {
        $odsArray = new DataSourceArray();

        $this->assertCount(0, $odsArray);
        $this->assertEquals(0, $odsArray->getTotalRowCount());

        $aData = array(
            array('a' => 1, 'b' => 2, 'c' => 3),
            array('a' => 4, 'b' => 5, 'c' => 6),
        );

        $odsArray = new DataSourceArray($aData);
        $this->assertCount(2, $odsArray);
        $this->assertEquals(2, $odsArray->getTotalRowCount());
    }

    public function testWriteRow()
    {
        $odsArray = new DataSourceArray();
        $odsArray->writeRow(array('a', 'b', 'c'));
        $odsArray->writeRow(array('a', 'b', 'c'));

        $this->assertCount(2, $odsArray);
    }

    public function testGetKeys()
    {
        $aData = array(
            array('a' => 1, 'b' => 2, 'c' => 3),
            array('a' => 4, 'b' => 5, 'c' => 6),
        );

        $odsArray = new DataSourceArray($aData);
        $aActualKeys = $odsArray->getKeys();
        $aExpectedKeys = array('a', 'b', 'c');

        $this->assertEquals($aExpectedKeys, $aActualKeys);
    }

    public function testGetFirstRow()
    {
        $aData = array(
            array('a' => 1, 'b' => 2, 'c' => 3),
            array('a' => 4, 'b' => 5, 'c' => 6),
        );

        $odsArray = new DataSourceArray($aData);

        $aActualData = $odsArray->getFirstRow();
        $aExpectedData = array('a' => 1, 'b' => 2, 'c' => 3);

        $this->assertEquals($aExpectedData, $aActualData);
    }

    public function testGetRows()
    {
        $aExpectedData = array(
            array('a' => 1, 'b' => 2, 'c' => 3),
            array('a' => 4, 'b' => 5, 'c' => 6),
        );

        $odsArray = new DataSourceArray($aExpectedData);

        $aActualData = $odsArray->getRows();

        $this->assertEquals($aExpectedData, $aActualData);
    }

    public function testGetValue()
    {
        $aData = array(
            array('a' => 1, 'b' => 2, 'c' => 3),
            array('a' => 4, 'b' => 5, 'c' => 6),
        );

        $odsArray = new DataSourceArray($aData);

        $sValue = $odsArray->getValue('a');

        $this->assertEquals(1, $sValue);

        $sValue = $odsArray->getValue('d');
        $this->assertEquals('', $sValue);
    }

    public function testGetUniqueValue()
    {
        $aData = array(array('a' => 1));

        $odsArray = new DataSourceArray($aData);
        $sActualValue = $odsArray->getUniqueValue();

        $sExpectedValue = 1;

        $this->assertEquals($sExpectedValue, $sActualValue);
    }

    public function testIterate()
    {
        $aExpectedData = array(
            array('a' => 1, 'b' => 2, 'c' => 3),
            array('a' => 4, 'b' => 5, 'c' => 6),
        );

        $odsArray = new DataSourceArray($aExpectedData);

        $aActualData = array();
        foreach ($odsArray as $aRow) {
            $aActualData[] = $aRow;
        }

        $this->assertEquals($aExpectedData, $aActualData);
    }

    public function testWriteFromDataSource()
    {
        $aData = array(
            array('a' => 1, 'b' => 2, 'c' => 3),
            array('a' => 4, 'b' => 5, 'c' => 6),
        );
        $odsArray = new DataSourceArray($aData);
        $aData2 = array(
            array('a' => 7, 'b' => 8, 'c' => 9),
            array('a' => 10, 'b' => 11, 'c' => 12),
        );
        $odsArray2 = new DataSourceArray($aData2);

        $odsArray->writeFromDataSource($odsArray2);

        $aActualData = $odsArray->getRows();

        $aExpectedData = array(
            array('a' => 1, 'b' => 2, 'c' => 3),
            array('a' => 4, 'b' => 5, 'c' => 6),
            array('a' => 7, 'b' => 8, 'c' => 9),
            array('a' => 10, 'b' => 11, 'c' => 12),
        );

        $this->assertEquals($aExpectedData, $aActualData);
    }

    public function testGetTplDisplayTable()
    {
        $oActualTpl = DataSource::getTplDisplayTable();
        $oExpectedTpl = new Template(__DIR__ . '/../../../../src/DspLib/DataSource/displayTable.html');

        $this->assertEquals($oExpectedTpl, $oActualTpl);
    }
}
