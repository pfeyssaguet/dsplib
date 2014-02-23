<?php

/**
 * DataSources are essentially wrappers of data which can be presented in tabular form
 *
 * @package    DspLib
 * @subpackage DataSource
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since      9 oct. 2011 15:51:30
 */

namespace DspLib\DataSource;

use DspLib\Template;

/**
 * DataSources are essentially wrappers of data which can be presented in tabular form
 *
 * @package    DspLib
 * @subpackage DataSource
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since      9 oct. 2011 15:51:30
 */
abstract class DataSource implements \Countable, \Iterator
{

    /**
     * Template à utiliser lors de l'utilisation de displayTable()
     *
     * @var Template
     */
    private static $oTplDisplayTable;

    /**
     * Décorateur à utiliser
     *
     * @var DataSourceDecorator
     */
    private $oDecorator;

    /**
     * Filtre à utiliser
     *
     * @var DataSourceFilter
     */
    protected $oFilter;

    /**
     * Renvoie la liste des clefs (titres de colonnes)
     *
     * @return array
     */
    abstract protected function getTitles();

    /**
     * Renvoie l'élément courant, pendant les itérations
     *
     * @return array
     */
    abstract protected function getCurrentElement();

    /**
     * Renvoie le nombre total d'enregistrements sans tenir compte d'une limite (à réimplémenter si on a une gestion
     * de limite)
     *
     * @return int
     */
    public function getTotalRowCount()
    {
        return count($this);
    }

    /**
     * Ajoute une ligne dans le DataSource
     *
     * @param array $aRow Ligne à ajouter
     */
    abstract public function writeRow(array $aRow);

    /**
     * Renvoie l'élément courant, pendant les itérations
     *
     * @return array
     */
    final public function current()
    {
        $aRow = $this->getCurrentElement();
        if (isset($this->oDecorator)) {
            $aRow = $this->oDecorator->decorate($aRow);
        }
        return $aRow;
    }

    /**
     * Renvoie la liste des clefs (titres de colonnes)
     *
     * @return array
     */
    final public function getKeys()
    {
        $aRow = $this->getTitles();
        if (isset($this->oDecorator)) {
            $aRow = $this->oDecorator->decorateKeys($aRow);
        }
        return $aRow;
    }

    /**
     * Écrit le contenu d'un DataSource dans le DataSource
     *
     * @param DataSource $odsIn DataSource contenant les données à importer
     */
    public function writeFromDataSource(DataSource $odsIn)
    {
        foreach ($odsIn as $aRow) {
            $this->writeRow($aRow);
        }
    }

    /**
     * Renvoie un template par défaut pour afficher un DS
     *
     * @return Template
     */
    public static function getTplDisplayTable()
    {
        if (!isset(self::$oTplDisplayTable)) {
            self::$oTplDisplayTable = new Template(__DIR__ . '/displayTable.html');
        }
        return self::$oTplDisplayTable;
    }

    /**
     * Définit le template d'affichage des tables
     *
     * @param Template $oTpl Template d'affichage
     */
    public static function setTplDisplayTable(Template $oTpl)
    {
        self::$oTplDisplayTable = $oTpl;
    }

    /**
     * Affiche le DS dans un tableau
     *
     * @param Template $oTpl Template à utiliser (facultatif)
     * @param array $aOptions Options (facultatif)
     *
     * @return string
     */
    public function displayTable(Template $oTpl = null, array $aOptions = array())
    {
        if (!isset($oTpl)) {
            $oTpl = $this->getTplDisplayTable();
        }

        $aTitles = $this->getKeys();
        $aTitles1 = array();
        foreach ($aTitles as $sTitle) {
            $aTitles1[] = array('Value' => $sTitle);
        }

        $aRows = $this->getRows();
        $aRows1 = array();
        foreach ($aRows as $aRow) {
            $aRow1 = array();
            foreach ($aRow as $sCell) {
                $aRow1[] = array('Value' => $sCell);
            }
            $aRows1[] = array('Cell' => $aRow1);
        }

        $oTpl->setParam('Titles', $aTitles1);
        $oTpl->setParam('Row', $aRows1);
        $oTpl->setParam('NumRows', count($aRows1));
        $oTpl->setParam('NbTotalRow', $this->getTotalRowCount());

        if (isset($aOptions['Title'])) {
            $oTpl->setParam('bTitle', true);
            $oTpl->setParam('Title', $aOptions['Title']);
        } else {
            $oTpl->setParam('bTitle', false);
        }

        return $oTpl->render();
    }

    /**
     * Renvoie toutes les lignes du DS
     *
     * @return array
     */
    public function getRows()
    {
        $aRows = array();
        foreach ($this as $aRow) {
            $aRows[] = $aRow;
        }
        return $aRows;
    }

    /**
     * Renvoie la première ligne du DS
     *
     * @return array
     */
    public function getFirstRow()
    {
        $aRow = null;
        foreach ($this as $aRow) {
            break;
        }
        return $aRow;
    }

    /**
     * Renvoie l'unique valeur dans recordSet dans le cas ou il n'y à qu'une ligne et 1 colonne
     */
    public function getUniqueValue()
    {
        $aKeys = $this->getTitles();
        $aRows = $this->getRows();
        $sReturn = false;

        if (count($aRows) == 1 && count($aKeys) == 1) {
            foreach ($aKeys as $sKeyName) {
                $sReturn = $aRows[0][$sKeyName];
                break;
            }
        }

        return $sReturn;
    }

    /**
     * Renvoie la valeur d'une colonne de la 1ère ligne
     *
     * @param string $sField Nom de la colonne à renvoyer
     * @return string
     */
    public function getValue($sField)
    {
        $sResult = '';
        $aFirstRow = $this->getFirstRow();
        if (isset($aFirstRow[$sField])) {
            $sResult = $aFirstRow[$sField];
        }
        return $sResult;
    }

    /**
     * Définit le décorateur à utiliser
     *
     * @param DataSourceDecorator $oDecorator
     */
    public function setDecorator(DataSourceDecorator $oDecorator)
    {
        $this->oDecorator = $oDecorator;
    }

    /**
     * Définit le filtre à utiliser
     *
     * @param DataSourceFilter $oFilter
     */
    public function setFilter(DataSourceFilter $oFilter)
    {
        $this->oFilter = $oFilter;
    }
}
