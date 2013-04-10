<?php

/**
 * Decorator of the DataSources : allows to modify the aspect of the rows and titles
 *
 * @package    DspLib
 * @subpackage DataSource
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since      28 oct. 2011 01:29:12
 */

namespace DspLib\DataSource;

/**
 * Decorator of the DataSources : allows to modify the aspect of the rows and titles
 *
 * @package    DspLib
 * @subpackage DataSource
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since      28 oct. 2011 01:29:12
 */
abstract class DataSourceDecorator
{
    public function decorate(array $aRow)
    {
        return $aRow;
    }

    public function decorateKeys(array $aRow)
    {
        return $aRow;
    }
}
