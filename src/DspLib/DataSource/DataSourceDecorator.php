<?php

namespace DspLib\DataSource;

/**
 * Decorator des DataSources : permet de modifier l'aspect des lignes et des titres
 *
 * @author deuspi
 * @since 28 oct. 2011 01:29:12
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
