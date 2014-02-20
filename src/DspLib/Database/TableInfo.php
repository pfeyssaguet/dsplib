<?php

/**
 * Describes the structure of a table in a database
 *
 * @package    DspLib
 * @subpackage Database
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since      4 mars 2011 22:50:27
 */

namespace DspLib\Database;

/**
 * Describes the structure of a table in a database
 *
 * @package    DspLib
 * @subpackage Database
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since      4 mars 2011 22:50:27
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
     * Non unique keys list
     *
     * @var array
     */
    private $aKeys = array();

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
     * Adds a key on a field list
     *
     * @param string $sKey Key name
     * @param array $aFields Field list
     * @param boolean $bUnique True for unique key
     */
    public function addKey($sKey, array $aFields, $bUnique = false)
    {
        if ($bUnique) {
            $this->aUniqueKeys[$sKey] = $aFields;
        } else {
            $this->aKeys[$sKey] = $aFields;
        }
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
    public function loadFields()
    {
        $sQuery = "SHOW FULL COLUMNS FROM `" . $this->sName . "`";
        $oStmt = $this->oDb->query($sQuery);

        foreach ($oStmt as $aData) {
            $sFieldName = $aData['Field'];
            $sFieldType = $aData['Type'];
            $bNullable = false;
            if (isset($aData['Null']) && $aData['Null'] == 'YES') {
                $bNullable = true;
            }

            $sExtra = null;
            if (isset($aData['Extra'])) {
                $sExtra = $aData['Extra'];
            }

            $sDefault = null;
            if (isset($aData['Default']) && $aData['Default'] != 'NULL') {
                $sDefault = $aData['Default'];
            }

            $oFieldInfo = new FieldInfo($sFieldName, $sFieldType, $bNullable, $sDefault, $sExtra);

            if (!empty($aData['Comment'])) {
                $oFieldInfo->setComment($aData['Comment']);
            }

            $this->addField($oFieldInfo);
        }
    }

    /**
     * Loads the keys from the database (primary and unique)
     */
    public function loadKeys()
    {
        $sQuery = "SHOW KEYS FROM " . $this->sName;
        $oStmt = $this->oDb->query($sQuery);

        $aKeys = array();
        $aUniqueKeys = array();
        foreach ($oStmt as $aData) {
            if ($aData['Non_unique'] == '0') {
                $aUniqueKeys[$aData['Key_name']][] = $aData['Column_name'];
            } else {
                $aKeys[$aData['Key_name']][] = $aData['Column_name'];
            }
        }

        foreach ($aUniqueKeys as $sKey => $aFields) {
            if ($sKey == 'PRIMARY') {
                foreach ($aFields as $sField) {
                    $this->addPrimaryKey($sField);
                }
            } else {
                $this->addKey($sKey, $aFields, true);
            }
        }

        foreach ($aKeys as $sKey => $aFields) {
            $this->addKey($sKey, $aFields);
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
                $oTableInfo->addKey($sKeyName, $aKeys, true);
            } else {
                $oTableInfo->addKey($sKeyName, $aKeys);
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

        if (!empty($this->aPrimaryKeys)) {
            if (!isset($oElKeys)) {
                $oElKeys = $oDoc->createElement('Keys');
                $oElement->appendChild($oElKeys);
            }
            $oElPrimaryKey = $oDoc->createElement('Key');
            $oElKeys->appendChild($oElPrimaryKey);
            $oElPrimaryKey->setAttribute('type', 'primary');
            foreach ($this->aPrimaryKeys as $sField) {
                $oElField = $oDoc->createElement('Field');
                $oElPrimaryKey->appendChild($oElField);
                $oElField->setAttribute('name', $sField);

            }
        }

        if (!empty($this->aUniqueKeys)) {
            if (!isset($oElKeys)) {
                $oElKeys = $oDoc->createElement('Keys');
                $oElement->appendChild($oElKeys);
            }
            foreach ($this->aUniqueKeys as $sAlias => $aFields) {
                $oElUniqueKey = $oDoc->createElement('Key');
                $oElKeys->appendChild($oElUniqueKey);
                $oElUniqueKey->setAttribute('name', $sAlias);
                $oElUniqueKey->setAttribute('type', 'unique');
                foreach ($aFields as $sField) {
                    $oElKeyField = $oDoc->createElement('Field');
                    $oElUniqueKey->appendChild($oElKeyField);
                    $oElKeyField->setAttribute('name', $sField);
                }
            }
        }

        if (!empty($this->aKeys)) {
            if (!isset($oElKeys)) {
                $oElKeys = $oDoc->createElement('Keys');
                $oElement->appendChild($oElKeys);
            }
            foreach ($this->aKeys as $sAlias => $aFields) {
                $oElKey = $oDoc->createElement('Key');
                $oElKeys->appendChild($oElUniqueKey);
                $oElKey->setAttribute('name', $sAlias);
                $oElKey->setAttribute('type', 'index');
                foreach ($aFields as $sField) {
                    $oElKeyField = $oDoc->createElement('Field');
                    $oElKey->appendChild($oElKeyField);
                    $oElKeyField->setAttribute('name', $sField);
                }
            }
        }

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
                $sQuery .= "," . PHP_EOL;
            }
            $sQuery .= $oField->generateCreate();
        }

        if (!empty($this->aPrimaryKeys)) {
            $sQuery .= "," . PHP_EOL;
            $sQuery .= "PRIMARY KEY (`" . implode('`, `', $this->aPrimaryKeys) . "`)";
        }

        if (!empty($this->aUniqueKeys)) {
            $aKeys = array();
            foreach ($this->aUniqueKeys as $sKey => $aKeys) {
                $sQuery .= "," . PHP_EOL;
                $sQuery .= "UNIQUE KEY `$sKey` (`" . implode('`, `', $aKeys) . "`)";
            }
        }

        if (!empty($this->aKeys)) {
            $aKeys = array();
            foreach ($this->aKeys as $sKey => $aKeys) {
                $sQuery .= "," . PHP_EOL;
                $sQuery .= "KEY `$sKey` (`" . implode('`, `', $aKeys) . "`)";
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
