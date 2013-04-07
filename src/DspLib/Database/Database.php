<?php

namespace DspLib\Database;

/**
 * Classe d'accès à une base de données
 *
 * @author Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since 13 avr. 2011 22:39:00
 */

abstract class Database
{
    /**
     * Le tableau de singletons (multiton)
     *
     * @var array[Database]
     */
    private static $aoInstances = array();

    /**
     * Paramètres de connexion
     *
     * @var array
     */
    protected $aParams = array();

    /**
     * Renvoie l'instance de la connexion demandée
     *
     * @param string $sName Nom de l'index à récupérer dans la config
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

    public function __construct($sName)
    {
        // Récupération de la config
        $oConfig = \DspLib\Config::getInstance();
        $aParams = $oConfig->getParam($sName);

        if (!isset($aParams)) {
            throw new \Exception('Cannot find config param ' . $sName);
        }
        $this->aParams = $aParams;
    }

    /**
     * Renvoie les paramètres de connexion
     *
     * @return array
     */
    public function getParams()
    {
        return $this->aParams;
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
    abstract public function query($sQuery, \DspLib\DataSource\DataSourceFilter $oFilter = null);

    abstract public function beginTransaction();

    abstract public function commitTransaction();

    abstract public function rollbackTransaction();

    abstract public function getLastInsertId();

    abstract public function escapeString($sString);
}
