<?php

/**
 * This class allows to describes a database structure.
 *
 * @package    DspLib
 * @subpackage Database
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since      4 mars 2011 23:30:46
 */

namespace DspLib\Database;

/**
 * This class allows to describes a database structure.
 *
 * @package    DspLib
 * @subpackage Database
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since      4 mars 2011 23:30:46
 */
class DatabaseInfo
{
    /**
     * Name of the schema
     *
     * @var string
     */
    private $schema;

    /**
     * List of the tables
     *
     * @var array[TableInfo]
     */
    private $tables = array();

    /**
     * Initializes the database describer.
     *
     * @param string $schema Schema name
     */
    private function __construct($schema)
    {
        $this->schema = $schema;
    }

    /**
     * Returns the list of the tables.
     *
     * @return array[TableInfo]
     */
    public function getTables()
    {
        return $this->tables;
    }

    /**
     * Returns a table based on its name.
     *
     * @param string $name Name of the table
     *
     * @return TableInfo
     */
    public function getTable($name)
    {
        foreach ($this->tables as $table) {
            if ($table->getName() == $name) {
                return $table;
            }
        }
        return false;
    }

    /**
     * Adds a table to the table list.
     *
     * @param TableInfo $tableInfo Table to add
     */
    public function addTable(TableInfo $tableInfo)
    {
        $this->tables[] = $tableInfo;
    }

    /**
     * Indique si le modèle de données contient la table demandée
     *
     * @param string $tableName Nom de la table
     *
     * @return boolean
     */
    public function hasTable($tableName)
    {
        foreach ($this->tables as $tableInfo) {
            if ($tableInfo->getName() == $tableName) {
                return true;
            }
        }
        return false;
    }

    /**
     * Génère les requêtes SQL pour créer les tables
     *
     * @return string
     */
    public function generateCreate()
    {
        $sQuery = "";
        foreach ($this->tables as $table) {
            $sQuery .= "\n\n-- *** CREATION OF TABLE " . $table->getName() . " ***\n";
            $sQuery .= $table->generateCreate() . ";\n\n";
        }
        return $sQuery;
    }

    /**
     * Renvoie le descriptif de base à partir d'une connexion.
     *
     * @param Database $oDb Connexion à une base de données
     *
     * @return DatabaseInfo
     */
    public static function getFromDb(Database $oDb)
    {
        $aParams = $oDb->getParams();
        $schema = $aParams['dbname'];
        $oDbInfo = new self($schema);
        $sQuery = "SHOW TABLES";
        $oStmt = $oDb->query($sQuery);

        foreach ($oStmt as $aData) {
            $tableNameName = $aData['Tables_in_' . $schema];
            $tableInfo = TableInfo::getTableInfoFromDb($oDb, $tableNameName);
            $oDbInfo->addTable($tableInfo);
        }
        return $oDbInfo;
    }

    /**
     * Sauvegarde la structure de base de données dans un format XML
     *
     * @param string $sPath Chemin du fichier XML
     */
    public function saveXML($sPath)
    {
        $oDoc = new \DOMDocument('1.0', 'UTF-8');
        $oElRoot = $oDoc->createElement('Schema');
        $oDoc->appendChild($oElRoot);
        $oElRoot->setAttribute('name', $this->schema);

        foreach ($this->tables as $table) {
            $oElTable = $oDoc->createElement('Table');
            $oElRoot->appendChild($oElTable);
            $table->writeToXMLElement($oElTable, $oDoc);
        }
        $oDoc->formatOutput = true;
        $oDoc->save($sPath);
    }

    /**
     * Charge la structure de base de données à partir d'un XML
     *
     * @param string $sPath Chemin du fichier XML
     *
     * @return DatabaseInfo
     */
    public static function loadXML($sPath)
    {
        $oDoc = new \DOMDocument();
        $oDoc->load($sPath);
        $oDocRoot = $oDoc->documentElement;

        $schema = $oDocRoot->getAttribute('name');
        $oDbInfo = new DatabaseInfo($schema);

        $oNodetableNames = $oDocRoot->getElementsByTagName('Table');
        for ($i = 0; $i < $oNodetableNames->length; $i++) {
            $oElTable = $oNodetableNames->item($i);

            $tableInfo = TableInfo::loadXMLElement($oElTable);

            $oDbInfo->addTable($tableInfo);
        }
        return $oDbInfo;
    }
}
