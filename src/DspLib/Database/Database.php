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

use DspLib\Config;
use DspLib\DataSource\DataSourceFilter;

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
    private static $instances = array();

    /**
     * Connection parameters
     *
     * @var array
     */
    protected $params = array();

    /**
     * Returns the instance of the asked connection
     *
     * @param string $name Name of the configuration parameter
     *
     * @return Database
     *
     * @throws \Exception
     */
    public static function getInstance($name = 'database')
    {
        if (!isset(self::$instances[$name])) {
            // Fetch the driver type from config
            $config = Config::getInstance();
            $params = $config->getParam($name);

            if (!isset($params)) {
                throw new \Exception('Cannot find config param ' . $name);
            }

            if (!isset($params['driver'])) {
                $driver = 'PDO';
            } else {
                $driver = $params['driver'];
            }

            // Loading of Database class
            $namespace = '\\DspLib\\Database\\'.$driver.'\\';
            $className = $namespace . 'Database';

            if (!class_exists($className)) {
                $message = "Could not find Database class for driver '$driver' (class '$className')";
                throw new \Exception($message);
            }

            // Loading of DbResult class
            $classNameDbResult = $namespace . 'DbResult';

            if (!class_exists($classNameDbResult)) {
                $message = "Could not find DbResult class for driver '$driver' (class '$classNameDbResult')";
                throw new \Exception($message);
            }

            // Everything's fine, we can load the instance
            self::$instances[$name] = new $className($name);
        }
        return self::$instances[$name];
    }

    /**
     * Loads the configuration parameters
     *
     * @param string $name Name of the configuration parameter which holds the connection infos
     *
     * @throws \Exception
     */
    public function __construct($name)
    {
        $config = Config::getInstance();
        $params = $config->getParam($name);

        if (!isset($params)) {
            throw new \Exception('Cannot find config param ' . $name);
        }
        $this->params = $params;
    }

    /**
     * Returns the connection parameters
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Performs a query and returns the result as a DbResult instance.
     *
     * @param string           $query  SQL query
     * @param DataSourceFilter $filter Filter (optional)
     *
     * @return \DspLib\Database\DbResult
     *
     * @throws \Exception
     */
    abstract public function query($query, DataSourceFilter $filter = null);

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
     * @param string $string The string to escape
     *
     * @return string
     */
    abstract public function escapeString($string);
}
