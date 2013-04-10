<?php

namespace DspLib\Test\DataSource;

use DspLib\DataSource\DataSourceXML;

class DataSourceXMLTest extends \PHPUnit_Framework_TestCase
{
    private $aTempFiles = array();

    private $odsXml = null;

    private function getTempFile()
    {
        $sFilePath = tempnam('.', 'test.DataSourceXML.');
        $this->aTempFiles[] = $sFilePath;
        return $sFilePath;
    }

    public function setUp()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL);
        file_put_contents($sFilePath, '<test>'.PHP_EOL, FILE_APPEND);

        file_put_contents($sFilePath, '<row>'.PHP_EOL, FILE_APPEND);
        file_put_contents($sFilePath, '<a>1</a>'.PHP_EOL, FILE_APPEND);
        file_put_contents($sFilePath, '<b>2</b>'.PHP_EOL, FILE_APPEND);
        file_put_contents($sFilePath, '<c>3</c>'.PHP_EOL, FILE_APPEND);
        file_put_contents($sFilePath, '</row>'.PHP_EOL, FILE_APPEND);

        file_put_contents($sFilePath, '<row>'.PHP_EOL, FILE_APPEND);
        file_put_contents($sFilePath, '<a>4</a>'.PHP_EOL, FILE_APPEND);
        file_put_contents($sFilePath, '<b>5</b>'.PHP_EOL, FILE_APPEND);
        file_put_contents($sFilePath, '<c>6</c>'.PHP_EOL, FILE_APPEND);
        file_put_contents($sFilePath, '</row>'.PHP_EOL, FILE_APPEND);

        file_put_contents($sFilePath, '<row>'.PHP_EOL, FILE_APPEND);
        file_put_contents($sFilePath, '<a>7</a>'.PHP_EOL, FILE_APPEND);
        file_put_contents($sFilePath, '<b>8</b>'.PHP_EOL, FILE_APPEND);
        file_put_contents($sFilePath, '<c>9</c>'.PHP_EOL, FILE_APPEND);
        file_put_contents($sFilePath, '</row>'.PHP_EOL, FILE_APPEND);

        file_put_contents($sFilePath, '</test>'.PHP_EOL, FILE_APPEND);

        $this->odsXml = new DataSourceXML($sFilePath);
        $this->odsXml->setNode('row');

    }
    public function tearDown()
    {
        foreach ($this->aTempFiles as $sFilePath) {
            unlink($sFilePath);
        }
    }

    public function testCount()
    {
        $iActualCount = count($this->odsXml);
        $iExpectedCount = 3;

        $this->assertEquals($iExpectedCount, $iActualCount);
    }

    public function testGetKeys()
    {
        $aKeys = $this->odsXml->getKeys();
        $aExpectedKeys = array('a', 'b', 'c');

        $this->assertEquals($aExpectedKeys, $aKeys);
    }

    public function testGetFirstRow()
    {
        $aActualData = $this->odsXml->getFirstRow();
        $aExpectedData = array('a' => 1, 'b' => 2, 'c' => 3);

        $this->assertEquals($aExpectedData, $aActualData);
    }

    public function testGetRows()
    {
        $aActualData = $this->odsXml->getRows();
        $aExpectedData = array(
            array('a' => 1, 'b' => 2, 'c' => 3),
            array('a' => 4, 'b' => 5, 'c' => 6),
            array('a' => 7, 'b' => 8, 'c' => 9),
        );

        $this->assertEquals($aExpectedData, $aActualData);
    }

    public function testGetValue()
    {
        $sValue = $this->odsXml->getValue('a');

        $this->assertEquals('1', $sValue);

        $sValue = $this->odsXml->getValue('truc');
        $this->assertEquals('', $sValue);
    }

    public function testIterate()
    {
        $aActualData = array();
        foreach ($this->odsXml as $iKey => $aRow) {
            $aActualData[] = $aRow;
        }

        $aExpectedData = array(
            array('a' => 1, 'b' => 2, 'c' => 3),
            array('a' => 4, 'b' => 5, 'c' => 6),
            array('a' => 7, 'b' => 8, 'c' => 9),
        );

        $this->assertEquals($aExpectedData, $aActualData);
    }

    public function testWriteRow()
    {
        $this->setExpectedException('\Exception');
        $this->odsXml->writeRow(array('a' => 7, 'b' => 8, 'c' => 9));
    }
}
