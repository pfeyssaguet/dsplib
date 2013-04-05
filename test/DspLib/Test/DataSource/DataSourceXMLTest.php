<?php

namespace DspLib\Test\DataSource;

use DspLib\DataSource\DataSourceXML;

class DataSourceXMLTest extends \PHPUnit_Framework_TestCase
{
    private $aTempFiles = array();

    private function getTempFile()
    {
        $sFilePath = tempnam('.', 'test.DataSourceXML.');
        $this->aTempFiles[] = $sFilePath;
        return $sFilePath;
    }

    public function tearDown()
    {
        foreach ($this->aTempFiles as $sFilePath) {
            unlink($sFilePath);
        }
    }

    public function testTruc()
    {
        $sFilePath = $this->getTempFile();
        file_put_contents($sFilePath, '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL);
        file_put_contents($sFilePath, '<test>'.PHP_EOL, FILE_APPEND);
        file_put_contents($sFilePath, '<row>'.PHP_EOL, FILE_APPEND);
        file_put_contents($sFilePath, '<name>a</name>'.PHP_EOL, FILE_APPEND);
        file_put_contents($sFilePath, '<value>1</value>'.PHP_EOL, FILE_APPEND);
        file_put_contents($sFilePath, '</row>'.PHP_EOL, FILE_APPEND);
        file_put_contents($sFilePath, '<row>'.PHP_EOL, FILE_APPEND);
        file_put_contents($sFilePath, '<name>b</name>'.PHP_EOL, FILE_APPEND);
        file_put_contents($sFilePath, '<value>2</value>'.PHP_EOL, FILE_APPEND);
        file_put_contents($sFilePath, '</row>'.PHP_EOL, FILE_APPEND);
        file_put_contents($sFilePath, '</test>'.PHP_EOL, FILE_APPEND);

        $odsXml = new DataSourceXML($sFilePath);
    }
}
