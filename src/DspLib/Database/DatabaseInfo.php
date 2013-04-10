<?php

/**
 * Cette classe permet de décrire la structure d'une base de données.
 *
 * @package    DspLib
 * @subpackage Database
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since      4 mars 2011 23:30:46
 */

namespace DspLib\Database;

/**
 * Cette classe permet de décrire la structure d'une base de données.
 *
 * @package    DspLib
 * @subpackage Database
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since      4 mars 2011 23:30:46
 */
class DatabaseInfo
{
    /**
     * Nom du schéma de base de données (nom de la base sous MySQL)
     *
     * @var string
     */
    private $sSchema;

    /**
     * Liste des tables de la base de données
     *
     * @var array[TableInfo]
     */
    private $aoTables = array();

    /**
     * Initialise le descriptif de base de données
     *
     * @param string $sSchema Nom du schéma
     */
    private function __construct($sSchema)
    {
        $this->sSchema = $sSchema;
    }

    /**
     * Renvoie la liste des tables
     *
     * @return array[TableInfo]
     */
    public function getTables()
    {
        return $this->aoTables;
    }

    /**
     * Renvoie la table à partir de son nom
     *
     * @param string $sName Nom de la table
     *
     * @return TableInfo
     */
    public function getTable($sName)
    {
        foreach ($this->aoTables as $oTable) {
            if ($oTable->getName() == $sName) {
                return $oTable;
            }
        }
        return false;
    }

    /**
     * Ajoute une table à la liste des tables
     *
     * @param TableInfo $oTable Table à ajouter
     */
    public function addTable(TableInfo $oTable)
    {
        $this->aoTables[] = $oTable;
    }

    /**
     * Indique si le modèle de données contient la table demandée
     *
     * @param string $sTable Nom de la table
     * @return boolean
     */
    public function hasTable($sTable)
    {
        foreach ($this->aoTables as $oTable) {
            if ($oTable->getName() == $sTable) {
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
        foreach ($this->aoTables as $oTable) {
            $sQuery .= "\n\n-- *** CREATION OF TABLE " . $oTable->getName() . " ***\n";
            $sQuery .= $oTable->generateCreate() . ";\n\n";
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
        $sSchema = $aParams['dbname'];
        $oDbInfo = new self($sSchema);
        $sQuery = "SHOW TABLES";
        $oStmt = $oDb->query($sQuery);

        foreach ($oStmt as $aData) {
            $sTableName = $aData['Tables_in_' . $sSchema];
            $oTableInfo = TableInfo::getTableInfoFromDb($oDb, $sTableName);
            $oDbInfo->addTable($oTableInfo);
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
        $oElRoot->setAttribute('name', $this->sSchema);

        foreach ($this->aoTables as $oTable) {
            $oElTable = $oDoc->createElement('Table');
            $oElRoot->appendChild($oElTable);
            $oTable->writeToXMLElement($oElTable, $oDoc);
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

        $sSchema = $oDocRoot->getAttribute('name');
        $oDbInfo = new DatabaseInfo($sSchema);

        $oNodesTables = $oDocRoot->getElementsByTagName('Table');
        for ($i = 0; $i < $oNodesTables->length; $i++) {
            $oElTable = $oNodesTables->item($i);

            $oTableInfo = TableInfo::loadXMLElement($oElTable);

            $oDbInfo->addTable($oTableInfo);
        }
        return $oDbInfo;
    }
}
