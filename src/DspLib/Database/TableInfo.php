<?php

namespace DspLib\Database;

/**
 * Cette classe permet de décrire la structure d'une table en base de données.
 *
 * @author Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since 4 mars 2011 22:50:27
 */
class TableInfo
{

    /**
     * Nom de la table
     *
     * @var string
     */
    private $sName;

    /**
     * Database
     *
     * @var Database
     */
    private $oDb;

    /**
     * Commentaire de la table
     *
     * @var string
     */
    private $sComment = '';

    /**
     * Liste des champs
     *
     * @var array[FieldInfo]
     */
    private $aoFields = array();

    /**
     * Liste des clefs primaires
     *
     * @var array
     */
    private $aPrimaryKeys = array();

    /**
     * Liste des clefs uniques
     *
     * @var array
     */
    private $aUniqueKeys = array();

    /**
     * Initialise le descriptif de table
     *
     * @param string $sName Nom de la table
     */
    public function __construct($sName, Database $oDb = null)
    {
        $this->sName = $sName;
        $this->oDb = $oDb;
    }

    /**
     * Renvoie le commentaire de la table
     *
     * @return string
     */
    public function getComment()
    {
        return $this->sComment;
    }

    /**
     * Définit le commentaire de la table
     *
     * @param string $sComment Commentaire
     */
    public function setComment($sComment)
    {
        $this->sComment = $sComment;
    }

    /**
     * Renvoie le nom de la table
     *
     * @return string
     */
    public function getName()
    {
        return $this->sName;
    }

    /**
     * Ajoute un champ à la liste des champs de la table
     *
     * @param FieldInfo $oField Champ à ajouter
     */
    public function addField(FieldInfo $oField)
    {
        $this->aoFields[] = $oField;
    }

    /**
     * Renvoie la liste des champs de la table
     *
     * @return array[FieldInfo]
     */
    public function getFields()
    {
        return $this->aoFields;
    }

    /**
     * Ajoute une clef primaire à la liste des clefs primaires
     *
     * @param string $sKey Nom de la clef primaire
     */
    public function addPrimaryKey($sKey)
    {
        $this->aPrimaryKeys[] = $sKey;
    }

    /**
     * Renvoie la liste des clefs primaires
     *
     * @return array
     */
    public function getPrimaryKeys()
    {
        return $this->aPrimaryKeys;
    }

    /**
     * Ajoute une clef unique sur une liste de champs
     *
     * @param string $sKey Nom de la clef
     * @param array $aFields Liste des champs sur lesquels porte la clef
     */
    public function addUniqueKey($sKey, array $aFields)
    {
        $this->aUniqueKeys[$sKey] = $aFields;
    }

    /**
     * Renvoie la liste des clefs uniques
     *
     * @return array[string => array[string]]
     */
    public function getUniqueKeys()
    {
        return $this->aUniqueKeys;
    }

    private function loadFields()
    {
        $sQuery = "DESC " . $this->sName;
        $oStmt = $this->oDb->query($sQuery);

        foreach ($oStmt as $aData) {
            $sFieldName = $aData['Field'];
            $sFieldType = $aData['Type'];
            $iSize = null;
            if (preg_match('/(?<type>[\w]+)\((?<size>[\w]+)\)/', $sFieldType, $aMatches)) {
                $sFieldType = $aMatches['type'];
                $iSize = $aMatches['size'];
            }
            $bNullable = false;
            if (isset($aData['Null']) && $aData['Null'] == 'YES') {
                $bNullable = true;
            }

            $sExtra = null;
            if (isset($aData['Extra'])) {
                $sExtra = $aData['Extra'];
            }

            $oFieldInfo = new FieldInfo($sFieldName, $sFieldType, $bNullable, $sExtra, $iSize);
            $this->addField($oFieldInfo);
        }
    }

    private function loadKeys()
    {
        $sQuery = "SHOW KEYS FROM " . $this->sName;
        $oStmt = $this->oDb->query($sQuery);

        $aUniqueKeys = array();
        while ($aData = $oStmt->next()) {
            $aUniqueKeys[$aData['Key_name']][] = $aData['Column_name'];
        }

        foreach ($aUniqueKeys as $sKey => $aFields) {
            if ($sKey == 'PRIMARY') {
                foreach ($aFields as $sField) {
                    $this->addPrimaryKey($sField);
                }
            } else {
                $this->addUniqueKey($sKey, $aFields);
            }
        }
    }

    /**
     * Charge la structure de la table à partir de la base de données
     *
     * @param Database $oDb Base de données à analyser
     * @param string $sTableName Nom de la table
     *
     * @return TableInfo
     */
    public static function getTableInfoFromDb(Database $oDb, $sTableName)
    {
        $oTable = new TableInfo($sTableName, $oDb);

        $oTable->loadFields();

        $oTable->loadKeys();

        return $oTable;
    }

