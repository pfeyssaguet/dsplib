<?php

namespace DspLib;

class Language
{

    private static $bDebug = false;

    private static $sDefaultLanguage = 'fr';

    private static $sCurrentLanguage = 'fr';

    private static $oInstance = null;

    private static $aPaths = array();

    private $aLanguages = array();

    public static function setDebug($bDebug)
    {
        self::$bDebug = $bDebug;
    }

    public static function addPath($sPath)
    {
        self::$aPaths[] = $sPath;
    }

    public static function getDefaultLanguage()
    {
        return self::$sDefaultLanguage;
    }

    public static function setDefaultLanguage($sLanguage)
    {
        self::$sDefaultLanguage = $sLanguage;
    }

    public static function getCurrentLanguage()
    {
        return self::$sCurrentLanguage;
    }

    public static function setCurrentLanguage($sLanguage)
    {
        self::$sCurrentLanguage = $sLanguage;
    }

    /**
     * Renvoie le singleton
     *
     * @return Language
     */
    public static function getInstance()
    {
        if (!isset(self::$oInstance)) {
            self::$oInstance = new self();
        }
        return self::$oInstance;
    }

    public static function getString($sName, $sLanguage = null)
    {
        $oLang = self::getInstance();
        return $oLang->getLanguageString($sName, $sLanguage);
    }

    private function __construct()
    {

    }

    private function loadLanguageFiles()
    {
        foreach (self::$aPaths as $sPath) {
            $aFiles = FileUtils::getFiles($sPath);
            foreach ($aFiles as $sFile) {
                $this->loadLanguageFile($sFile);
            }
        }
    }

    private function loadLanguageFile($sFile)
    {
        $sCode = substr(basename($sFile), 0, strlen(basename($sFile)) - 4);
        $sPath = dirname($sFile);
        if (isset($this->aLanguages[$sCode]) && isset($this->aLanguages[$sCode][$sPath])) {
            return;
        }

        $oDoc = new \DOMDocument();
        $oDoc->load($sFile);
        $oDocRoot = $oDoc->documentElement;

        $sName = $oDocRoot->getAttribute('name');
        if (!isset($this->aLanguages[$sCode])) {
            $this->aLanguages[$sCode] = array();
        }
        $this->aLanguages[$sCode][$sPath] = array(
            'file' => $sFile,
            'code' => $sCode,
            'name' => $sName,
            'terms' => array(),
        );

        $oNodesTerms = $oDocRoot->getElementsByTagName('term');

        for ($i = 0; $i < $oNodesTerms->length; $i++) {
            $oElTerm = $oNodesTerms->item($i);

            $sKey = $oElTerm->getAttribute('name');
            $sVal = $oElTerm->nodeValue;

            $this->aLanguages[$sCode][$sPath]['terms'][$sKey] = $sVal;
        }

    }

    private function loadLanguage($sLanguage)
    {
        foreach (self::$aPaths as $sPath) {
            $sFile = $sPath . '/' . $sLanguage . '.xml';

            if (!is_readable($sFile)) {
                throw new \Exception("Language error : language '$sLanguage' does not exists in path $sPath");
            }

            $this->loadLanguageFile($sFile);
        }
    }

    private function getLanguageString($sName, $sLanguage = null)
    {
        if (!isset($sLanguage) || empty($sLanguage)) {
            if (isset(self::$sCurrentLanguage)) {
                $sLanguage = self::$sCurrentLanguage;
            } else {
                $sLanguage = self::$sDefaultLanguage;
            }
        }

        // TODO bout de scotch pour choper le terme dans la bonne langue
        if (isset($this->aLanguages[$sLanguage])) {
            foreach ($this->aLanguages[$sLanguage] as $aPath) {
                if (isset($aPath['terms'][$sName])) {
                    return $aPath['terms'][$sName];
                }
            }
        }

        if (!isset($this->aLanguages[$sLanguage]) || count($this->aLanguages[$sLanguage]) != count(self::$aPaths)) {
            try {
                $this->loadLanguage($sLanguage);
            } catch (\Exception $e) {
                // on peut pas charger le langage demandé, on essaie avec celui par défaut
                if ($sLanguage != self::$sDefaultLanguage) {
                    $sBuggyLanguage = $sLanguage;
                    $sLanguage = self::$sDefaultLanguage;

                    // ce coup-ci, si ça foire on laisse péter l'exception (si on est en mode debug)
                    try {
                        $this->loadLanguage($sLanguage);
                    } catch (\Exception $e) {
                        if (self::$bDebug) {
                            // on est en mode debug, on lance l'exception
                            throw $e;
                        } else {
                            // on est pas en mode debug, supposément en prod, donc on se contente d'un notice
                            // et on renvoie la chaîne d'origine

                            // bon ça a pas pété mais on va quand même sortir un ptit notice
                            $sMessage = "Language notice : ";
                            $sMessage .= "missing term '$sName' in language '$sBuggyLanguage' and default language";
                            trigger_error($sMessage, E_USER_NOTICE);
                            return $sName;
                        }
                    }

                    // bon ça a pas pété mais on va quand même sortir un ptit notice
                    // TODO ou pas : c'est laid ça fait plein de notices :s trouver mieux !
                    // $sMessage = "Language notice : ";
                    // $sMessage .= "missing term '$sName' in language '$sBuggyLanguage'. ";
                    // $sMessage .= "Using default language instead";
                    //trigger_error($sMessage, E_USER_NOTICE);
                } else {
                    // c'était déjà le language par défaut.. ok on relance l'exception
                    throw $e;
                }
            }
        }

        if (!isset($this->aLanguages[$sLanguage])) {
            throw new \Exception("Language error : unable to load language '$sLanguage'");
        }

        $sTerm = '';
        $bFound = false;
        foreach ($this->aLanguages[$sLanguage] as $aPath) {
            if (isset($aPath['terms'][$sName])) {
                $bFound = true;
                $sTerm = $aPath['terms'][$sName];
                break;
            }
        }

        if (!$bFound) {
            if (self::$bDebug) {
                // en mode debug, si on ne trouve pas un terme, on lance une exception
                throw new \Exception("Language error : term '$sName' does not exists (language '$sLanguage')");
            } else {
                // en mode prod, on ne pète pas l'exécution pour ça.. on sort un notice
                trigger_error("Language error : term '$sName' does not exists (language '$sLanguage')", E_USER_NOTICE);

                // et on redéfinit le paramètre avec sa propre valeur histoire d'avoir quand même quelque chose
                $sTerm = $sName;
            }
        }

        return $sTerm;
    }
}
