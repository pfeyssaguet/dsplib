<?php

namespace DspLib\Test\Database;

use DspLib\Database\DatabaseInfo;
use DspLib\Database\Database;

/**
 * DatabaseInfo test class
 *
 * @author Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since  10 avr. 2013 10:40:15
 */

class DatabaseInfoTest extends DatabaseTestCase
{
    public function testGetTable()
    {
        $oDb = Database::getInstance();
        $oDbInfo = DatabaseInfo::getFromDb($oDb);

        $oTableInfo = $oDbInfo->getTable('table1');

        $this->assertEquals('table1', $oTableInfo->getName());
    }

    public function testHasTable()
    {
        $oDb = Database::getInstance();
        $oDbInfo = DatabaseInfo::getFromDb($oDb);

        $this->assertTrue($oDbInfo->hasTable('table1'));
        $this->assertFalse($oDbInfo->hasTable('unexistingTable'));
    }

    public function testGetTables()
    {
        $oDb = Database::getInstance();
        $oDbInfo = DatabaseInfo::getFromDb($oDb);

        $aActualTables = $oDbInfo->getTables();

        $oTableInfo = $oDbInfo->getTable('table1');
        $oTableInfo2 = $oDbInfo->getTable('table1_innodb');

        $aExpectedTables = array($oTableInfo, $oTableInfo2);

        $this->assertEquals($aExpectedTables, $aActualTables);
    }

    public function testSaveXml()
    {
        $oDb = Database::getInstance();
        $oDbInfo = DatabaseInfo::getFromDb($oDb);
        $oDbInfo->saveXML(__DIR__ . '/test.xml');

        $this->assertFileEquals(__DIR__ . '/table1.xml', __DIR__ . '/test.xml');

        unlink(__DIR__ . '/test.xml');
    }

    public function testLoadXml()
    {
        $oDb = Database::getInstance();
        $oDbInfo = DatabaseInfo::loadXML(__DIR__ . '/table1.xml');

        $oDbInfo2 = DatabaseInfo::getFromDb($oDb);

        // FIXME doesn't work cause no Db when loadXml + pb with keys
        //$this->assertEquals($oDbInfo2, $oDbInfo);
    }
}
