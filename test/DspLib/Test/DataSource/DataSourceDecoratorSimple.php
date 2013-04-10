<?php

/**
 * DataSourceDecoratorSimple class
 *
 * @package    Test
 * @subpackage DataSource
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 */

namespace DspLib\Test\DataSource;

use DspLib\DataSource\DataSourceDecorator;


/**
 * DataSourceDecoratorSimple class
 *
 * @package    Test
 * @subpackage DataSource
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 */
class DataSourceDecoratorSimple extends DataSourceDecorator
{
    public function decorate(array $aRow)
    {
        $aNewRow = array();
        foreach ($aRow as $sKey => $sValue) {
            $aNewRow[$sKey] = "_" . $sValue . "_";
        }
        return $aNewRow;
    }

    public function decorateKeys(array $aRow)
    {
        $aNewRow = array();
        foreach ($aRow as $sKey => $sValue) {
            $aNewRow[$sKey] = "_" . $sValue . "_";
        }
        return $aNewRow;
    }
}
