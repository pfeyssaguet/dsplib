<?php

/**
 * Allows to filter a DataSource with various options
 *
 * FIXME works only with DataSourceSQL... a pain in the ass to implement, have to do it in each DS..
 *
 * @package    DspLib
 * @subpackage DataSource
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since      28 oct. 2011 21:49:06
 */

namespace DspLib\DataSource;

/**
 * Allows to filter a DataSource with various options
 *
 * @package    DspLib
 * @subpackage DataSource
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since      28 oct. 2011 21:49:06
 */
class DataSourceFilter
{

    const SIGN_EQUALS                 = '=';
    const SIGN_DIFFERENT             = '!=';
    const SIGN_GREATER                 = '>';
    const SIGN_GREATER_OR_EQUALS     = '>=';
    const SIGN_LESSER                 = '<';
    const SIGN_LESSER_OR_EQUALS     = '<=';
    const SIGN_BETWEEN                 = 'BETWEEN';
    const SIGN_LIMIT                 = 'LIMIT';
    const SIGN_CONTENT                 = 'LIKE';
    const SIGN_NOTCONTENT             = 'NOTLIKE';
    const SIGN_ISNULL                 = 'ISNULL';
    const SIGN_ISNOTNULL             = 'ISNOTNULL';

    const TYPE_INTEGER                 = 'INT';
    const TYPE_VARCHAR                 = 'VARCHAR';
    const TYPE_DATE                    = 'DATE';
    const TYPE_DATETIME             = 'DATETIME';
    const TYPE_LIST                 = 'LIST';

    private $aFilters = array();

    public function getFilters()
    {
        return $this->aFilters;
    }

    public function addFilter($sField, $sSign, $sValue, $sValue2 = null)
    {
        $this->aFilters[] = array(
            'field'     => $sField,
            'sign'         => $sSign,
            'value'     => $sValue,
            'value2'     => $sValue2
        );
    }
}
