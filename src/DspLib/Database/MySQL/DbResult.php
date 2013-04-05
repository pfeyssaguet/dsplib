<?php

namespace DspLib\Database\MySQL;

/**
 * Conteneur d'un jeu d'enregistrements pour MySQL
 *
 * @author deuspi
 * @since 22 oct. 2011 21:04:53
 */

class DbResult extends \DspLib\Database\DbResult
{
    private $mResults;

    private $iIndex = 0;

    private $aKeys = array();

    private $aCurrentRow;

    /**
     * Donne le nombre total de lignes d'un recordSet sans tenir compte des limites eventuelles
     * @var integer
     */
    private $iNbTotalRow = 0;

    public function __construct($mResults, $iNbTotalRows)
    {

        $this->mResults = $mResults;
        $this->iNbTotalRow = $iNbTotalRows;

        if (!is_resource($this->mResults)) {
            throw new \Exception('Parameter must be a MySQL result resource');
        }

        $iNbFields = mysql_num_fields($this->mResults);
        for ($i = 0; $i < $iNbFields; $i++) {
            $this->aKeys[$i] = mysql_field_name($this->mResults, $i);
        }
    }

    public function getKeys()
    {
         return $this->aKeys;
    }

    public function count()
    {
        return mysql_num_rows($this->mResults);
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
        $this->aCurrentRow = mysql_fetch_assoc($this->mResults);
    }

    /**
     * (non-PHPdoc)
     *
     * @see Iterator::rewind()
     */
    public function rewind()
    {
        if (mysql_num_rows($this->mResults) == 0) {
            return;
        }
        mysql_data_seek($this->mResults, 0);
        $this->iIndex = 0;
        $this->aCurrentRow = mysql_fetch_assoc($this->mResults);
    }

    /**
     * (non-PHPdoc)
     *
     * @see Iterator::valid()
     */
    public function valid()
    {
        return $this->iIndex < mysql_num_rows($this->mResults);
    }

    /**
     * Retourne le nomdre d'enregistrements total d'une requete sans tenir compte des limites
     *
     * @return int
     */
    public function getTotalRowCount()
    {
        return $this->iNbTotalRow;
    }
}
