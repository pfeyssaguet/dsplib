<?php

/**
 * Record set container for PDO
 *
 * @package    DspLib
 * @subpackage Database
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since      22 oct. 2011 21:04:53
 */

namespace DspLib\Database\PDO;

/**
 * Record set container for PDO
 *
 * @package    DspLib
 * @subpackage Database
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since      22 oct. 2011 21:04:53
 */
class DbResult extends \DspLib\Database\DbResult
{

    /**
     * @var \PDOStatement
     */
    private $stmt;

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
     * Total number of rows without consideration for eventual limits
     *
     * @var integer
     */
    private $nbTotalRows = 0;

    /**
     * Initializes the resultset
     *
     * @param \PDOStatement $stmt        PDO resultset
     * @param integer       $nbTotalRows Number of rows without the limit
     *
     * @throws \Exception
     */
    public function __construct(\PDOStatement $stmt, $nbTotalRows)
    {
        $this->stmt = $stmt;
        $this->nbTotalRows = $nbTotalRows;

        $iNbCols = $this->stmt->columnCount();

        for ($i = 0; $i < $iNbCols; $i++) {
            $aMeta = $this->stmt->getColumnMeta($i);
            $this->columnNames[] = $aMeta['name'];
        }
    }

    public function getKeys()
    {
        return $this->columnNames;
    }

    public function count()
    {
        return $this->stmt->rowCount();
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
        return true;
    }

    /**
     * (non-PHPdoc)
     *
     * @see Iterator::next()
     */
    public function next()
    {
        $this->currentRow = $this->stmt->fetch(\PDO::FETCH_ASSOC);
        return $this->currentRow;
    }

    /**
     * (non-PHPdoc)
     *
     * @see Iterator::rewind()
     */
    public function rewind()
    {
        $this->currentRow = $this->stmt->fetch(\PDO::FETCH_ASSOC, \PDO::FETCH_ORI_FIRST);
        return $this->currentRow;
    }

    /**
     * (non-PHPdoc)
     *
     * @see Iterator::valid()
     */
    public function valid()
    {
        return $this->currentRow !== false;
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
