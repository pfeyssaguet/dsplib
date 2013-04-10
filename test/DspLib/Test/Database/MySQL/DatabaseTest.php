<?php

/**
 * Database test class for MySQL
 *
 * @package    Test
 * @subpackage Database
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 */

namespace DspLib\Test\Database\MySQL;

use DspLib\Config;
use DspLib\Database\MySQL\Database;
use DspLib\Test\Database\DatabaseTestCase;
use DspLib\DataSource\DataSourceTable;

/**
 * Database test class for MySQL
 *
 * @package    Test
 * @subpackage Database
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 */
class DatabaseTest extends DatabaseTestCase
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

    public function testConnection()
    {
        $oDb = Database::getInstance();

        $oResult = $oDb->query('SELECT DATABASE()');
        $oResult->rewind();
        $aRow = $oResult->current();

        $sActualSchema = $aRow['DATABASE()'];
        $sExpectedSchema = $GLOBALS['db_name'];

        $this->assertEquals($sExpectedSchema, $sActualSchema);
    }

    public function testCommitTransaction()
    {
        $oDb = Database::getInstance();
        $oDb->beginTransaction();
        $oDb->query("DELETE FROM table1_innodb");

        $oDb->commitTransaction();

        $odsTable = new DataSourceTable('table1_innodb');
        $this->assertEquals(0, count($odsTable));
    }

    public function testRollbackTransaction()
    {
        $oDb = Database::getInstance();
        $oDb->beginTransaction();
        $oDb->query("DELETE FROM table1_innodb");

        $oDb->rollbackTransaction();

        $odsTable = new DataSourceTable('table1_innodb');
        $this->assertEquals(3, count($odsTable));
    }

    public function testGetLastInsertId()
    {
        $oDb = Database::getInstance();
        $oDb->query("INSERT INTO table1 (name, value) VALUES ('d', 4)");
        $iActualLastId = $oDb->getLastInsertId();
        $this->assertEquals(4, $iActualLastId);
    }

    public function testError()
    {
        $oDb = Database::getInstance();

        $this->setExpectedException('\Exception');
        $oDb->query("ZZZZZZZ");

    }

    public function providerEscapeString()
    {
        return array(
            array('test', '\'test\''),
            array('test\'quote', '\'test\\\'quote\''),
        );
    }

    /**
     * @dataProvider providerEscapeString
     */
    public function testEscapeString($sTestString, $sExpected)
    {
        $oDb = Database::getInstance();
        $sActual = $oDb->escapeString($sTestString);
        $this->assertEquals($sExpected, $sActual);
    }
}
