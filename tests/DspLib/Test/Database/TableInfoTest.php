<?php

/**
 * TableInfo test class
 *
 * @package    Test
 * @subpackage Database
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since      5 avril 2013
 */

namespace DspLib\Test\Database;

use DspLib\Config;
use DspLib\Database\TableInfo;
use DspLib\Database\FieldInfo;
use DspLib\Database\Database;

/**
 * TableInfo test class
 *
 * @package    Test
 * @subpackage Database
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since      5 avril 2013
 */

class TableInfoTest extends DatabaseTestCase
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
        $oDb->query(
            "CREATE TABLE IF NOT EXISTS test_table (
                idtest_table INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                name VARCHAR(32) NOT NULL DEFAULT '' COMMENT 'test name',
                test_null VARCHAR(50) COMMENT 'test null',
                key1 VARCHAR(12) NOT NULL,
                key2 VARCHAR(12) NOT NULL,
                PRIMARY KEY (idtest_table),
                UNIQUE KEY key1_key2 (key1, key2)
            ) COMMENT 'Test comment'"
        );
    }

    public function tearDown()
    {
        $oDb = Database::getInstance();
        $oDb->query("DROP TABLE test_table");
    }

    public function testCreateTable()
    {
        $oDb = Database::getInstance();
        $oTableInfo = new TableInfo('test_table2');
        $oFieldInfo = new FieldInfo('test_field', 'varchar(32)');
        $oTableInfo->addField($oFieldInfo);
        $oTableInfo->setComment("Test comment");
        $oTableInfo->createTable($oDb);

        $oResult = $oDb->query("SHOW TABLE STATUS LIKE 'test_table2'");
        $oResult->rewind();
        $aRow = $oResult->current();
        $this->assertEquals('Test comment', $aRow['Comment']);

        $oResult = $oDb->query("SHOW FULL COLUMNS FROM test_table2");
        $oResult->rewind();
        $aRow = $oResult->current();
        $this->assertEquals('test_field', $aRow['Field']);
        $this->assertEquals('varchar(32)', $aRow['Type']);

        $oDb->query("DROP TABLE test_table2");
    }

    public function testCreateTableBis()
    {
        $oDb = Database::getInstance();
        $oTableInfo = new TableInfo('test_table2');
        $oTableInfo->setComment('Test comment');

        $oFieldInfo = new FieldInfo('idtest_table', 'INT(11) UNSIGNED', false, null, 'AUTO_INCREMENT');
        $oTableInfo->addField($oFieldInfo);
        $oTableInfo->addPrimaryKey('idtest_table');

        $oFieldInfo = new FieldInfo('name', 'VARCHAR(32)');
        $oFieldInfo->setComment('test name');
        $oFieldInfo->setDefault("''");
        $oTableInfo->addField($oFieldInfo);

        $oFieldInfo = new FieldInfo('test_null', 'VARCHAR(50)', true);
        $oFieldInfo->setComment('test null');
        $oTableInfo->addField($oFieldInfo);

        $oFieldInfo = new FieldInfo('key1', 'VARCHAR(12)');
        $oTableInfo->addField($oFieldInfo);

        $oFieldInfo = new FieldInfo('key2', 'VARCHAR(12)');
        $oTableInfo->addField($oFieldInfo);

        $oTableInfo->addKey('key1_key2', array('key1', 'key2'), true);

        $sActualCreateTableScript = $oTableInfo->generateCreate();
        $sExpectedCreateTableScript = "CREATE TABLE IF NOT EXISTS `test_table2` (
`idtest_table` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`name` VARCHAR(32) NOT NULL DEFAULT '' COMMENT 'test name',
`test_null` VARCHAR(50) COMMENT 'test null',
`key1` VARCHAR(12) NOT NULL,
`key2` VARCHAR(12) NOT NULL,
PRIMARY KEY (`idtest_table`),
UNIQUE KEY `key1_key2` (`key1`, `key2`)
) COMMENT 'Test comment'";

        $this->assertEquals($sExpectedCreateTableScript, $sActualCreateTableScript);

        $oTableInfo->createTable($oDb);

        $oDb->query("DROP TABLE test_table2");
    }

    public function testGetComment()
    {
        $oDb = Database::getInstance();
        $oTableInfo = TableInfo::getTableInfoFromDb($oDb, 'test_table');
        $sActualComment = $oTableInfo->getComment();
        $sExpectedComment = "Test comment";
        $this->assertEquals($sExpectedComment, $sActualComment);
    }

    public function testGetPrimaryKeys()
    {
        $oDb = Database::getInstance();
        $oTableInfo = TableInfo::getTableInfoFromDb($oDb, 'test_table');
        $aActualKeys = $oTableInfo->getPrimaryKeys();

        $aExpectedKeys = array(
            'idtest_table',
        );

        $this->assertEquals($aExpectedKeys, $aActualKeys);
    }

    public function testGetUniqueKeys()
    {
        $oDb = Database::getInstance();
        $oTableInfo = TableInfo::getTableInfoFromDb($oDb, 'test_table');
        $aActualKeys = $oTableInfo->getUniqueKeys();

        $aExpectedKeys = array(
            'key1_key2' => array('key1', 'key2'),
        );

        $this->assertEquals($aExpectedKeys, $aActualKeys);
    }
}
