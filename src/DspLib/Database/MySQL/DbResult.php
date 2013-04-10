<?php

/**
 * MySQL DbResult class file.
 *
 * Recordset container for mysql standard API
 *
 * @package    DspLib
 * @subpackage Database
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since      22 oct. 2011 21:04:53
 */

namespace DspLib\Database\MySQL;

/**
 * MySQL DbResult class.
 *
 * Recordset container for mysql standard API
 *
 * @package    DspLib
 * @subpackage Database
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since      22 oct. 2011 21:04:53
 */

class DbResult extends \DspLib\Database\DbResult
{
    /**
     * MySQL resultset
     *
     * @var resource
     */
    private $rResults;

    /**
     * Cursor position in the recordset
     *
     * @var integer
     */
    private $iIndex = 0;

    /**
     * Column titles
     *
     * @var array
     */
    private $aKeys = array();

    /**
     * Current row
     *
     * @var array
     */
    private $aCurrentRow;

    /**
     * Total number of rows in the recordset without the limit
     *
     * @var integer
     */
    private $iNbTotalRow = 0;

    /**
     * Initializes the resultset
     *
     * @param resource $rResults     MySQL resultset
     * @param integer  $iNbTotalRows Number of rows without the limit
     * @throws \Exception
     */
    public function __construct($rResults, $iNbTotalRows)
    {
        $this->rResults = $rResults;
        $this->iNbTotalRow = $iNbTotalRows;

        if (!is_resource($this->rResults)) {
            throw new \Exception('Parameter must be a MySQL result resource');
        }

        $iNbFields = mysql_num_fields($this->rResults);
        for ($i = 0; $i < $iNbFields; $i++) {
            $this->aKeys[$i] = mysql_field_name($this->rResults, $i);
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see \DspLib\Database\DbResult::getKeys()
     */
    public function getKeys()
    {
         return $this->aKeys;
    }

    /**
     * (non-PHPdoc)
     *
     * @see Countable::count()
     */
    public function count()
    {
        return mysql_num_rows($this->rResults);
    }

    /**
     * (non-PHPdoc)
     *
     * @see Iterator::current()
     */
    public function current()
    {
        return $this->aCurrentRow;
    }

    /**
     * (non-PHPdoc)
     *
     * @see Iterator::key()
     */
    public function key()
    {
        return $this->iIndex;
    }

    /**
     * (non-PHPdoc)
     *
     * @see Iterator::next()
     */
    public function next()
    {
        $this->iIndex++;
        $this->aCurrentRow = mysql_fetch_assoc($this->rResults);
    }

    /**
     * (non-PHPdoc)
     *
     * @see Iterator::rewind()
     */
    public function rewind()
    {
        if (mysql_num_rows($this->rResults) == 0) {
            return;
        }
        mysql_data_seek($this->rResults, 0);
        $this->iIndex = 0;
        $this->aCurrentRow = mysql_fetch_assoc($this->rResults);
    }

    /**
     * (non-PHPdoc)
     *
     * @see Iterator::valid()
     */
    public function valid()
    {
        return $this->iIndex < mysql_num_rows($this->rResults);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \DspLib\Database\DbResult::getTotalRowCount()
     */
    public function getTotalRowCount()
    {
        return $this->iNbTotalRow;
    }
}
