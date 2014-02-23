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
    private $results;

    /**
     * Cursor position in the recordset
     *
     * @var integer
     */
    private $position = 0;

    /**
     * Column titles
     *
     * @var array
     */
    private $columnNames = array();

    /**
     * Current row
     *
     * @var array
     */
    private $currentRow;

    /**
     * Total number of rows in the recordset without the limit
     *
     * @var integer
     */
    private $nbTotalRows = 0;

    /**
     * Initializes the resultset
     *
     * @param resource $results     MySQL resultset
     * @param integer  $nbTotalRows Number of rows without the limit
     *
     * @throws \Exception
     */
    public function __construct($results, $nbTotalRows)
    {
        $this->results = $results;
        $this->nbTotalRows = $nbTotalRows;

        if (!is_resource($this->results)) {
            throw new \Exception('Parameter must be a MySQL result resource');
        }

        $iNbFields = mysql_num_fields($this->results);
        for ($i = 0; $i < $iNbFields; $i++) {
            $this->columnNames[$i] = mysql_field_name($this->results, $i);
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see \DspLib\Database\DbResult::getKeys()
     */
    public function getKeys()
    {
         return $this->columnNames;
    }

    /**
     * (non-PHPdoc)
     *
     * @see Countable::count()
     */
    public function count()
    {
        return mysql_num_rows($this->results);
    }

    /**
     * (non-PHPdoc)
     *
     * @see Iterator::current()
     */
    public function current()
    {
        return $this->currentRow;
    }

    /**
     * (non-PHPdoc)
     *
     * @see Iterator::key()
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * (non-PHPdoc)
     *
     * @see Iterator::next()
     */
    public function next()
    {
        $this->position++;
        $this->currentRow = mysql_fetch_assoc($this->results);
    }

    /**
     * (non-PHPdoc)
     *
     * @see Iterator::rewind()
     */
    public function rewind()
    {
        if (mysql_num_rows($this->results) == 0) {
            return;
        }
        mysql_data_seek($this->results, 0);
        $this->position = 0;
        $this->currentRow = mysql_fetch_assoc($this->results);
    }

    /**
     * (non-PHPdoc)
     *
     * @see Iterator::valid()
     */
    public function valid()
    {
        return $this->position < mysql_num_rows($this->results);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \DspLib\Database\DbResult::getTotalRowCount()
     */
    public function getTotalRowCount()
    {
        return $this->nbTotalRows;
    }
}
