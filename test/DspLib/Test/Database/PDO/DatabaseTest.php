<?php

namespace DspLib\Test\Database\PDO;

use DspLib\Config;
use DspLib\Database\PDO\Database;
use DspLib\Test\Database\DatabaseTestCase;

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
}
