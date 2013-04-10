<?php

/**
 * Access class to a database
 *
 * @package    DspLib
 * @subpackage Database
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since      13 avr. 2011 22:39:00
 */

namespace DspLib\Database;

/**
 * Access class to a database
 *
 * @package    DspLib
 * @subpackage Database
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since      13 avr. 2011 22:39:00
 */
abstract class Database
{
    /**
     * Singleton table (multiton)
     *
     * @var array[Database]
     */
    private static $aoInstances = array();

    /**
     * Connection parameters
     *
     * @var array
     */
    protected $aParams = array();

    /**
     * Returns the instance of the asked connection
     *
     * @param string $sName Name of the configuration parameter
     * @return Database
     */
    public static function getInstance($sName = 'database')
    {
        if (!isset(self::$aoInstances[$sName])) {
            // Il faut repérer le type de driver
            $oConfig = \DspLib\Config::getInstance();
            $aParams = $oConfig->getParam($sName);

            if (!isset($aParams)) {
                throw new \Exception('Cannot find config param ' . $sName);
            }

            if (!isset($aParams['driver'])) {
                $sDriver = 'PDO';
            } else {
                $sDriver = $aParams['driver'];
            }

            // Chargement de la classe Database
            $sNamespace = '\\DspLib\\Database\\'.$sDriver.'\\';
            $sClassName = $sNamespace . 'Database';

            if (!class_exists($sClassName)) {
                throw new \Exception("Could not find Database class for driver '$sDriver' (class '$sClassName')");
            }

            // Chargement de la classe DbResult
            $sClassNameDbResult = $sNamespace . 'DbResult';

            if (!class_exists($sClassNameDbResult)) {
                $sMessage = "Could not find DbResult class for driver '$sDriver' (class '$sClassNameDbResult')";
                throw new \Exception($sMessage);
            }

            // Tout est ok, on peut charger l'instance
            self::$aoInstances[$sName] = new $sClassName($sName);
        }
        return self::$aoInstances[$sName];
    }

    /**
     * Loads the configuration parameters
     *
     * @param string $sName Name of the configuration parameter which holds the connection infos
     *
     * @throws \Exception
     */
    public function __construct($sName)
    {
        $oConfig = \DspLib\Config::getInstance();
        $aParams = $oConfig->getParam($sName);

        if (!isset($aParams)) {
            throw new \Exception('Cannot find config param ' . $sName);
        }
        $this->aParams = $aParams;
    }

    /**
     * Returns the connection parameters
     *
     * @return array
     */
    public function getParams()
    {
        return $this->aParams;
    }

    /**
     * Performs a query and returns the results as a DbResult instance
     *
     * @param string $sQuery SQL Query
     * @param \DspLib\DataSource\DataSourceFilter $oFilter Filter (optional)
     *
     * @return \DspLib\Database\DbResult
     */
    abstract public function query($sQuery, \DspLib\DataSource\DataSourceFilter $oFilter = null);

    /**
     * Begins an SQL transaction
     */
    abstract public function beginTransaction();

    /**
     * Commits a pending transaction
     */
    abstract public function commitTransaction();

    /**
     * Rollbacks a pending transaction
     */
    abstract public function rollbackTransaction();

    /**
     * Returns the last inserted id
     *
     * @return int
     */
    abstract public function getLastInsertId();

    /**
     * Escapes a string to make queries with
     *
     * @param string $sString The string to escape
     *
     * @return string
     */
    abstract public function escapeString($sString);
}
