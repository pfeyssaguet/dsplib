<?php

/**
 * MySQL Database class file.
 *
 * Database implementation for MySQL standard API.
 * The standard API becomes deprecated as of PHP 5.5 so it should be discarded in profit of the PDO one.
 *
 * @package    DspLib
 * @subpackage Database
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since      22 oct. 2011 20:49:07
 */

namespace DspLib\Database\MySQL;

use DspLib\DataSource\DataSourceFilter;

/**
 * MySQL Database class.
 *
 * Standard mysql PHP API implementation
 *
 * @package    DspLib
 * @subpackage Database
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since      22 oct. 2011 20:49:07
 */

class Database extends \DspLib\Database\Database
{

    /**
     * MySQL connection resource
     *
     * @var resource
     */
    private $link = null;

    /**
     * Creates the connection and selects the schema.
     *
     * @param string $name Config parameter name
     */
    public function __construct($name)
    {
        parent::__construct($name);

        // Connection
        $this->link = mysql_connect(
            $this->params['host'],
            $this->params['login'],
            $this->params['password']
        );

        // Schema selection
        mysql_select_db($this->params['dbname'], $this->link);
    }

    /**
     * Prepares the query.
     *
     * @param string           $query  SQL query
     * @param DataSourceFilter $filter Filter (optional)
     *
     * @return string
     */
    private function prepareQuery($query, DataSourceFilter $filter = null)
    {
        // Add filter if applicable
        if (isset($filter)) {
            $query = "SELECT * FROM ($query) AS zz_result1";
            $filters = $filter->getFilters();
            $isFirst = true;
            $limit = '';
            foreach ($filters as $filterArray) {

                if ($filterArray['sign'] != DataSourceFilter::SIGN_LIMIT) {
                    if ($isFirst) {
                        $query .= " WHERE ";
                        $isFirst = false;
                    } else {
                        $query .= " AND ";
                    }
                }

                switch ($filterArray['sign']) {
                    case DataSourceFilter::SIGN_BETWEEN:
                        $query .= $filterArray['field'] . " BETWEEN ";
                        $query .= $this->escapeString($filterArray['value']);
                        $query .= " AND " . $this->escapeString($filterArray['value2']);
                        break;
                    case DataSourceFilter::SIGN_LIMIT:
                        $limit = " LIMIT " . $filterArray['value'];
                        $limit .= ", " . $filterArray['value2'];
                        break;
                    case DataSourceFilter::SIGN_CONTENT:
                        $escapedValue = mysql_real_escape_string($filterArray['value']);
                        $query .= $filterArray['field'] . " LIKE ";
                        $query .= "'%" . $escapedValue . "%'";
                        break;
                    case DataSourceFilter::SIGN_NOTCONTENT:
                        $escapedValue = mysql_real_escape_string($filterArray['value']);
                        $query .= $filterArray['field'] . " NOT LIKE '%" . $escapedValue . "%'";
                        break;
                    case DataSourceFilter::SIGN_ISNULL:
                        $query .= $filterArray['field'] . " IS NULL";
                        break;
                    case DataSourceFilter::SIGN_ISNOTNULL:
                        $query .= $filterArray['field'] . " IS NOT NULL";
                        break;
                    default:
                        $query .= $filterArray['field'] . " " . $filterArray['sign'] . " ";
                        $query .= $this->escapeString($filterArray['value']);
                }
            }

            $query .= $limit;
        }

        // Modify SELECT queries to get the total number of rows without the limit
        if (strpos($query, 'FROM')) {
            $query = preg_replace('/^SELECT/', 'SELECT SQL_CALC_FOUND_ROWS', trim($query));
        }

        return $query;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \DspLib\Database\Database::query()
     */
    public function query($query, DataSourceFilter $filter = null)
    {
        $query = $this->prepareQuery($query, $filter);
        $nbTotalRows = 0;

        if (!$results = mysql_query($query, $this->link)) {
            $message = "Database : Query error";
            $message .= PHP_EOL . "Message : " . mysql_error($this->link);
            $message .= PHP_EOL . "Query : " . PHP_EOL . $query;
            throw new \Exception($message);
        }

        // if the query returns true then it was INSERT/UPDATE/DELETE
        if ($results === true) {
            return mysql_affected_rows($this->link);
        }

        // If we had SQL_CALC_FOUND_ROWS in the SELECT, we fetch the rows number
        if (strpos($query, 'SQL_CALC_FOUND_ROWS') !== false) {
            $rez = mysql_query('SELECT FOUND_ROWS() as NB');
            $recordset = mysql_fetch_array($rez);

            $nbTotalRows = $recordset['NB'];
        }
        return new DbResult($results, $nbTotalRows);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \DspLib\Database\Database::beginTransaction()
     */
    public function beginTransaction()
    {
        mysql_query('SET AUTOCOMMIT = 0', $this->link);
        mysql_query('BEGIN TRANSACTION', $this->link);
        return true;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \DspLib\Database\Database::commitTransaction()
     */
    public function commitTransaction()
    {
        mysql_query('COMMIT', $this->link);
        mysql_query('SET AUTOCOMMIT = 1', $this->link);
        return true;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \DspLib\Database\Database::rollbackTransaction()
     */
    public function rollbackTransaction()
    {
        mysql_query('ROLLBACK', $this->link);
        mysql_query('SET AUTOCOMMIT = 1', $this->link);
        return true;
    }

    /**
     * (non-PHPdoc)
     *
     * @see \DspLib\Database\Database::getLastInsertId()
     */
    public function getLastInsertId()
    {
        return mysql_insert_id($this->link);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \DspLib\Database\Database::escapeString()
     */
    public function escapeString($string)
    {
        return "'" . mysql_real_escape_string($string, $this->link) . "'";
    }
}
