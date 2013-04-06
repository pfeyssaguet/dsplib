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
    }

    /**
     * Table creation test
     *
     * @return void
     */
    public function testCreateTable()
    {
        $oDb = Database::getInstance();
    	$oTableInfo = new TableInfo('test_table');
    	$oFieldInfo = new FieldInfo('test_field', 'varchar(32)');
    	$oTableInfo->addField($oFieldInfo);
    	$oTableInfo->createTable($oDb);

    	$oDb->query("DROP TABLE test_table");

    	// TODO assert something...
    }
}
