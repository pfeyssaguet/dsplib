<?php

/**
 * DataSourceSQL test class
 *
 * @package    Test
 * @subpackage DataSource
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 */

namespace DspLib\Test\DataSource;

use DspLib\Config;
use DspLib\DataSource\DataSourceSQL;
use DspLib\DataSource\DataSourceFilter;
use DspLib\Test\Database\DatabaseTestCase;

/**
 * DataSourceSQL test class
 *
 * @package    Test
 * @subpackage DataSource
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 */
class DataSourceSQLTest extends DatabaseTestCase
{
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

    public function testCount()
    {
        $odsSql = new DataSourceSQL("SELECT * FROM table1 LIMIT 2");
        $this->assertCount(2, $odsSql);
        $this->assertEquals(3, $odsSql->getTotalRowCount());
    }

    public function testWriteRow()
    {
        $odsSql = new DataSourceSQL("SELECT * FROM table1 LIMIT 2");
        $this->setExpectedException('\Exception');
        $odsSql->writeRow(array('d', '4'));
    }

    public function testGetKeys()
    {
        $odsSql = new DataSourceSQL("SELECT * FROM table1 LIMIT 2");
        $aKeys = $odsSql->getKeys();
        $aExpectedKeys = array('idtable1', 'name', 'value');

        $this->assertEquals($aExpectedKeys, $aKeys);
    }

    public function testGetFirstRow()
    {
        $odsSql = new DataSourceSQL("SELECT * FROM table1 LIMIT 2");

        $aActualData = $odsSql->getFirstRow();
        $aExpectedData = array('idtable1' => 1, 'name' => 'a', 'value' => '1');

        $this->assertEquals($aExpectedData, $aActualData);
    }

    public function testGetRows()
    {
        $odsSql = new DataSourceSQL("SELECT * FROM table1 LIMIT 2");

        $aActualData = $odsSql->getRows();
        $aExpectedData = array(
            array('idtable1' => 1, 'name' => 'a', 'value' => '1'),
            array('idtable1' => 2, 'name' => 'b', 'value' => '2'),
        );

        $this->assertEquals($aExpectedData, $aActualData);
    }

    public function testGetValue()
    {
        $odsSql = new DataSourceSQL("SELECT * FROM table1 LIMIT 2");

        $sValue = $odsSql->getValue('name');

        $this->assertEquals('a', $sValue);

        $sValue = $odsSql->getValue('truc');
        $this->assertEquals('', $sValue);
    }

    public function testIterate()
    {
        $odsSql = new DataSourceSQL("SELECT * FROM table1 LIMIT 2");

        $aActualData = array();
        foreach ($odsSql as $aRow) {
            $aActualData[] = $aRow;
        }

        $aExpectedData = array(
            array('idtable1' => 1, 'name' => 'a', 'value' => '1'),
            array('idtable1' => 2, 'name' => 'b', 'value' => '2'),
        );

        $this->assertEquals($aExpectedData, $aActualData);
    }

    public function testFilter()
    {
        $odsSql = new DataSourceSQL("SELECT * FROM table1 LIMIT 2");

        $odsFilter = new DataSourceFilter();
        $odsFilter->addFilter('name', DataSourceFilter::SIGN_EQUALS, 'a');

        $odsSql->setFilter($odsFilter);

        $aActualData = $odsSql->getRows();

        $aExpectedData = array(
            array('idtable1' => 1, 'name' => 'a', 'value' => '1'),
        );

        $this->assertEquals($aExpectedData, $aActualData);
    }
}
