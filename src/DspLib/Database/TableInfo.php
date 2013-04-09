<?php

namespace DspLib\Database;

/**
 * Describes the structure of a table in a database
 *
 * @author Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since 4 mars 2011 22:50:27
 */
class TableInfo
{
    /**
     * Table name
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
     * Table comment
     *
     * @var string
     */
    private $sComment = '';

    /**
     * Field list
     *
     * @var array[FieldInfo]
     */
    private $aoFields = array();

    /**
     * Primary keys list
     *
     * @var array
     */
    private $aPrimaryKeys = array();

    /**
     * Unique keys list
     *
     * @var array
     */
    private $aUniqueKeys = array();

    /**
     * Initialization
     *
     * @param string $sName Table name
     */
    public function __construct($sName, Database $oDb = null)
    {
        $this->sName = $sName;
        $this->oDb = $oDb;
    }

    /**
     * Returns the table comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->sComment;
    }

    /**
     * Sets the table comment
     *
     * @param string $sComment Comment
     */
    public function setComment($sComment)
    {
        $this->sComment = $sComment;
    }

    /**
     * Returns the table name
     *
     * @return string
     */
    public function getName()
    {
        return $this->sName;
    }

    /**
     * Adds a field to the field list
     *
     * @param FieldInfo $oField Field to add
     */
    public function addField(FieldInfo $oField)
    {
        $this->aoFields[] = $oField;
    }

    /**
     * Returns the field list
     *
     * @return array[FieldInfo]
     */
    public function getFields()
    {
        return $this->aoFields;
    }

    /**
     * Adds a primary key
     *
     * @param string $sKey Primary key name
     */
    public function addPrimaryKey($sKey)
    {
        $this->aPrimaryKeys[] = $sKey;
    }

    /**
     * Returns the primary keys list
     *
     * @return array
     */
    public function getPrimaryKeys()
    {
        return $this->aPrimaryKeys;
    }

    /**
     * Adds a unique key on a field list
     *
     * @param string $sKey Key name
     * @param array $aFields Field list
     */
    public function addUniqueKey($sKey, array $aFields)
    {
        $this->aUniqueKeys[$sKey] = $aFields;
    }

    /**
     * Returns the unique keys list
     *
     * @return array[string => array[string]]
     */
    public function getUniqueKeys()
    {
        return $this->aUniqueKeys;
    }

    /**
     * Loads the fields from the database
     */
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

    /**
     * Loads the keys from the database (primary and unique)
     */
    private function loadKeys()
    {
        $sQuery = "SHOW KEYS FROM " . $this->sName;
        $oStmt = $this->oDb->query($sQuery);

        $aUniqueKeys = array();
        foreach ($oStmt as $aData) {
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
     * Loads the table structure from the database
     *
     * @param Database $oDb Database
     * @param string $sTableName Table name
     *
     * @return TableInfo
     */
    public static function getTableInfoFromDb(Database $oDb, $sTableName)
    {
        $oTable = new TableInfo($sTableName, $oDb);

        $oResult = $oDb->query("SHOW TABLE STATUS LIKE '$sTableName'");
        $oResult->rewind();
        $aRow = $oResult->current();
        $oTable->setComment($aRow['Comment']);

        $oTable->loadFields();
        $oTable->loadKeys();

        return $oTable;
    }

    /**
     * Loads the table structure from a XML Node
     *
     * @param DOMElement $oElement XML Node
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

        // Load the fields
        $oElFields = $oElement->getElementsByTagName('Fields')->item(0);
        self::loadXMLFields($oTableInfo, $oElFields);

        // Load the keys
        $oElKeys = $oElement->getElementsByTagName('Keys')->item(0);
        if ($oElKeys != null) {
            self::loadXMLKeys($oTableInfo, $oElKeys);
        }

        return $oTableInfo;
    }

    /**
     * Loads the fields from a XML Node
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