    /**
     * Charge la structure de la table à partir d'un noeud XML
     *
     * @param DOMElement $oElement Noeud de la table
     *
     * @return TableInfo
     */
    public static function loadXMLElement(\DOMElement $oElement)
    {

        $sName = $oElement->getAttribute('name');
        $oTableInfo = new TableInfo($sName);

        if ($oElement->hasAttribute('comment')) {
            $sComment = $oElement->getAttribute('comment');
            $oTableInfo->setComment($sComment);
        }

        $oElFields = $oElement->getElementsByTagName('Fields')->item(0);
        self::loadXMLFields($oTableInfo, $oElFields);

        // Chargement des clefs primaires et des autres clefs
        $oElKeys = $oElement->getElementsByTagName('Keys')->item(0);
        if ($oElKeys != null) {
            self::loadXMLKeys($oTableInfo, $oElKeys);
        }

        return $oTableInfo;
    }

    /**
     * Charge les champs de la table à partir du noeud XML
     *
     * @param TableInfo $oTableInfo Objet dans lequel on veut charger les champs
     * @param DOMElement $oElement Noeud XML contenant les champs à charger
     */
    private static function loadXMLFields(TableInfo $oTableInfo, \DOMElement $oElement)
    {
        $oNodesFields = $oElement->getElementsByTagName('Field');
        for ($i = 0; $i < $oNodesFields->length; $i++) {
            $oElField = $oNodesFields->item($i);
            $oFieldInfo = FieldInfo::loadXMLElement($oElField);
            $oTableInfo->addField($oFieldInfo);
        }
    }

    /**
     * Charge les clefs primaires et les autres clefs à partir du noeud XML
     *
     * @param TableInfo $oTableInfo Objet dans lequel on veut charger les clefs
     * @param DOMElement $oElement Noeud XML contenant les clefs à charger
     */
    private static function loadXMLKeys(TableInfo $oTableInfo, \DOMElement $oElement)
    {
        $oNodesKeys = $oElement->getElementsByTagName('Key');
        for ($i = 0; $i < $oNodesKeys->length; $i++) {
            $oElKey = $oNodesKeys->item($i);
            $sType = $oElKey->getAttribute('type');
            $sKeyName = '';
            if ($oElKey->hasAttribute('name')) {
                $sKeyName = $oElKey->getAttribute('name');
            }
            $oNodesFields = $oElKey->getElementsByTagName('Field');

            $aKeys = array();
            for ($j = 0; $j < $oNodesFields->length; $j++) {
                $oElField = $oNodesFields->item($j);
                $sKey = $oElField->getAttribute('name');
                $aKeys[] = $sKey;
            }

            if ($sType == 'primary') {
                foreach ($aKeys as $sKey) {
                    $oTableInfo->addPrimaryKey($sKey);
                }
            } elseif ($sType == 'unique') {
                $oTableInfo->addUniqueKey($sKeyName, $aKeys);
            }
        }
    }

    /**
     * Crée un noeud XML représentant la table
     *
     * @param \DOMElement $oElement Noeud dans lequel créer l'élément
     * @param \DOMDocument $oDoc Document XML dans lequel créer l'élément
     *
     * @return \DOMElement
     */
    public function writeToXMLElement(\DOMElement $oElement, \DOMDocument $oDoc)
    {
        $oElement->setAttribute('name', $this->sName);

        $oElFields = $oDoc->createElement('Fields');
        $oElement->appendChild($oElFields);

        foreach ($this->aoFields as $oField) {
            $oElField = $oDoc->createElement('Field');
            $oElFields->appendChild($oElField);
            $oField->writeToXMLElement($oElField, $oDoc);
        }

        // TODO reconstituer les clefs

        return $oElement;
    }

    /**
     * Génère une requête SQL de création de la table
     *
     * @return string
     */
    public function generateCreate()
    {
        $sQuery = "CREATE TABLE IF NOT EXISTS `" . $this->sName . "` (" . PHP_EOL;
        $bStart = true;
        foreach ($this->aoFields as $oField) {
            if ($bStart) {
                $bStart = false;
            } else {
                $sQuery .= ", " . PHP_EOL;
            }
            $sQuery .= "\t";
            $sQuery .= $oField->generateCreate();
        }

        if (!empty($this->aPrimaryKeys)) {
            $sQuery .= ", " . PHP_EOL;
            $sQuery .= "\t";
            $sQuery .= "PRIMARY KEY (`" . implode('`, `', $this->aPrimaryKeys) . "`)";
        }

        if (!empty($this->aUniqueKeys)) {
            $aKeys = array();
            foreach ($this->aUniqueKeys as $sKey => $aKeys) {
                $sQuery .= ", " . PHP_EOL;
                $sQuery .= "\t";
                $sQuery .= "UNIQUE KEY `$sKey` (`" . implode('`, `', $aKeys) . "`)";
            }
        }
        $sQuery .= PHP_EOL . ")";

        if (!empty($this->sComment)) {
            $sQuery .= " COMMENT '" . addslashes($this->sComment) . "'";
        }
        return $sQuery;
    }

    public function createTable(Database $oDb)
    {
        $sQuery = $this->generateCreate();
        $oDb->query($sQuery);
    }
}
