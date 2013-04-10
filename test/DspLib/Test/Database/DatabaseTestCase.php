<?php

namespace DspLib\Test\Database;

abstract class DatabaseTestCase extends \PHPUnit_Extensions_Database_TestCase
{
    // instancie pdo seulement une fois pour le nettoyage du test/le chargement de la fixture
    static private $pdo = null;

    // instancie PHPUnit_Extensions_Database_DB_IDatabaseConnection seulement une fois par test
    private $conn = null;

    final public function getConnection()
    {
        if ($this->conn === null) {
            if (self::$pdo == null) {
                $dsn = "mysql:dbname=$GLOBALS[db_name];host=$GLOBALS[db_host]";
                self::$pdo = new \PDO($dsn, $GLOBALS['db_login'], $GLOBALS['db_password']);
                self::$pdo->exec(
                    "CREATE TABLE table1 (
                        idtable1 INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                        name VARCHAR(32) NOT NULL DEFAULT '',
                        value VARCHAR(32) NOT NULL DEFAULT '',
                        PRIMARY KEY (idtable1)
                    )"
                );
                self::$pdo->exec(
                    "CREATE TABLE `table1_innodb` (
                        `idtable1` int(11) unsigned NOT NULL AUTO_INCREMENT,
                        `name` varchar(32) NOT NULL DEFAULT '',
                        `value` varchar(32) NOT NULL DEFAULT '',
                        PRIMARY KEY (`idtable1`)
                    ) ENGINE=InnoDB"
                );
            }
            $this->conn = $this->createDefaultDBConnection(self::$pdo, $GLOBALS['db_name']);
        }

        return $this->conn;
    }

    public function getDataSet()
    {
        return $this->createMySQLXMLDataSet(__DIR__ . '/dataset.xml');
    }
}
