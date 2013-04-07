<?php

namespace DspLib\Database\PDO;

/**
 * Implémentation de PDO pour l'accès à une base de données
 *
 * TODO Ne gère que MySQL pour l'instant
 *
 * @author Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since 22 oct. 2011 20:49:07
 */

class Database extends \DspLib\Database\Database
{

    /**
     * Représente l'instance de la connexion PDO
     *
     * @var PDO
     */
    private $oLink = null;

    /**
     * Commentaire du schéma de base de données
     *
     * @var string
     */
    private $sComment = '';

    public function __construct($sName)
    {
        parent::__construct($sName);

        // Création de la connexion PDO
        $sDSN = 'mysql:host=' . $this->aParams['host'] . ';dbname=' . $this->aParams['dbname'];
        $aOptions = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
        );
        $this->oLink = new \PDO($sDSN, $this->aParams['login'], $this->aParams['password'], $aOptions);
    }

    public function getHost()
    {
        return $this->sHost;
    }

    public function getSchema()
    {
        return $this->sSchema;
    }

    /**
     *
     * Effectue une requête et renvoie le résultat sous forme de DbResult
     *
     * @param string $sQuery Requête SQL
     * @param \DspLib\DataSource\DataSourceFilter $oFilter Filtre (facultatif)
     *
     * @return \DspLib\Database\DbResult
     */
    public function query($sQuery, \DspLib\DataSource\DataSourceFilter $oFilter = null)
    {
        try {
            $oStmt = $this->oLink->prepare($sQuery);
            foreach ($aParams as $sKey => $sValue) {
                if (is_object($sValue)) {
                    var_dump($sValue);
                    debug_print_backtrace();
                    die();
                }
                $oStmt->bindValue($sKey, $sValue);
            }
            $oStmt->execute();

            return new DbResultPdo($oStmt, 0);

        } catch (PDOException $e) {
            $sMessage = "Database : Erreur de requête";
            $sMessage .= PHP_EOL . "Message : " . $e->getMessage();
            $sMessage .= PHP_EOL . "Requête : " . PHP_EOL . $sQuery;
            $sMessage .= PHP_EOL . "Params : " . PHP_EOL . var_export($aParams, true);
            throw new \Exception($sMessage, 0, $e);
        }
    }

    public function beginTransaction()
    {
        return $this->oLink->beginTransaction();
    }

    public function commitTransaction()
    {
        return $this->oLink->commit();
    }

    public function rollbackTransaction()
    {
        return $this->oLink->rollBack();
    }

    public function getLastInsertId()
    {
        return $this->oLink->lastInsertId();
    }

    public function escapeString($sString)
    {
        return $this->oLink->quote($sString);
    }
}
