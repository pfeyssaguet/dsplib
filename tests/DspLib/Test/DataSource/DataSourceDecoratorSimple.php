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
    /**
     * (non-PHPdoc)
     * @see \DspLib\DataSource\DataSourceDecorator::decorate()
     */
    public function decorate(array $aRow)
    {
        $aNewRow = array();
        foreach ($aRow as $sKey => $sValue) {
            $aNewRow[$sKey] = "_" . $sValue . "_";
        }
        return $aNewRow;
    }

    /**
     * (non-PHPdoc)
     * @see \DspLib\DataSource\DataSourceDecorator::decorateKeys()
     */
    public function decorateKeys(array $aRow)
    {
        $aNewRow = array();
        foreach ($aRow as $sKey => $sValue) {
            $aNewRow[$sKey] = "_" . $sValue . "_";
        }
        return $aNewRow;
    }
}
