<?php

/**
 * PDO Database class file.
 *
 * Database implementation for PDO API.
 *
 * @package    DspLib
 * @subpackage Database
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since      22 oct. 2011 20:49:07
 */

namespace DspLib\Database\PDO;

use DspLib\DataSource\DataSourceFilter;

/**
 * PDO Database class.
 *
 * Database implementation for PDO API.
 *
 * TODO Only MySQL is covered right now.
 *
 * @package    DspLib
 * @subpackage Database
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since      22 oct. 2011 20:49:07
 */
class Database extends \DspLib\Database\Database
{

    /**
     * PDO connection
     *
     * @var \PDO
     */
    private $link = null;

    public function __construct($name)
    {
        parent::__construct($name);

        // Initialization of the PDO connection
        $dsn = 'mysql:host=' . $this->params['host'] . ';dbname=' . $this->params['dbname'];
        $options = array(
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
        );
        $this->link = new \PDO($dsn, $this->params['login'], $this->params['password'], $options);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \DspLib\Database\Database::query()
     */
    public function query($query, DataSourceFilter $filter = null)
    {
        try {
            $stmt = $this->link->prepare($query);
            /*
            foreach ($params as $key => $value) {
                if (is_object($value)) {
                    var_dump($value);
                    debug_print_backtrace();
                    die();
                }
                $stmt->bindValue($key, $value);
            }
            */
            $stmt->execute();

            return new DbResult($stmt, 0);

        } catch (\PDOException $e) {
            $message = "Database : Erreur de requête";
            $message .= PHP_EOL . "Message : " . $e->getMessage();
            $message .= PHP_EOL . "Requête : " . PHP_EOL . $query;
            //$message .= PHP_EOL . "Params : " . PHP_EOL . var_export($params, true);
            throw new \Exception($message, 0, $e);
        }
    }

    /**
     * (non-PHPdoc)
     *
     * @see \DspLib\Database\Database::beginTransaction()
     */
    public function beginTransaction()
    {
        return $this->link->beginTransaction();
    }

    /**
     * (non-PHPdoc)
     *
     * @see \DspLib\Database\Database::commitTransaction()
     */
    public function commitTransaction()
    {
        return $this->link->commit();
    }

    /**
     * (non-PHPdoc)
     *
     * @see \DspLib\Database\Database::rollbackTransaction()
     */
    public function rollbackTransaction()
    {
        return $this->link->rollBack();
    }

    /**
     * (non-PHPdoc)
     *
     * @see \DspLib\Database\Database::getLastInsertId()
     */
    public function getLastInsertId()
    {
        return $this->link->lastInsertId();
    }

    /**
     * (non-PHPdoc)
     *
     * @see \DspLib\Database\Database::escapeString()
     */
    public function escapeString($string)
    {
        return $this->link->quote($string);
    }
}
