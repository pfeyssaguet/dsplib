<?php

/**
 * FieldInfo test class
 *
 * @author Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since  10 avr. 2013 08:23:28
 */

namespace DspLib\Test\Database;

use DspLib\Config;
use DspLib\Database\TableInfo;
use DspLib\Database\FieldInfo;
use DspLib\Database\Database;

class FieldInfoTest extends DatabaseTestCase
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

        $oDb = Database::getInstance();
        $oDb->query("CREATE TABLE test_table (
            idtest_table INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(32) NOT NULL DEFAULT '' COMMENT 'test name',
            test_null VARCHAR(50) COMMENT 'test null',
            key1 VARCHAR(12) NOT NULL,
            key2 VARCHAR(12) NOT NULL,
            PRIMARY KEY (idtest_table),
            UNIQUE KEY key1_key2 (key1, key2)
        ) COMMENT 'Test comment'");
    }

    public function tearDown()
    {
        $oDb = Database::getInstance();
        $oDb->query("DROP TABLE test_table");
    }

    public function testGetComment()
    {
        $oDb = Database::getInstance();
        $oTableInfo = TableInfo::getTableInfoFromDb($oDb, 'test_table');
        $aFields = $oTableInfo->getFields();
        /** @var $oField FieldInfo */
        foreach ($aFields as $oField) {
            $sName = $oField->getName();
            if ($sName == 'name') {
                $this->assertEquals('test name', $oField->getComment());
                $this->assertEquals('varchar(32)', $oField->getType());
            } elseif ($sName == 'idtest_table') {
                $this->assertEquals('auto_increment', $oField->getExtra());
                $this->assertEquals('int(11) unsigned', $oField->getType());
            } elseif ($sName == 'test_null') {
                $this->assertTrue($oField->isNullable());
                $this->assertEquals('varchar(50)', $oField->getType());
            }
        }
    }
}
