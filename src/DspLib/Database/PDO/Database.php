<?php

/**
 * PDO implementation for database access
 *
 * @package    DspLib
 * @subpackage Database
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since      22 oct. 2011 20:49:07
 */

namespace DspLib\Database\PDO;

use DspLib\DataSource\DataSourceFilter;

/**
 * PDO implementation for database access
 *
 * TODO Only MySQL is covered right now
 *
 * @package    DspLib
 * @subpackage Database
 * @author     Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since      22 oct. 2011 20:49:07
 */
class Database extends \DspLib\Database\Database
{

    /**
     * Représente l'instance de la connexion PDO
     *
     * @var \PDO
     */
    private $link = null;

    public function __construct($sName)
    {
        parent::__construct($sName);

        // Création de la connexion PDO
        $dsn = 'mysql:host=' . $this->params['host'] . ';dbname=' . $this->params['dbname'];
        $options = array(
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
        );
        $this->link = new \PDO($dsn, $this->params['login'], $this->params['password'], $options);
    }

    /**
     *
     * Effectue une requête et renvoie le résultat sous forme de DbResult
     *
     * @param string $query Requête SQL
     * @param DataSourceFilter $filter Filtre (facultatif)
     *
     * @return \DspLib\Database\DbResult
     * @throws \Exception
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

    public function beginTransaction()
    {
        return $this->link->beginTransaction();
    }

    public function commitTransaction()
    {
        return $this->link->commit();
    }

    public function rollbackTransaction()
    {
        return $this->link->rollBack();
    }

    public function getLastInsertId()
    {
        return $this->link->lastInsertId();
    }

    public function escapeString($string)
    {
        return $this->link->quote($string);
    }
}
