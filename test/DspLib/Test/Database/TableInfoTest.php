<?php

/**
 * TableInfo test class
 *
 * PHP Version 5.3
 *
 * @category Database
 * @package  Test
 * @author   Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @license  http://www.deuspi.org Proprietary
 * @link     https://github.com/pfeyssaguet/dsplib
 * @since    5 avril 2013
 */

namespace DspLib\Test\Database\MySQL;

use DspLib\Config;
use DspLib\Database\MySQL\Database;
use DspLib\Database\TableInfo;
use DspLib\Test\Database\DatabaseTestCase;
use DspLib\Database\FieldInfo;

/**
 * TableInfo test class
 *
 * @category Database
 * @package  Test
 * @author   Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @license  http://www.deuspi.org Proprietary
 * @link     https://github.com/pfeyssaguet/dsplib
 */
class TableInfoTest extends DatabaseTestCase
{
	/**
     * Performs operation returned by getSetUpOperation().
	 *
	 * @see PHPUnit_Extensions_Database_TestCase::setUp()
	 * @return void
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

        $oDb = Database::getInstance();
        $oDb->query("CREATE TABLE test_table (
            idtest_table INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(32) NOT NULL DEFAULT '',
            test_null VARCHAR(50) NULL,
            key1 VARCHAR(12) NOT NULL,
            Key2 VARCHAR(12) NOT NULL,
            PRIMARY KEY(idtest_table),
            UNIQUE KEY key1_key2 (key1, key2)
        ) COMMENT='Test comment'");
    }

    public function tearDown()
    {
        $oDb = Database::getInstance();
        $oDb->query("DROP TABLE test_table");
    }

    /**
     * Table creation test
     *
     * @return void
     */
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

        var_dump($aExpectedKeys);
        var_dump($aActualKeys);
        $this->assertEquals($aExpectedKeys, $aActualKeys);
    }
}
