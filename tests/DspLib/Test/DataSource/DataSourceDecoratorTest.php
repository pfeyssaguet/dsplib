<?php

/**
 * DataSourceDecorator test class
 *
 * @package    Test
 * @subpackage DataSource
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 */

namespace DspLib\Test\DataSource;

use DspLib\DataSource\DataSourceArray;

/**
 * DataSourceDecorator test class
 *
 * @package    Test
 * @subpackage DataSource
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 */
class DataSourceDecoratorTest extends \PHPUnit_Framework_TestCase
{
    public function testDecorateNothing()
    {
        $aData = array(
            array('a' => 1, 'b' => 2, 'c' => 3),
            array('a' => 4, 'b' => 5, 'c' => 6),
        );

        $odsArray = new DataSourceArray($aData);

        $oDecorator = new DataSourceDecoratorNothing();

        $odsArray->setDecorator($oDecorator);

        $aNewData = array();
        foreach ($odsArray as $sKey => $aRow) {
            $aNewData[$sKey] = $aRow;
        }

        $this->assertEquals($aData, $aNewData);
    }

    public function testDecorateKeysNothing()
    {
        $aData = array(
            array('a' => 1, 'b' => 2, 'c' => 3),
        );

        $odsArray = new DataSourceArray($aData);

        $oDecorator = new DataSourceDecoratorNothing();

        $odsArray->setDecorator($oDecorator);

        $aKeys = $odsArray->getKeys();
        $aExpectedKeys = array('a', 'b', 'c');

        $this->assertEquals($aExpectedKeys, $aKeys);
    }

    public function testDecorate()
    {
        $aData = array(
            array('a' => 1, 'b' => 2, 'c' => 3),
            array('a' => 4, 'b' => 5, 'c' => 6),
        );

        $odsArray = new DataSourceArray($aData);

        $oDecorator = new DataSourceDecoratorSimple();

        $odsArray->setDecorator($oDecorator);

        $aNewData = array();
        foreach ($odsArray as $sKey => $aRow) {
            $aNewData[$sKey] = $aRow;
        }

        $aExpectedData = array(
            array('a' => '_1_', 'b' => '_2_', 'c' => '_3_'),
            array('a' => '_4_', 'b' => '_5_', 'c' => '_6_'),
        );

        $this->assertEquals($aExpectedData, $aNewData);
    }

    public function testDecorateKeys()
    {
        $aData = array(
            array('a' => 1, 'b' => 2, 'c' => 3),
        );

        $odsArray = new DataSourceArray($aData);

        $oDecorator = new DataSourceDecoratorSimple();

        $odsArray->setDecorator($oDecorator);

        $aKeys = $odsArray->getKeys();
        $aExpectedKeys = array('_a_', '_b_', '_c_');

        $this->assertEquals($aExpectedKeys, $aKeys);
    }
}
