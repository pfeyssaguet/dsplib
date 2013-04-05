<?php

namespace DspLib\Database;

/**
 * Cette classe permet de décrire la structure d'une table en base de données.
 *
 * @author deuspi
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
     * Liste des tables liées
     *
     * @var array[TableInfo]
     */
    private $aoLinkedTables = array();

    private $aOneToMany = array();

    private $aManyToOne = array();

    private $aManyToMany = array();

    /**
     * Initialise le descriptif de table
     *
     * @param string $sName Nom de la table
     */
    public function __construct($sName)
    {
        $this->sName = $sName;
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

    /**
     * Renvoie la liste des tables liées
     *
     * @return array[TableInfo]
     */
    public function getLinkedTables()
    {
        return $this->aoLinkedTables;
    }

    /**
     * Ajoute une table de jointure à la liste des tables liées
     *
     * @param string $sTableName Nom de la table liée
     * @param TableInfo $oTableInfo Table de jointure
     */
    public function addLinkedTable($sTableName, TableInfo $oTableInfo)
    {
        $this->aoLinkedTables[$sTableName] = $oTableInfo;
    }

    public function addOneToMany($sTableName)
    {
        $this->aOneToMany[] = $sTableName;
    }

    public function addManyToOne($sTableName)
    {
        $this->aManyToOne[] = $sTableName;
    }

    public function addManyToMany($sTableName, $sForeignKey)
    {
        $this->aManyToMany[$sForeignKey] = $sTableName;
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
        $oTable = new TableInfo($sTableName);

        $sQuery = "DESC $sTableName";
        $oStmt = $oDb->query($sQuery);

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
            $oTable->addField($oFieldInfo);
        }

        $sQuery = "SHOW KEYS FROM $sTableName";
        $oStmt = $oDb->query($sQuery);

        $aUniqueKeys = array();
        while ($aData = $oStmt->next()) {
            $aUniqueKeys[$aData['Key_name']][] = $aData['Column_name'];
        }

        foreach ($aUniqueKeys as $sKey => $aFields) {
            if ($sKey == 'PRIMARY') {
                foreach ($aFields as $sField) {
                    $oTable->addPrimaryKey($sField);
                }
            } else {
                $oTable->addUniqueKey($sKey, $aFields);
            }
        }

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

        // Chargement des tables liées
        $oElOneToMany = $oElement->getElementsByTagName('OneToMany')->item(0);
        if ($oElOneToMany != null) {
            self::loadXMLOneToMany($oTableInfo, $oElOneToMany);
        }

        $oElManyToMany = $oElement->getElementsByTagName('ManyToMany')->item(0);
        if ($oElManyToMany != null) {
            self::loadXMLManyToMany($oTableInfo, $oElManyToMany);
        }

        $oElManyToOne = $oElement->getElementsByTagName('ManyToOne')->item(0);
        if ($oElManyToOne != null) {
            self::loadXMLManyToOne($oTableInfo, $oElManyToOne);
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
     * Charge les tables liées à celle-ci en 1-N à partir du noeud XML
     *
     * @param TableInfo $oTableInfo Objet dans lequel on veut charger les tables liées
     * @param DOMElement $oElement Noeud XML
     */
    private static function loadXMLOneToMany(TableInfo $oTableInfo, \DOMElement $oElement)
    {
        $oNodesLinkedTables = $oElement->getElementsByTagName('LinkedTable');
        for ($i = 0; $i < $oNodesLinkedTables->length; $i++) {
            $oElLinkedTable = $oNodesLinkedTables->item($i);
            $sTableName = $oElLinkedTable->getAttribute('name');
            $oTableInfo->addOneToMany($sTableName);
        }
    }

    /**
     * Charge les tables liées à celle-ci en N-N à partir du noeud XML
     *
     * @param TableInfo $oTableInfo Objet dans lequel on veut charger les tables liées
     * @param DOMElement $oElement Noeud XML
     */
    private static function loadXMLManyToMany(TableInfo $oTableInfo, \DOMElement $oElement)
    {
        $oNodesLinkedTables = $oElement->getElementsByTagName('LinkedTable');
        for ($i = 0; $i < $oNodesLinkedTables->length; $i++) {
            $oElLinkedTable = $oNodesLinkedTables->item($i);
            $sTableName = $oElLinkedTable->getAttribute('name');
            $sForeignKey = $oElLinkedTable->getAttribute('foreign');

            $oElJoinTable = $oElLinkedTable->getElementsByTagName('JoinTable')->item(0);

            $oJoinTable = self::loadXMLElement($oElJoinTable);

            $oTableInfo->addManyToMany($sTableName, $sForeignKey);
            $oTableInfo->addLinkedTable($sTableName, $oJoinTable);
        }
    }

    /**
     * Charge les tables liées à celle-ci en N-1 à partir du noeud XML
     *
     * @param TableInfo $oTableInfo Objet dans lequel on veut charger les tables liées
     * @param DOMElement $oElement Noeud XML
     */
    private static function loadXMLManyToOne(TableInfo $oTableInfo, \DOMElement $oElement)
    {
        $oNodesLinkedTables = $oElement->getElementsByTagName('LinkedTable');
        for ($i = 0; $i < $oNodesLinkedTables->length; $i++) {
            $oElLinkedTable = $oNodesLinkedTables->item($i);
            $sTableName = $oElLinkedTable->getAttribute('name');
            $oTableInfo->addManyToOne($sTableName);
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

    /**
     * Génère une classe de DAO
     *
     * @param string $sPath Chemin du répertoire où créer le fichier
     *
     * @throws Exception
     */
    public function generateDAO($sPath)
    {
        $oDb = Database::getInstance();
        $oDbInfo = DatabaseInfo::getFromDb($oDb);

        // Création des paramètres pour le template
        $sClassName = \DspLib\Lib\StringUtils::toCamelCase($this->sName);
        $aFields = array();
        foreach ($this->aoFields as $oField) {
            $sFieldName = $oField->getName();
            $sPropNameMaj = \DspLib\Lib\StringUtils::toCamelCase($sFieldName);
            $sPropName = 's' . $sPropNameMaj;
            $aFields[] = array(
                'name' => $sFieldName,
                'type' => 'string',
                'prop_name' => $sPropName,
                'prop_name_maj' => $sPropNameMaj
            );
        }

        // On génère des ptites méthodes statiques
        $aKeys = array();
        $aPrimaryKeys = array();
        foreach ($this->aPrimaryKeys as $sFieldName) {
            $sPropNameMaj = \DspLib\Lib\StringUtils::toCamelCase($sFieldName);
            $sPropName = 's' . $sPropNameMaj;
            $aPrimaryKeys[] = array(
                'name' => $sFieldName,
                'prop_name' => $sPropName,
                'prop_name_maj' => $sPropNameMaj
            );

        }

        $sPrimaryKey = '';
        if (count($aPrimaryKeys) == 1) {
            $sPrimaryKey = $aPrimaryKeys[0]['name'];
            $sPrimaryKeyPropName = 's' . \DspLib\Lib\StringUtils::toCamelCase($sPrimaryKey);

            // Ajout de la clef primaire en tant que clef simple
            $aKeys[] = array(
                'name' => $sPrimaryKey,
                'prop_name' => $sPrimaryKeyPropName,
                'prop_name_maj' => \DspLib\Lib\StringUtils::toCamelCase($sPrimaryKey),
                'method_args' => '$' . $sPrimaryKeyPropName,
                'Fields' => array(
                    array(
                        'name' => $sPrimaryKey,
                        'prop_name' => $sPrimaryKeyPropName
                    )
                )
            );
        }

        // Ajout des clefs uniques
        foreach ($this->aUniqueKeys as $sKeyName => $aKeyFields) {
            $sPropNameMaj = \DspLib\Lib\StringUtils::toCamelCase($sKeyName);
            $sPropName = 's' . $sPropNameMaj;
            $aPropFields = array();
            $aMethodArgs = array();
            foreach ($aKeyFields as $sKeyFieldName) {
                $sKeyFieldPropName = 's' . \DspLib\Lib\StringUtils::toCamelCase($sKeyFieldName);
                $aPropFields[] = array(
                    'name' => $sKeyFieldName,
                    'prop_name' => $sKeyFieldPropName
                );
                $aMethodArgs[] = '$' . $sKeyFieldPropName;
            }
            $aKeys[] = array(
                'name' => $sKeyName,
                'prop_name' => $sPropName,
                'prop_name_maj' => $sPropNameMaj,
                'method_args' => implode(', ', $aMethodArgs),
                'Fields' => $aPropFields
            );
        }

        $aOneToMany = array();
        if (!empty($this->aOneToMany)) {
            if (empty($sPrimaryKey)) {
                $sError = "Impossible de générer le OneToMany de l'objet $sTableName : ";
                if (count($aPrimaryKeys) == 0) {
                    $sError .= "La table n'a pas de clef primaire";
                } else {
                    $sError .= "La table a plusieurs clefs primaires";
                }
                throw new Exception($sError);
            }
            $sPrimaryKeyPropName = 's' . \DspLib\Lib\StringUtils::toCamelCase($sPrimaryKey);
            foreach ($this->aOneToMany as $sLinkedTableName) {
                $sLinkedClassName = \DspLib\Lib\StringUtils::toCamelCase($sLinkedTableName);
                $oLinkedTableInfo = $oDbInfo->getTable($sLinkedTableName);
                $aLinkedTableFields = $oLinkedTableInfo->getFields();
                $aLinkedFields = array();
                foreach ($aLinkedTableFields as $oField) {
                    $sFieldName = $oField->getName();
                    $sPropNameMaj = \DspLib\Lib\StringUtils::toCamelCase($sFieldName);
                    $sPropName = 's' . $sPropNameMaj;
                    $aLinkedFields[] = array(
                        'name' => $sFieldName
                    );
                }

                $aOneToMany[] = array(
                    'linked_table' => $sLinkedTableName,
                    'linked_class' => $sLinkedClassName,
                    'foreign_key' => $sPrimaryKey,
                    'foreign_key_prop_name' => $sPrimaryKeyPropName,
                    'Fields' => $aLinkedFields
                );
            }
        }

        $aManyToOne = array();
        if (!empty($this->aManyToOne)) {
            foreach ($this->aManyToOne as $sLinkedTableName) {
                $sLinkedClassName = \DspLib\StringUtils::toCamelCase($sLinkedTableName);
                $oLinkedTableInfo = $oDbInfo->getTable($sLinkedTableName);
                $aLinkedTableFields = $oLinkedTableInfo->getFields();
                $aLinkedPK = $oLinkedTableInfo->getPrimaryKeys();
                if (!isset($aLinkedPK[0])) {
                    $sMessage = "Impossible de générer le ManyToOne de l'objet $sTableName : ";
                    $sMessage .= "La table liée $sLinkedTableName n'a pas de clef primaire !";
                    throw new \Exception($sMessage);
                }
                $sForeignKeyPropName = \DspLib\StringUtils::toCamelCase($aLinkedPK[0]);
                $aLinkedFields = array();
                foreach ($aLinkedTableFields as $oField) {
                    $sFieldName = $oField->getName();
                    $sPropNameMaj = \DspLib\StringUtils::toCamelCase($sFieldName);
                    $sPropName = 's' . $sPropNameMaj;
                    $aLinkedFields[] = array(
                        'name' => $sFieldName
                    );
                }

                $aManyToOne[] = array(
                    'linked_table' => $sLinkedTableName,
                    'linked_class' => $sLinkedClassName,
                    'foreign_key' => $sPrimaryKey,
                    'foreign_key_prop_name' => $sForeignKeyPropName,
                );
            }
        }

        $aManyToMany = array();
        if (!empty($this->aManyToMany)) {
            foreach ($this->aManyToMany as $sForeignKey => $sLinkedTableName) {
                $oLinkedTableInfo = $oDbInfo->getTable($sLinkedTableName);
                $oJoinTableInfo = $this->aoLinkedTables[$sLinkedTableName];
                $sJoinTable = $oJoinTableInfo->getName();

                $sLinkedClassName = \DspLib\StringUtils::toCamelCase($sLinkedTableName);
                $aLinkedTableFields = $oLinkedTableInfo->getFields();
                $aLinkedPK = $oLinkedTableInfo->getPrimaryKeys();
                if (!isset($aLinkedPK[0])) {
                    $sMessage = "Impossible de générer le ManyToMany de l'objet " . $this->sName . ' :';
                    $sMessage .= " la table liée $sLinkedTableName n'a pas de clef primaire !";
                    throw new Exception($sMessage);
                }
                $sLinkedPrimaryKey = $aLinkedPK[0];


                $sForeignKeyPropName = \DspLib\StringUtils::toCamelCase($sForeignKey);
                $aLinkedFields = array();
                foreach ($aLinkedTableFields as $oField) {
                    $sFieldName = $oField->getName();
                    $sPropNameMaj = \DspLib\StringUtils::toCamelCase($sFieldName);
                    $sPropName = 's' . $sPropNameMaj;
                    $aLinkedFields[] = array(
                        'name' => $sLinkedTableName . '.' . $sFieldName
                    );
                }

                $aoJoinFields = $oJoinTableInfo->getFields();
                foreach ($aoJoinFields as $oJoinField) {
                    $aLinkedFields[] = array(
                        'name' => $sJoinTable . '.' . $oJoinField->getName()
                    );
                }

                $aManyToMany[] = array(
                    'linked_table' => $sLinkedTableName,
                    'linked_class' => $sLinkedClassName,
                    'linked_primary_key' => $sLinkedPrimaryKey,
                    'primary_key' => $sPrimaryKey,
                    'primary_key_prop_name' => $sPrimaryKeyPropName,
                    'foreign_key' => $sForeignKey,
                    'foreign_key_prop_name' => $sForeignKeyPropName,
                    'join_table' => $sJoinTable,
                    'Fields' => $aLinkedFields
                );
            }
        }

        $sDateGenerated = date('Y-m-d H:i:s');
        $sFileName = 'DAO' . $sClassName . '.php';

        // Création du template
        $oTpl = new \DspLib\Template(__DIR__ . '/template_dao.txt');

        $oTpl->setParam('table_name', $this->sName);
        $oTpl->setParam('date_generated', $sDateGenerated);
        $oTpl->setParam('Fields', $aFields);
        $oTpl->setParam('PrimaryKeys', $aPrimaryKeys);
        $oTpl->setParam('Keys', $aKeys);
        $oTpl->setParam('OneToMany', $aOneToMany);
        $oTpl->setParam('ManyToOne', $aManyToOne);
        $oTpl->setParam('ManyToMany', $aManyToMany);
        $oTpl->setParam('class_name', $sClassName);

        $sOutput = $oTpl->render();

        $sFilePath = $sPath . '/' . $sFileName;
        $bRegen = false;
        if (is_readable($sFilePath)) {
            $bRegen = true;
        }
        file_put_contents($sFilePath, $sOutput);

        if ($bRegen) {
            return false;
        }

        // Nouveau DAO donc on doit relancer le make_autoload
        return true;

    }

    public function createTable(Database $oDb)
    {
        $sQuery = $this->generateCreate();
        $oDb->query($sQuery);
    }
}
