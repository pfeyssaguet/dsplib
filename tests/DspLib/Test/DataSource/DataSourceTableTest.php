<?php

/**
 * DataSourceTable test class
 *
 * @package    Test
 * @subpackage DataSource
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 */

namespace DspLib\Test\DataSource;

use DspLib\Config;
use DspLib\DataSource\DataSourceTable;
use DspLib\DataSource\DataSourceArray;
use DspLib\Test\Database\DatabaseTestCase;

/**
 * DataSourceTable test class
 *
 * @package    Test
 * @subpackage DataSource
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 */
class DataSourceTableTest extends DatabaseTestCase
{
    /**
     * (non-PHPdoc)
     * @see PHPUnit_Extensions_Database_TestCase::setUp()
     */
    public function setUp()
    {
        parent::setUp();
        $oConfig = Config::getInstance();
        $oConfig->setParam(
            'database',
            array(
                'driver' => 'MySQL',
                'host' => $GLOBALS['db_host'],
                'login' => $GLOBALS['db_login'],
                'password' => $GLOBALS['db_password'],
                'dbname' => $GLOBALS['db_name'],
            )
        );
    }

    public function testConstructWithNotExistingTable()
    {
        $this->setExpectedException('\InvalidArgumentException');
        new DataSourceTable('not_existing_table');
    }

    public function testCount()
    {
        $odsTable = new DataSourceTable('table1');

        $this->assertCount(3, $odsTable);
        $this->assertEquals(3, $odsTable->getTotalRowCount());
    }

    public function testWriteRow()
    {
        $odsTable = new DataSourceTable('table1');
        $odsTable->writeRow(array('name' => 'd', 'value' => '4'));
        $odsTable->writeRow(array('name' => 'e', 'value' => '5'));

        $this->assertCount(5, $odsTable);
    }

    public function testGetKeys()
    {
        $odsTable = new DataSourceTable('table1');
        $aActualKeys = $odsTable->getKeys();
        $aExpectedKeys = array('idtable1', 'name', 'value');

        $this->assertEquals($aExpectedKeys, $aActualKeys);
    }

    public function testGetFirstRow()
    {
        $odsTable = new DataSourceTable('table1');

        $aActualData = $odsTable->getFirstRow();
        $aExpectedData = array('idtable1' => 1, 'name' => 'a', 'value' => 1);

        $this->assertEquals($aExpectedData, $aActualData);
    }

    public function testGetRows()
    {
        $odsTable = new DataSourceTable('table1');

        $aActualData = $odsTable->getRows();
        $aExpectedData = array(
            array('idtable1' => 1, 'name' => 'a', 'value' => 1),
            array('idtable1' => 2, 'name' => 'b', 'value' => 2),
            array('idtable1' => 3, 'name' => 'c', 'value' => 3),
        );

        $this->assertEquals($aExpectedData, $aActualData);
    }

    public function testGetValue()
    {
        $odsTable = new DataSourceTable('table1');

        $sValue = $odsTable->getValue('name');

        $this->assertEquals('a', $sValue);

        $sValue = $odsTable->getValue('truc');
        $this->assertEquals('', $sValue);
    }

    public function testIterate()
    {
        $odsTable = new DataSourceTable('table1');

        $aActualData = array();
        foreach ($odsTable as $aRow) {
            $aActualData[] = $aRow;
        }

        $aExpectedData = array(
            array('idtable1' => 1, 'name' => 'a', 'value' => 1),
            array('idtable1' => 2, 'name' => 'b', 'value' => 2),
            array('idtable1' => 3, 'name' => 'c', 'value' => 3),
        );

        $this->assertEquals($aExpectedData, $aActualData);
    }

    public function testWriteFromDataSource()
    {
        $odsTable = new DataSourceTable('table1');
        $aData = array(
            array('name' => 'd', 'value' => 4),
            array('name' => 'e', 'value' => 5),
        );
        $odsArray = new DataSourceArray($aData);

        $odsTable->writeFromDataSource($odsArray);

        $aActualData = $odsTable->getRows();

        $aExpectedData = array(
            array('idtable1' => 1, 'name' => 'a', 'value' => 1),
            array('idtable1' => 2, 'name' => 'b', 'value' => 2),
            array('idtable1' => 3, 'name' => 'c', 'value' => 3),
            array('idtable1' => 4, 'name' => 'd', 'value' => 4),
            array('idtable1' => 5, 'name' => 'e', 'value' => 5),
        );

        $this->assertEquals($aExpectedData, $aActualData);
    }

    public function testSetWhere()
    {
        $odsTable = new DataSourceTable('table1');
        $odsTable->setWhere("name = 'a'");

        $aActualData = $odsTable->getRows();

        $aExpectedData = array(
            array('idtable1' => 1, 'name' => 'a', 'value' => 1),
        );

        $this->assertEquals($aExpectedData, $aActualData);
    }
}
