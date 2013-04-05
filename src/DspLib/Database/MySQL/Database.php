<?php

namespace DspLib\Database\MySQL;

/**
 * Implémentation d'une base de données MySQL avec l'API mysql de base de PHP
 *
 * @author deuspi
 * @since 22 oct. 2011 20:49:07
 */

class Database extends \DspLib\Database\Database
{

    /**
     * Représente l'instance de la connexion MySQL
     *
     * @var resource
     */
    private $mLink = null;

    public function __construct($sName)
    {
        parent::__construct($sName);

        // Création de la connexion
        $this->mLink = mysql_connect($this->aParams['host'], $this->aParams['login'], $this->aParams['password']);

        // Sélection de la base de données
        mysql_select_db($this->aParams['dbname'], $this->mLink);
    }

    /**
     * Effectue une requête et renvoie le résultat sous forme de DbResult
     *
     * @param string $sQuery Requête SQL
     * @param \DspLib\DataSource\DataSourceFilter $oFilter Filtre (facultatif)
     *
     * @return \DspLib\Database\DbResult
     */
    public function query($sQuery, \DspLib\DataSource\DataSourceFilter $oFilter = null)
    {
        //On ajoute les filtres éventuels
        if (isset($oFilter)) {
            $sQuery = "SELECT * FROM ($sQuery) AS zz_result1";
            $aFilters = $oFilter->getFilters();
            $bFirst = true;
            $sLimit = '';
            foreach ($aFilters as $aFilter) {

                if ($aFilter['sign'] != \DspLib\DataSource\DataSourceFilter::SIGN_LIMIT) {
                    if ($bFirst) {
                        $sQuery .= " WHERE ";
                        $bFirst = false;
                    } else {
                        $sQuery .= " AND ";
                    }
                }

                switch ($aFilter['sign']) {
                    case \DspLib\DataSource\DataSourceFilter::SIGN_BETWEEN:
                        $sQuery .= $aFilter['field'] . " BETWEEN " . $this->escapeString($aFilter['value']);
                        $sQuery .= " AND " . $this->escapeString($aFilter['value2']);
                        break;
                    case \DspLib\DataSource\DataSourceFilter::SIGN_LIMIT:
                        $sLimit = " LIMIT " . $aFilter['value'] . ", " . $aFilter['value2'];
                        break;
                    case \DspLib\DataSource\DataSourceFilter::SIGN_CONTENT:
                        $sEscapedValue = mysql_real_escape_string($aFilter['value']);
                        $sQuery .= $aFilter['field'] . " LIKE '%" . $sEscapedValue . "%'";
                        break;
                    case \DspLib\DataSource\DataSourceFilter::SIGN_NOTCONTENT:
                        $sEscapedValue = mysql_real_escape_string($aFilter['value']);
                        $sQuery .= $aFilter['field'] . " NOT LIKE '%" . $sEscapedValue . "%'";
                        break;
                    case \DspLib\DataSource\DataSourceFilter::SIGN_ISNULL:
                        $sQuery .= $aFilter['field'] . " IS NULL";
                        break;
                    case \DspLib\DataSource\DataSourceFilter::SIGN_ISNOTNULL:
                        $sQuery .= $aFilter['field'] . " IS NOT NULL";
                        break;
                    default:
                        $sQuery .= $aFilter['field'] . " " . $aFilter['sign'] . " ";
                        $sQuery .= $this->escapeString($aFilter['value']);
                }
            }

            $sQuery .= $sLimit;
        }

        //On modifie les requetes de type SELECT pour avoir automatiquement le nombre total de lignes hors limit
        $iNbTotalRows = 0;

        $sQuery = preg_replace('/^SELECT/', 'SELECT SQL_CALC_FOUND_ROWS', trim($sQuery));

        if (!$mResults = mysql_query($sQuery, $this->mLink)) {
            $sMessage = "Database : Erreur de requête";
            $sMessage .= PHP_EOL . "Message : " . mysql_error($this->mLink);
            $sMessage .= PHP_EOL . "Requête : " . PHP_EOL . $sQuery;
            throw new \Exception($sMessage);
        }

        // Si on a reçu TRUE c'est que c'était du INSERT/UPDATE/DELETE
        if ($mResults === true) {
            return mysql_affected_rows($this->mLink);
        }

        //Si y'a bien un SQL_CALC_FOUND_ROWS dans le select, on récupère le nombre d'enregistrements
        if (strpos($sQuery, 'SQL_CALC_FOUND_ROWS') !== false) {
            $oRez = mysql_query('SELECT FOUND_ROWS() as NB');
            $aRecordSet = mysql_fetch_array($oRez);

            $iNbTotalRows = $aRecordSet['NB'];
        }
        return new DbResult($mResults, $iNbTotalRows);
    }

    public function beginTransaction()
    {
        mysql_query('BEGIN TRANSACTION', $this->mLink);
        return true;
    }

    public function commitTransaction()
    {
        mysql_query('COMMIT', $this->mLink);
        return true;
    }

    public function rollbackTransaction()
    {
        mysql_query('ROLLBACK', $this->mLink);
        return true;
    }

    public function getLastInsertId()
    {
        return mysql_insert_id($this->mLink);
    }

    public function escapeString($sString)
    {
        return "'" . mysql_real_escape_string($sString) . "'";
    }
}
