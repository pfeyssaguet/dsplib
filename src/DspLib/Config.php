<?php

namespace DspLib;

/**
 * Gestion de la configuration
 *
 * @author Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since 22 oct. 2011 19:56:54
 */

class Config
{
    private static $oInstance = null;

    /**
     * Renvoie le singleton
     *
     * @return Config
     */
    public static function getInstance()
    {
        if (!isset(self::$oInstance)) {
            self::$oInstance = new self();
        }

        return self::$oInstance;
    }

    /**
     * Constructeur privé car singleton (passer par getInstance)
     */
    private function __construct()
    {

    }

    /**
     * Définit un paramètre
     *
     * @param string $sName Nom du paramètre
     * @param mixed $mValue Valeur du paramètre
     *
     * @return void
     */
    public function setParam($sName, $mValue)
    {
        $this->aOptions[$sName] = $mValue;
    }

    /**
     * Renvoie un paramètre
     *
     * @param string $sName Nom du paramètre
     * @param string $sDefault Valeur à renvoyer si le paramètre n'est pas défini
     *
     * @return mixed
     */
    public function getParam($sName, $sDefault = null)
    {
        if (isset($this->aOptions[$sName])) {
            return $this->aOptions[$sName];
        }

        if (isset($sDefault)) {
            return $sDefault;
        }

        return null;
    }
}
