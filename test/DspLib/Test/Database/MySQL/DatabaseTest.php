<?php

namespace DspLib\Test\Database\MySQL;

use DspLib\Config;
use DspLib\Database\MySQL\Database;

class DatabaseTest extends \DspLib\Test\Database\DatabaseTestCase
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
    }
}
