<?php

namespace DspLib\Test\DataSource;

use DspLib\DataSource\DataSourceCSV;
use DspLib\DataSource\DataSourceArray;

class DataSourceCSVTest extends \PHPUnit_Framework_TestCase
{
    private $aTempFiles = array();

    private function getTempFile()
    {
        $sFilePath = tempnam('.', 'test.DataSourceCSV.');
        $this->aTempFiles[] = $sFilePath;
        return $sFilePath;
    }

    public function tearDown()
    {
        foreach ($this->aTempFiles as $sFilePath) {
            unlink($sFilePath);
        }
    }

    public function testCount()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, 'a;b;c' . "\n");

        $odsCSV = new DataSourceCSV($sFilePath);

        $this->assertCount(0, $odsCSV);
        $this->assertEquals(0, $odsCSV->getTotalRowCount());

        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, 'a;b;c' . "\n");
        file_put_contents($sFilePath, '1;2;3' . "\n", FILE_APPEND);
        file_put_contents($sFilePath, '4;5;6' . "\n", FILE_APPEND);

        $odsCSV = new DataSourceCSV($sFilePath);
        $this->assertCount(2, $odsCSV);
        $this->assertEquals(2, $odsCSV->getTotalRowCount());

        // On essaie encore mais sans le retour chariot final
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, 'a;b;c' . "\n");
        file_put_contents($sFilePath, '1;2;3' . "\n", FILE_APPEND);
        file_put_contents($sFilePath, '4;5;6', FILE_APPEND);

        $odsCSV = new DataSourceCSV($sFilePath);
        $this->assertCount(2, $odsCSV);
        $this->assertEquals(2, $odsCSV->getTotalRowCount());
    }

    public function testWriteRow()
    {
        $sFilePath = $this->getTempFile();
        $odsCSV = new DataSourceCSV($sFilePath);
        $odsCSV->writeRow(array('a' => 1, 'b' => 2, 'c' => 3));
        $odsCSV->writeRow(array('a' => 4, 'b' => 5, 'c' => 6));
        $odsCSV->writeRow(array('a' => 7, 'b' => 8, 'c' => 9));

        $this->assertCount(3, $odsCSV);

        // Essai de write puis read
        $sFilePath = $this->getTempFile();
        $odsCSV = new DataSourceCSV($sFilePath);
        $odsCSV->writeRow(array('a' => 1, 'b' => 2, 'c' => 3));
        $odsCSV->writeRow(array('a' => 4, 'b' => 5, 'c' => 6));
        $odsCSV->writeRow(array('a' => 7, 'b' => 8, 'c' => 9));

        $aExpectedData = array(
            array('a' => 1, 'b' => 2, 'c' => 3),
            array('a' => 4, 'b' => 5, 'c' => 6),
            array('a' => 7, 'b' => 8, 'c' => 9),
        );

        $aActualData = $odsCSV->getRows();

        $this->assertEquals($aExpectedData, $aActualData);
    }

    public function testGetKeys()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, 'a;b;c' . "\n");
        file_put_contents($sFilePath, '1;2;3' . "\n", FILE_APPEND);
        file_put_contents($sFilePath, '4;5;6' . "\n", FILE_APPEND);

        $odsCSV = new DataSourceCSV($sFilePath);
        $aKeys = $odsCSV->getKeys();
        $aExpectedKeys = array('a', 'b', 'c');

        $this->assertEquals($aExpectedKeys, $aKeys);
    }

    public function testGetFirstRow()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, 'a;b;c' . "\n");
        file_put_contents($sFilePath, '1;2;3' . "\n", FILE_APPEND);
        file_put_contents($sFilePath, '4;5;6' . "\n", FILE_APPEND);

        $odsCSV = new DataSourceCSV($sFilePath);

        $aNewData = $odsCSV->getFirstRow();
        $aExpectedData = array('a' => 1, 'b' => 2, 'c' => 3);

        $this->assertEquals($aExpectedData, $aNewData);
    }

    public function testGetRows()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, 'a;b;c' . "\n");
        file_put_contents($sFilePath, '1;2;3' . "\n", FILE_APPEND);
        file_put_contents($sFilePath, '4;5;6' . "\n", FILE_APPEND);

        $odsCSV = new DataSourceCSV($sFilePath);

        $aActualData = $odsCSV->getRows();

        $aExpectedData = array(
            array('a' => 1, 'b' => 2, 'c' => 3),
            array('a' => 4, 'b' => 5, 'c' => 6),
        );

        $this->assertEquals($aExpectedData, $aActualData);
    }

    public function testGetValue()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, 'a;b;c' . "\n");
        file_put_contents($sFilePath, '1;2;3' . "\n", FILE_APPEND);
        file_put_contents($sFilePath, '4;5;6' . "\n", FILE_APPEND);

        $odsCSV = new DataSourceCSV($sFilePath);

        $iValue = $odsCSV->getValue('a');

        $this->assertEquals(1, $iValue);

        $sValue = $odsCSV->getValue('d');
        $this->assertEquals('', $sValue);
    }

    public function testIterate()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, 'a;b;c' . "\n");
        file_put_contents($sFilePath, '1;2;3' . "\n", FILE_APPEND);
        file_put_contents($sFilePath, '4;5;6' . "\n", FILE_APPEND);

        $aExpectedData = array(
            array('a' => 1, 'b' => 2, 'c' => 3),
            array('a' => 4, 'b' => 5, 'c' => 6),
        );

        $odsCSV = new DataSourceCSV($sFilePath);

        $aActualData = array();
        foreach ($odsCSV as $iKey => $aRow) {
            $aActualData[] = $aRow;
        }

        $this->assertEquals($aExpectedData, $aActualData);
    }

    public function testWriteFromDataSource()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, 'a;b;c' . "\n");
        file_put_contents($sFilePath, '1;2;3' . "\n", FILE_APPEND);
        file_put_contents($sFilePath, '4;5;6' . "\n", FILE_APPEND);

        $odsCSV = new DataSourceCSV($sFilePath);

        $aData2 = array(
            array('a' => 7, 'b' => 8, 'c' => 9),
            array('a' => 10, 'b' => 11, 'c' => 12),
        );
        $odsArray = new DataSourceArray($aData2);

        $odsCSV->writeFromDataSource($odsArray);

        $aActualData = $odsCSV->getRows();

        $aExpectedData = array(
            array('a' => 1, 'b' => 2, 'c' => 3),
            array('a' => 4, 'b' => 5, 'c' => 6),
            array('a' => 7, 'b' => 8, 'c' => 9),
            array('a' => 10, 'b' => 11, 'c' => 12),
        );

        $this->assertEquals($aExpectedData, $aActualData);
    }
}
