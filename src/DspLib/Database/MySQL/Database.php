<?php

/**
 * MySQL Database class file
 *
 * @author   Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since    22 oct. 2011 20:49:07
 */

namespace DspLib\Database\MySQL;

use DspLib\DataSource\DataSourceFilter;

/**
 * MySQL Database class
 *
 * Standard mysql PHP API implementation
 */

class Database extends \DspLib\Database\Database
{

    /**
     * MySQL connection resource
     *
     * @var resource
     */
    private $rLink = null;

    /**
     * Creates the connection and selects the schema
     *
     * @param string $sName Config parameter name
     *
     * @return void
     */
    public function __construct($sName)
    {
        parent::__construct($sName);

        // Connection
        $this->rLink = mysql_connect(
            $this->aParams['host'],
            $this->aParams['login'],
            $this->aParams['password']
		);

        // Schema selection
        mysql_select_db($this->aParams['dbname'], $this->rLink);
    }

    /**
     * Performs a query and returns the result as a DbResult instance
     *
     * @param string           $sQuery  SQL query
     * @param DataSourceFilter $oFilter Filter (optional)
     *
     * @return \DspLib\Database\DbResult
     */
    public function query($sQuery, DataSourceFilter $oFilter = null)
    {
        // Add filter if applicable
        if (isset($oFilter)) {
            $sQuery = "SELECT * FROM ($sQuery) AS zz_result1";
            $aFilters = $oFilter->getFilters();
            $bFirst = true;
            $sLimit = '';
            foreach ($aFilters as $aFilter) {

                if ($aFilter['sign'] != DataSourceFilter::SIGN_LIMIT) {
                    if ($bFirst) {
                        $sQuery .= " WHERE ";
                        $bFirst = false;
                    } else {
                        $sQuery .= " AND ";
                    }
                }

                switch ($aFilter['sign']) {
                    case DataSourceFilter::SIGN_BETWEEN:
                        $sQuery .= $aFilter['field'] . " BETWEEN ";
                        $sQuery .= $this->escapeString($aFilter['value']);
                        $sQuery .= " AND " . $this->escapeString($aFilter['value2']);
                        break;
                    case DataSourceFilter::SIGN_LIMIT:
                        $sLimit = " LIMIT " . $aFilter['value'];
                        $sLimit .= ", " . $aFilter['value2'];
                        break;
                    case DataSourceFilter::SIGN_CONTENT:
                        $sEscapedValue = mysql_real_escape_string($aFilter['value']);
                        $sQuery .= $aFilter['field'] . " LIKE ";
                        $sQuery .= "'%" . $sEscapedValue . "%'";
                        break;
                    case DataSourceFilter::SIGN_NOTCONTENT:
                        $sEscapedValue = mysql_real_escape_string($aFilter['value']);
                        $sQuery .= $aFilter['field'] . " NOT LIKE '%" . $sEscapedValue . "%'";
                        break;
                    case DataSourceFilter::SIGN_ISNULL:
                        $sQuery .= $aFilter['field'] . " IS NULL";
                        break;
                    case DataSourceFilter::SIGN_ISNOTNULL:
                        $sQuery .= $aFilter['field'] . " IS NOT NULL";
                        break;
                    default:
                        $sQuery .= $aFilter['field'] . " " . $aFilter['sign'] . " ";
                        $sQuery .= $this->escapeString($aFilter['value']);
                }
            }

            $sQuery .= $sLimit;
        }

        // Modify SELECT queries to get the total number of rows without the limit
        $iNbTotalRows = 0;

        $sQuery = preg_replace('/^SELECT/', 'SELECT SQL_CALC_FOUND_ROWS', trim($sQuery));

        if (!$mResults = mysql_query($sQuery, $this->rLink)) {
            $sMessage = "Database : Query error";
            $sMessage .= PHP_EOL . "Message : " . mysql_error($this->rLink);
            $sMessage .= PHP_EOL . "Query : " . PHP_EOL . $sQuery;
            throw new \Exception($sMessage);
        }

        // if the query returns true then it was INSERT/UPDATE/DELETE
        if ($mResults === true) {
            return mysql_affected_rows($this->rLink);
        }

        // If we had SQL_CALC_FOUND_ROWS in the SELECT, we fetch the rows number
        if (strpos($sQuery, 'SQL_CALC_FOUND_ROWS') !== false) {
            $oRez = mysql_query('SELECT FOUND_ROWS() as NB');
            $aRecordSet = mysql_fetch_array($oRez);

            $iNbTotalRows = $aRecordSet['NB'];
        }
        return new DbResult($mResults, $iNbTotalRows);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \DspLib\Database\Database::beginTransaction()
     */
    public function beginTransaction()
    {
        mysql_query('BEGIN TRANSACTION', $this->rLink);
        return true;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \DspLib\Database\Database::commitTransaction()
     */
    public function commitTransaction()
    {
        mysql_query('COMMIT', $this->rLink);
        return true;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \DspLib\Database\Database::rollbackTransaction()
     */
    public function rollbackTransaction()
    {
        mysql_query('ROLLBACK', $this->rLink);
        return true;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \DspLib\Database\Database::getLastInsertId()
     */
    public function getLastInsertId()
    {
        return mysql_insert_id($this->rLink);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \DspLib\Database\Database::escapeString()
     */
    public function escapeString($sString)
    {
        return "'" . mysql_real_escape_string($sString) . "'";
    }
}
