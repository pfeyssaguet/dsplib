<?php

namespace DspLib\Test\DataSource;

use DspLib\DataSource\DataSourceDecorator;

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
