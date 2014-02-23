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
    private $oStmt;

    private $aKeys = array();

    private $aCurrentRow;

    /**
     * Total number of rows without consideration for eventual limits
     * @var integer
     */
    private $iNbTotalRow = 0;

    public function __construct(\PDOStatement $oStmt, $iNbTotalRows)
    {
        $this->oStmt = $oStmt;
        $this->iNbTotalRow = $iNbTotalRows;

        $iNbCols = $this->oStmt->columnCount();

        for ($i = 0; $i < $iNbCols; $i++) {
            $aMeta = $this->oStmt->getColumnMeta($i);
            $this->aKeys[] = $aMeta['name'];
        }
    }

    public function getKeys()
    {
        return $this->aKeys;
    }

    public function count()
    {
        return $this->oStmt->rowCount();
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
        return true;
    }

    /**
     * (non-PHPdoc)
     *
     * @see Iterator::next()
     */
    public function next()
    {
        $this->aCurrentRow = $this->oStmt->fetch(\PDO::FETCH_ASSOC);
        return $this->aCurrentRow;
    }

    /**
     * (non-PHPdoc)
     *
     * @see Iterator::rewind()
     */
    public function rewind()
    {
        $this->aCurrentRow = $this->oStmt->fetch(\PDO::FETCH_ASSOC, \PDO::FETCH_ORI_FIRST);
        return $this->aCurrentRow;
    }

    /**
     * (non-PHPdoc)
     *
     * @see Iterator::valid()
     */
    public function valid()
    {
        return $this->aCurrentRow !== false;
    }

    /**
     * Retourne le nomdre d'enregistrements total d'une requete sans tenir compte des limites
     */
    public function getTotalRowCount()
    {
        return $this->iNbTotalRow;
    }
}
