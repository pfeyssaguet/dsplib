<?php

namespace DspLib\Test\Database\PDO;

use DspLib\Config;
use DspLib\Database\PDO\Database;
use DspLib\Test\Database\DatabaseTestCase;
use DspLib\DataSource\DataSourceTable;

class DatabaseTest extends DatabaseTestCase
{
    public function setUp()
    {
        parent::setUp();
        $oConfig = Config::getInstance();
        $oConfig->setParam(
            'database_pdo',
            array(
                'driver' => 'PDO',
                'host' => $GLOBALS['db_host'],
                'login' => $GLOBALS['db_login'],
                'password' => $GLOBALS['db_password'],
                'dbname' => $GLOBALS['db_name'],
            )
        );
    }

    public function testConnection()
    {
        $oDb = Database::getInstance('database_pdo');

        $oResult = $oDb->query('SELECT DATABASE()');
        $oResult->rewind();
        $aRow = $oResult->current();

        $sActualSchema = $aRow['DATABASE()'];
        $sExpectedSchema = $GLOBALS['db_name'];

        $this->assertEquals($sExpectedSchema, $sActualSchema);
    }

    public function testCommitTransaction()
    {
        $oDb = Database::getInstance('database_pdo');
        $oDb->beginTransaction();
        $oDb->query("DELETE FROM table1_innodb");

        $oDb->commitTransaction();

        $odsTable = new DataSourceTable('table1_innodb', $oDb);
        $this->assertEquals(0, count($odsTable));
    }

    public function testRollbackTransaction()
    {
        $oDb = Database::getInstance('database_pdo');
        $oDb->beginTransaction();
        $oDb->query("DELETE FROM table1_innodb");

        $oDb->rollbackTransaction();

        $odsTable = new DataSourceTable('table1_innodb', $oDb);
        $this->assertEquals(3, count($odsTable));
    }

    public function testGetLastInsertId()
    {
        $oDb = Database::getInstance('database_pdo');
        $oDb->query("INSERT INTO table1 (name, value) VALUES ('d', 4)");
        $iActualLastId = $oDb->getLastInsertId();
        $this->assertEquals(4, $iActualLastId);
    }
}
