<?php

namespace DspLib\DataSource;

/**
 * DataSource d'accès à une table de la base de données
 *
 * @author deuspi
 * @since 11 oct. 2011 22:36:48
 */

class DataSourceTable extends DataSource
{

    /**
     * Nom de la table
     *
     * @var string
     */
    private $sTable;

    /**
     * Base de données
     *
     * @var \DspLib\Database\Database
     */
    private $oDb;

    /**
     * Description de la table
     *
     * @var \DspLib\Database\TableInfo
     */
    private $oTableInfo;

    /**
     * Liste des champs de la table
     *
     * @var array
     */
    private $aKeys = array();

    /**
     * Clause WHERE
     *
     * @var string
     */
    private $sWhere = '';

    /**
     * Jeu d'enregistrements
     *
     * @var \DspLib\Database\DbResult
     */
    private $oStmt;

    private $aCurrentRow;

    public function __construct($sTable, Database $oDb = null)
    {
        $this->sTable = $sTable;
        $this->oDb = $oDb;

        if (!isset($this->oDb)) {
            $this->oDb = \DspLib\Database\Database::getInstance();
        }

        $oDbInfo = \DspLib\Database\DatabaseInfo::getFromDb($this->oDb);
        $this->oTableInfo = $oDbInfo->getTable($this->sTable);
        if ($this->oTableInfo == null) {
            throw new \InvalidArgumentException("Table $sTable does not exists");
        }

        $aFields = $this->oTableInfo->getFields();
        foreach ($aFields as $oField) {
            $this->aKeys[] = $oField->getName();
        }
    }

    public function setWhere($sWhere)
    {
        $this->sWhere = $sWhere;
        $this->oStmt = null;
    }

    public function getQuery()
    {
        $sQuery = "SELECT * FROM " . $this->sTable;

        if (!empty($this->sWhere)) {
            $sQuery .= " WHERE " . $this->sWhere;
        }
        return $sQuery;
    }

    private function getStmt()
    {
        if (!isset($this->oStmt)) {
            $sQuery = $this->getQuery();
            $this->oStmt = $this->oDb->query($sQuery);
        }
        return $this->oStmt;
    }

    protected function getTitles()
    {
        return $this->aKeys;
    }

    public function count()
    {
        $oStmt = $this->getStmt();
        return count($oStmt);
    }

    /**
     * (non-PHPdoc)
     *
     * @see DataSource::getCurrentElement()
     */
    protected function getCurrentElement()
    {
        $oStmt = $this->getStmt();
        return $oStmt->current();
    }

    public function key()
    {
        $oStmt = $this->getStmt();
        return $oStmt->key();
    }

    public function next()
    {
        $oStmt = $this->getStmt();
        return $oStmt->next();
    }

    public function rewind()
    {
        $oStmt = $this->getStmt();
        return $oStmt->rewind();
    }

    public function valid()
    {
        $oStmt = $this->getStmt();
        return $oStmt->valid();
    }

    /**
     * Ajoute une ligne dans le DataSource
     *
     * @param array $aRow Ligne à ajouter
     */
    public function writeRow(array $aRow)
    {
        $sQuery = "INSERT INTO " . $this->sTable . " (" . implode(", ", array_keys($aRow)) . ") VALUES (";
        $aValues = array();
        foreach ($aRow as $sValue) {
            $aValues[] = $this->oDb->escapeString($sValue);
        }
        $sQuery .= implode(", ", $aValues) . ")";
        $this->oDb->query($sQuery);
    }
}
