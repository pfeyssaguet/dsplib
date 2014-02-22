<?php

/**
 * DataSource that provides access to an SQL query
 *
 * @package    DspLib
 * @subpackage DataSource
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since      9 oct. 2011 22:14:34
 */

namespace DspLib\DataSource;

use DspLib\Database\Database;

/**
 * DataSource that provides access to an SQL query
 *
 * @package    DspLib
 * @subpackage DataSource
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since      9 oct. 2011 22:14:34
 */
class DataSourceSQL extends DataSource
{

    /**
     * Requête SQL
     *
     * @var string
     */
    private $sQuery;

    /**
     * Base de données
     *
     * @var Database
     */
    private $oDb;

    /**
     * Résultat de la requête
     *
     * @var DbResult
     */
    private $oResult;

    public function __construct($sQuery, Database $oDb = null)
    {
        $this->sQuery = $sQuery;
        $this->oDb = $oDb;

        if (!isset($this->oDb)) {
            $this->oDb = Database::getInstance();
        }
    }

    private function getResult()
    {
        if (!isset($this->oResult)) {
            $sQuery = $this->sQuery;

            $oFilters = isset($this->oFilter) ? $this->oFilter : null;
            $this->oResult = $this->oDb->query($sQuery, $oFilters);
        }
        return $this->oResult;
    }

    /**
     * Retourne le nombre d'enregistrements total d'une requete sans tenir compte du limit
     *
     * @return int
     */
    public function getTotalRowCount()
    {
        return $this->getResult()->getTotalRowCount();
    }

    /**
     * (non-PHPdoc)
     *
     * @see DataSource::getTitles()
     */
    protected function getTitles()
    {
        return $this->getResult()->getKeys();
    }

    /**
     * (non-PHPdoc)
     *
     * @see Countable::count()
     */
    public function count()
    {
        return count($this->getResult());
    }

    /**
     * (non-PHPdoc)
     *
     * @see DataSource::getCurrentElement()
     */
    protected function getCurrentElement()
    {
        return $this->getResult()->current();
    }

    /**
     * (non-PHPdoc)
     *
     * @see Iterator::key()
     */
    public function key()
    {
        return $this->getResult()->key();
    }

    /**
     * (non-PHPdoc)
     *
     * @see Iterator::next()
     */
    public function next()
    {
        return $this->getResult()->next();
    }

    /**
     * (non-PHPdoc)
     *
     * @see Iterator::rewind()
     */
    public function rewind()
    {
        return $this->getResult()->rewind();
    }

    /**
     * (non-PHPdoc)
     *
     * @see Iterator::valid()
     */
    public function valid()
    {
        return $this->getResult()->valid();
    }

    /**
     * Définit le filtre à utiliser
     *
     * @param DataSourceFilter $oFilter
     */
    public function setFilter(DataSourceFilter $oFilter)
    {
        parent::setFilter($oFilter);
        $this->oResult = null;
    }

    /**
     * Ajoute une ligne dans le DataSource
     *
     * @param array $aRow Ligne à ajouter
     */
    public function writeRow(array $aRow)
    {
        throw new \Exception('Method writeRow is not supported in ' . __CLASS__);
    }
}
