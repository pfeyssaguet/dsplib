<?php

/**
 * Template class file
 *
 * Allows to use tpl files to replace blocks and variables (and much more...)
 *
 * @package DspLib
 * @author Pierre Feyssaguet <pfeyssaguet@gmail.com>
 */
namespace DspLib;

use DspLib\DataSource\DataSource;
use DspLib\DataSource\DataSourceArray;

/**
 * Template class
 *
 * Allows to use tpl files to replace blocks and variables (and much more...)
 *
 * @package DspLib
 * @author Pierre Feyssaguet <pfeyssaguet@gmail.com>
*/
class Template
{
    /**
     * Répertoire des templates
     *
     * @var string
     */
    private static $sRootPath = '';

    /**
     * Fonction de traitement des URL
     *
     * @var mixed
     */
    private static $mCallbackUrl = false;

    /**
     * Fonction de traitement des langues
     *
     * @var mixed
     */
    private static $mCallbackLang = false;

    /**
     * Fonction de traitement des dates
     *
     * @var mixed
     */
    private static $mCallbackDate = false;

    /**
     * Fonction de traitement des listes déroulantes
     *
     * @var mixed
     */
    private static $mCallbackSelect = false;

    /**
     * Liste des callbacks custom
     *
     * @var array
     */
    private static $mCallbackCustom = array();

    /**
     * Liste des paramètres par défaut à utiliser dans tous les templates
     *
     * @var array
     */
    private static $aDefaultParams = array();

    /**
     * Fichier du template
     *
     * @var string
     */
    private $sTemplateFile;

    /**
     * Tableau des paramètres de remplacement
     *
     * @var array
     */
    private $aParams = array();

    /**
     * Tableau des paramètres qui n'ont pas été utilisés
     *
     * @var array
     */
    private $aUnusedParams = array();

    /**
     * Tableau des listes
     *
     * @var array
     */
    private $aListes = array();

    /**
     * Contenu du template
     *
     * @var string
     */
    private $sData;

    /**
     * Expression régulière qui cible le contenu d'un bloc
     * @var string
     */
    const TOUS_LES_CARACTERES = '\d\s\w\p{L}\.\:\{\}\(\)\=\&\;\$éèêëàâäîïôöûü\/<>"!-_|';

    /**
     * Renvoie le répertoire des templates.
     *
     * @return string
     */
    public static function getRootPath()
    {
        if (strrpos(self::$sRootPath, '/') == strlen(self::$sRootPath)) {
            self::$sRootPath = substr(self::$sRootPath, strlen(self::$sRootPath) - 1);
        }
        return self::$sRootPath;
    }

    /**
     * Définit le répertoire des templates.
     *
     * @param string $sPath chemin
     *
     * @return void
     */
    public static function setRootPath($sPath)
    {
        self::$sRootPath = $sPath;
    }

    /**
     * Renvoie la fonction de traitement des URL.
     *
     * @return mixed
     */
    public static function getCallbackUrl()
    {
        return self::$mCallbackUrl;
    }

    /**
     * Définit la fonction de traitement des URL.
     *
     * @param mixed $mCallback fonction
     *
     * @return void
     */
    public static function setCallbackUrl($mCallback)
    {
        self::$mCallbackUrl = $mCallback;
    }

    /**
     * Renvoie la fonction de traitement des langues.
     *
     * @return mixed
     */
    public static function getCallbackLang()
    {
        return self::$mCallbackLang;
    }

    /**
     * Définit la fonction de traitement des langues.
     *
     * @param mixed $mCallback fonction
     *
     * @return void
     */
    public static function setCallbackLang($mCallback)
    {
        self::$mCallbackLang = $mCallback;
    }

    /**
     * Renvoie la fonction de traitement des dates.
     *
     * @return mixed
     */
    public static function getCallbackDate()
    {
        return self::$mCallbackDate;
    }

    /**
     * Définit la fonction de traitement des dates.
     *
     * @param mixed $mCallback fonction
     *
     * @return void
     */
    public static function setCallbackDate($mCallback)
    {
        self::$mCallbackDate = $mCallback;
    }

    /**
     * Renvoie la fonction de traitement des listes déroulantes.
     *
     * @return mixed
     */
    public static function getCallbackSelect()
    {
        return self::$mCallbackSelect;
    }

    /**
     * Définit la fonction de traitement des listes déroulantes.
     *
     * @param mixed $mCallback fonction
     *
     * @return void
     */
    public static function setCallbackSelect($mCallback)
    {
        self::$mCallbackSelect = $mCallback;
    }

    /**
     * Renvoie la fonction custom de callback correspondant à l'argument.
     *
     * @param string $sFunction nom de la fonction custom
     *
     * @return mixed
     */
    public static function getCallbackCustom($sFunction)
    {
        if (!array_key_exists($sFunction, self::$mCallbackCustom)) {
            return false;
        }

        return self::$mCallbackCustom[$sFunction];
    }

    /**
     * Ajoute une fonction custom.
     *
     * @param string $sFunction Nom de la fonction custom
     * @param mixed  $mCallback Callback de la fonction
     *
     * @return void
     */
    public static function addCallbackCustom($sFunction, $mCallback)
    {
        self::$mCallbackCustom[$sFunction] = $mCallback;
    }

    /**
     * Réinitialise la table des paramètres par défaut.
     *
     * @return void
     */
    public static function clearDefaultParams()
    {
        self::$aDefaultParams = array();
    }

    /**
     * Ajoute un paramètre par défaut à utiliser dans tous les templates.
     *
     * @param string $sParam Nom du paramètre
     * @param mixed  $mValue Valeur du paramètre
     *
     * @return void
     */
    public static function addDefaultParam($sParam, $mValue)
    {
        self::$aDefaultParams[$sParam] = $mValue;
    }

    /**
     * Formate le message d'erreur pour ajouter des informations.
     *
     * @param string $sMessage message d'erreur
     *
     * @return string
     */
    private function formatError($sMessage)
    {
        $sError = "[" . __CLASS__ . " ERROR] - ";

        $sError .= " - Template " . $this->getTemplateName() . " - ";

        $sError .= $sMessage;

        return $sError;
    }

    /**
     * Renvoie le nom du template
     *
     * @return string
     */
    public function getTemplateName()
    {
        if (isset($this->sTemplateFile)) {
            return str_replace($this->getRootPath(), '', $this->sTemplateFile);
        }
        return 'dynamique';
    }

    /**
     * Constructeur.
     *
     * Si un fichier de template est fourni, il est chargé
     *
     * @param string $sTemplateFile fichier de template à charger
     * @throws \InvalidArgumentException
     */
    public function __construct($sTemplateFile = null)
    {
        if (isset($sTemplateFile)) {
            $sPath = $sTemplateFile;
            if (substr($sPath, 0, 1) != '/' && self::getRootPath() != '') {
                $sPath = self::getRootPath() . '/' . $sPath;
            }

            $sPath = realpath($sPath);

            if (!is_readable($sPath)) {
                $sMessage = __CLASS__ . " Error";
                $sMessage .= " - Impossible de trouver la vue [" . basename($sTemplateFile) . "]";
                $sMessage .= " - chemin [$sPath]";
                throw new \InvalidArgumentException($sMessage);
            }

            $this->sTemplateFile = $sPath;
        }

        // on met en place les paramètres par défaut du template
        $this->aParams = self::$aDefaultParams;
    }

    /**
     * Définit tous les paramètres en une seule fois
     *
     * @param array $aParams Tableau des paramètres
     *
     * @return void
     */
    public function setParams(array $aParams)
    {
        $this->aParams = $aParams;
    }

    /**
     * Définit un paramètre à une valeur.
     *
     * @param string $sParam Nom du paramètre
     * @param mixed  $value  Valeur du paramètre
     *
     * @return void
     */
    public function setParam($sParam, $value)
    {
        $this->aParams[$sParam] = $value;
    }

    /**
     * Définit plusieurs paramètres en une seule fois
     *
     * @param array $aParams
     */
    public function addParams(array $aParams)
    {
        foreach ($aParams as $sKey => $mValue) {
            $this->setParam($sKey, $mValue);
        }
    }

    /**
     * Définit une liste pour les remplacements à utiliser avec la fonction {list:}.
     *
     * @param string $sParam Nom utilisé dans la liste
     * @param array  $aList  Contenu de la liste
     *
     * @return void
     */
    public function setList($sParam, array $aList)
    {
        $this->aListes[$sParam] = $aList;
    }

    /**
     * Effectue les remplacements pour les blocs répétés.
     *
     * Un bloc répété s'écrit de la manière suivante :
     * <code>
     * <!-- BEGIN monbloc -->
     * contenu du bloc répétitif
     * monbloc.champ1
     * monbloc.champ2
     * <!-- END monbloc -->
     * </code>
     *
     * Le paramètre correspondant doit être un tableau :
     * <code>
     * $param['monbloc'] = array(
     *     array(
     *         'champ1' => 'valeur1.1',
     *         'champ2' => 'valeur2.1'
     *     ),
     *     array(
     *         'champ1' => 'valeur1.2',
     *         'champ2' => 'valeur2.2'
     *     )
     * );
     * </code>
     *
     * @return void
     * @throws \InvalidArgumentException
     */
    private function parseTables()
    {
        $sBlocOuvrant = '<!-- BEGIN (?<table>[\w+\.]+) -->';
        $sBlocContenu = '(?<content>[' . self::TOUS_LES_CARACTERES . ']+)';
        $sBlocFermant = '<!-- END \1 -->';
        $sPattern = '/' . $sBlocOuvrant . $sBlocContenu . $sBlocFermant . '/U';
        if (preg_match_all($sPattern, $this->sData, $aMatches)) {
            foreach ($aMatches['table'] as $iKey => $sTable) {
                $sPatternSuppr = '/<!-- BEGIN ' . $sTable . ' -->([';
                $sPatternSuppr .= self::TOUS_LES_CARACTERES;
                $sPatternSuppr .= ']+)<!-- END ' . $sTable . ' -->/U';

                if (array_key_exists($sTable, $this->aParams)) {
                    $mTable = $this->aParams[$sTable];

                    // FIXME bug chelou qui empêche de fetcher directement à l'intérieur d'un DS
                    // en attendant on le pré-fetch
                    if ($mTable instanceof DataSource) {
                        $mTable = $mTable->getRows();
                    }

                    $sResult = '';
                    foreach ($mTable as $aRow) {
                        $sMessage = "Template '" . $this->getTemplateName() . "' : ";
                        $sMessage .= "création d'un sous-template pour la table " . $sTable;
                        Log::addInfo(__CLASS__, $sMessage);

                        $oSubView = new self();
                        // on ajoute le data et tous les arguments
                        $oSubView->setData($aMatches['content'][$iKey]);
                        $oSubView->setParams($this->aParams);

                        if (!is_array($aRow)) {
                            $errorMsg = $this->formatError('Param ' . $sTable . ' does not contain an array');
                            throw new \InvalidArgumentException($errorMsg);
                        }
                        foreach ($aRow as $sParamKey => $mParamValue) {
                            $sParamName = $sTable . '.' . $sParamKey;
                            $oSubView->setParam($sParamName, $mParamValue);
                        }

                        // on envoie les listes à la sub view
                        foreach ($this->aListes as $sKey => $aList) {
                            $oSubView->setList($sKey, $aList);
                        }

                        $sResult .= $oSubView->render();
                    }

                    // on procède au remplacement
                    $this->sData = preg_replace($sPatternSuppr, $sResult, $this->sData, 1);
                } else {
                    // Aucun paramètre pour cette table, on la supprime
                    $this->sData = preg_replace($sPatternSuppr, '', $this->sData, 1);
                }
            }
        }
    }

    /**
     * Effectue les remplacements pour les blocs conditionnels
     *
     * Un bloc conditionnel s'écrit de la manière suivante :
     * <!-- IF mavariable -->
     * contenu du bloc conditionnel
     * <!-- ELSE mavariable -->
     * contenu du bloc si la condition est fausse
     * <!-- ENDIF mavariable -->
     *
     * Il est également possible d'écrire IFNOT pour inverser le test
     *
     * Le bloc ne s'affichera que si la condition est vérifiée
     *
     * @return void
     */
    private function parseConditions()
    {
        // les expressions régulières c'est pas mon kif
        // alors je vais la décomposer

        $sBlocOuvrant = '<!-- +IF[NOT]* +(?P<variable>[' . self::TOUS_LES_CARACTERES . ']+) +-->';
        $sBlocContenu = '(?P<content>[' . self::TOUS_LES_CARACTERES . ']+)';
        $sBlocFermant = '<!-- +ENDIF +\1 +-->';

        $sBlocElse = '<!-- +ELSE +\1 +-->';
        $sBlocContenuElse = '(?P<content_else>[' . self::TOUS_LES_CARACTERES . ']+)';

        $sPattern = '/' . $sBlocOuvrant . $sBlocContenu;
        $sPattern .= '(' . $sBlocElse . $sBlocContenuElse . '){0,1}';
        $sPattern .= $sBlocFermant . '/U';

        while (preg_match_all($sPattern, $this->sData, $aMatches)) {
            // on a trouvé des blocs conditionnels : on les parcourt pour les tester
            foreach ($aMatches['variable'] as $iKey => $sVariable) {
                $sVariableEscape = preg_quote($sVariable);
                // on crée un pattern pour cibler cette variable précisément
                $sPatternVar = '/<!-- +IF +' . $sVariableEscape . ' +-->';
                $sPatternVar .= '([' . self::TOUS_LES_CARACTERES . ']+)';
                $sPatternVar .= '(<!-- +ELSE +' . $sVariableEscape . ' +-->';
                $sPatternVar .= '([' . self::TOUS_LES_CARACTERES . ']+)){0,1}';
                $sPatternVar .= '<!-- +ENDIF +' . $sVariableEscape . ' +-->/U';

                $sPatternVarInverse = '/<!-- +IFNOT +' . $sVariableEscape . ' +-->';
                $sPatternVarInverse .= '([' . self::TOUS_LES_CARACTERES . ']+)';
                $sPatternVarInverse .= '(<!-- +ELSE +' . $sVariableEscape . ' +-->';
                $sPatternVarInverse .= '([' . self::TOUS_LES_CARACTERES . ']+)){0,1}';
                $sPatternVarInverse .= '<!-- +ENDIF +' . $sVariableEscape . ' +-->/U';

                // Si la variable a tester est contenue dans un tableau, on regarde si celui ci existe,
                // puis on cherche toutes les clés demandées ( séparées par des '.' )
                // on regarde si le paramètre de la condition est défini
                if (array_key_exists($sVariable, $this->aParams)) {
                    // on teste la valeur du paramètre (true ou false)
                    if ($this->aParams[$sVariable]) {
                        // on laisse le bloc mais on vire quand même les commentaires
                        if (preg_match($sPatternVar, $aMatches[0][$iKey]) > 0) {
                            $sReplace = $aMatches['content'][$iKey];
                            $this->sData = preg_replace($sPatternVar, $sReplace, $this->sData, 1);
                        } else {
                            $sReplace = $aMatches['content_else'][$iKey];
                            $this->sData = preg_replace($sPatternVarInverse, $sReplace, $this->sData, 1);
                        }
                    } else {
                        // paramètre à false : on vire le bloc
                        if (preg_match($sPatternVar, $aMatches[0][$iKey]) > 0) {
                            $sReplace = $aMatches['content_else'][$iKey];
                            $this->sData = preg_replace($sPatternVar, $sReplace, $this->sData, 1);
                        } else {
                            $sReplace = $aMatches['content'][$iKey];
                            $this->sData = preg_replace($sPatternVarInverse, $sReplace, $this->sData, 1);
                        }
                    }
                } else {
                    // paramètre inconnu
                    // on vire le bloc
                    if (preg_match($sPatternVar, $aMatches[0][$iKey]) > 0) {
                        $sReplace = $aMatches['content_else'][$iKey];
                        $this->sData = preg_replace($sPatternVar, $sReplace, $this->sData, 1);
                    } else {
                        $sReplace = $aMatches['content'][$iKey];
                        $this->sData = preg_replace($sPatternVarInverse, $sReplace, $this->sData, 1);
                    }
                }
            }
        }
    }

    /**
     * Effectue les remplacements pour les paramètres simples
     * Renvoie une exception s'il reste des paramètres tableaux
     *
     * @return void
     */
    private function parseParams()
    {
        // on parse les chaînes pour faire des remplacements
        $aParams = array();
        $aReplaceData = array();
        foreach ($this->aParams as $sParam => $sValue) {
            if (is_array($sValue) || $sValue instanceof \Traversable) {
                foreach ($sValue as $sKey => $sSubValue) {
                    if (!is_array($sSubValue)) {
                        $aParams[] = '/{' . $sParam . '.' . $sKey . '}/';
                        $aReplaceData[] = $sSubValue;
                    }
                }
            } else {
                $aParams[] = '/{' . $sParam . '}/';
                $aReplaceData[] = $sValue;
            }
        }

        $this->sData = preg_replace($aParams, $aReplaceData, $this->sData);
    }

    /**
     * Effectue les remplacements pour les fonctions
     * Exemple de fonction : {url:xxx}
     *
     * @return void
     */
    private function parseFunctions()
    {
        // pour les datasources
        $this->funcDataSource();

        // pour les URL
        $this->funcUrl();

        // pour les langues
        $this->funcLang();

        // pour les dates
        $this->funcDate();

        // pour les listes déroulantes
        $this->funcSelect();

        // pour les fonctions custom
        $this->funcCustom();
    }

    /**
     * Nettoie les variables non remplacées
     *
     * @return void
     */
    private function cleanUp()
    {
        $sPattern = '/{[\w\._]+}/';
        $sReplace = '';
        $this->sData = preg_replace($sPattern, $sReplace, $this->sData);
    }

    /**
     * Lance la fonction {url:}
     * Cette fonction sert à déporter la logique des URL à l'extérieur
     * des templates.
     *
     * Au niveau des templates on écrit par exemple :
     * {url:param1=val1,param2=val2}
     * L'url construite sera par défaut "?param1=val1&param2=val2"
     *
     * @return void
     */
    private function funcUrl()
    {
        $mCallback = self::getCallbackUrl();

        $sPattern = '/{url:(?<params>[\w\.=,]*)}/';
        if (preg_match_all($sPattern, $this->sData, $aMatches)) {
            foreach ($aMatches['params'] as $sParam) {
                $sPatternReplace = '/{url:(' . $sParam . ')}/';

                if ($mCallback !== false) {
                    $sReplace = call_user_func($mCallback, $sParam);
                } else {
                    $sReplace = 'index.php';
                    if ($sParam != '') {
                        $sReplace .= '?' . str_replace(',', '&', $sParam);
                    }
                }
                $this->sData = preg_replace($sPatternReplace, $sReplace, $this->sData);
            }
        }
    }

    /**
     * Lance la fonction {date:}
     * Cette fonction sert à formater une date, par défaut depuis le format MySQL
     *
     * Au niveau des templates on écrit par exemple :
     * {date:2010-05-10 22:34:22}
     * La date affichée sera au format suivant :
     * 10/05/2010 22:34
     *
     * @return void
     */
    private function funcDate()
    {
        $mCallback = self::getCallbackDate();

        $sPattern = '/{date:(?<params>[\w\.,\-:\s\/]*)}/';
        if (preg_match_all($sPattern, $this->sData, $aMatches)) {
            foreach ($aMatches['params'] as $sParam) {
                $sPatternReplace = '/{date:(' . $sParam . ')}/';

                if ($mCallback !== false) {
                    $sReplace = call_user_func($mCallback, $sParam);
                } else {
                    // on doit couper en 2 éventuellement pour récupérer le format
                    $aDate = explode(',', $sParam);
                    $sDate = $aDate[0];

                    // si on a un format, on l'utilise
                    if (count($aDate) == 2) {
                        $sFormat = $aDate[1];
                        $sReplace = self::formatDateMySQL($sDate, $sFormat);
                    } else {
                        $sReplace = self::formatDateMySQL($sDate);
                    }
                }
                $this->sData = preg_replace($sPatternReplace, $sReplace, $this->sData);
            }
        }
    }

    /**
     * Lance la fonction {custom:}
     * Cette fonction sert à appeler une fonction de callback externe
     * qui a été préalablement définie par un addCallbackCustom()
     *
     * Au niveau des templates on écrit par exemple :
     * {custom:fonction:params}
     * la fonction "fonction" est appelée avec les paramètres
     *
     * @return void
     */
    private function funcCustom()
    {
        $sPattern = '/{(?<fonction>[\w]+):(?<params>[' . self::TOUS_LES_CARACTERES . ']*)}/U';
        if (preg_match_all($sPattern, $this->sData, $aMatches)) {
            foreach ($aMatches['fonction'] as $iKey => $sFonction) {
                $sPatternReplace = '/' . $aMatches[0][$iKey] . '/';
                $sParams = $aMatches['params'][$iKey];

                $mCallback = self::getCallbackCustom($sFonction);
                if ($mCallback === false) {
                    $sReplace = $sParams;
                    trigger_error($this->formatError("[custom:$sFonction] - Fonction inexistante"), E_USER_NOTICE);
                } else {
                    $sReplace = call_user_func($mCallback, $sParams);
                }

                $this->sData = preg_replace($sPatternReplace, $sReplace, $this->sData);
            }
        }
    }

    /**
     * Lance la fonction {select:}
     * Cette fonction sert à créer une liste déroulante à partir d'un tableau
     * défini au préalable via setList()
     *
     * Au niveau des templates on écrit par exemple :
     * {select:nom_champ,Tableau,valeur_selected}
     *
     * @return void
     */
    private function funcSelect()
    {
        $mCallback = self::getCallbackSelect();

        $sPattern = '/{select:(?<field>[\w]+),(?<table>[\w]+),(?<default>[\w_\.]*)(,(?<onchange>[\w_,\.\(\);]*))?}/';
        if (preg_match_all($sPattern, $this->sData, $aMatches)) {
            foreach ($aMatches['table'] as $iKey => $sTable) {
                $sPatternReplace = '/' . preg_quote($aMatches[0][$iKey]) . '/';

                if (!array_key_exists($sTable, $this->aListes)) {
                    trigger_error($this->formatError("[select:$sTable] - Liste inexistante"), E_USER_WARNING);
                    $sDefault = $aMatches['default'][$iKey];
                    $sReplace = $sDefault;
                } else {
                    $aList = $this->aListes[$sTable];
                    $sField = $aMatches['field'][$iKey];
                    $sDefault = $aMatches['default'][$iKey];
                    $sOnChange = $aMatches['onchange'][$iKey];

                    if ($mCallback !== false) {
                        $sReplace = call_user_func($mCallback, $sField, $aList, $sDefault);
                    } else {
                        if (!empty($sOnChange)) {
                            $sReplace = <<<HTML
        <select id="{$sField}" name="{$sField}" onchange="{$sOnChange}">

HTML;
                        } else {
                            $sReplace = <<<HTML
        <select id="{$sField}" name="{$sField}">

HTML;
                        }
                        foreach ($aList as $sValue => $sLibelle) {
                            $sSelected = '';
                            if ($sDefault == $sValue) {
                                $sSelected = 'selected="selected"';
                            }

                            $sReplace .= <<<HTML
        <option {$sSelected} value="{$sValue}">{$sLibelle}</option>

HTML;
                        }

                        $sReplace .= <<<HTML
        </select>

HTML;
                    }
                }
                $this->sData = preg_replace($sPatternReplace, $sReplace, $this->sData);
            }
        }
    }

    /**
     * Lance la fonction {lang:}
     * Cette fonction sert à traduire un terme.
     *
     * Au niveau des templates :
     * {lang:MY_TERM} pour traduire avec la langue par défaut
     * {lang:MY_TERM,en} pour traduire avec une langue spécifique
     *
     * @return void
     */
    private function funcLang()
    {
        $mCallback = self::getCallbackLang();

        $sPattern = '/{lang:(?<term>[\w]+)(,(?<code>[\w]+))?}/';

        if (preg_match_all($sPattern, $this->sData, $aMatches)) {
            foreach ($aMatches['term'] as $iKey => $sParam) {
                // détection de la langue et calcul du pattern de remplacement
                if (isset($aMatches['code'][$iKey]) && !empty($aMatches['code'][$iKey])) {
                    $sLangCode = $aMatches['code'][$iKey];
                    $sPatternReplace = '/{lang:(' . $sParam . '),(' . $sLangCode . ')+?}/';
                } else {
                    $sLangCode = '';
                    $sPatternReplace = '/{lang:(' . $sParam . ')}/';
                }

                if ($mCallback !== false) {
                    $sReplace = call_user_func($mCallback, $sParam);
                } else {
                    $sReplace = Language::getString($sParam, $sLangCode);
                }
                $this->sData = preg_replace($sPatternReplace, $sReplace, $this->sData);
            }
        }
    }

    private function funcDataSource()
    {
        $sPattern = '/{ds:(?<param>[\w]+)(,(?<decorator>[\w\\\]+))?}/';

        if (preg_match_all($sPattern, $this->sData, $aMatches)) {
            foreach ($aMatches['param'] as $iKey => $sParam) {
                if (isset($aMatches['decorator'][$iKey])) {
                    $sDecorator = $aMatches['decorator'][$iKey];
                }
                $sPatternReplace = '/{ds:' . $sParam;
                if (isset($sDecorator) && !empty($sDecorator)) {
                    $sPatternReplace .= ',' . preg_quote($sDecorator);
                }
                $sPatternReplace .= '}/';
                if (isset($this->aParams[$sParam])) {
                    $mParam = $this->aParams[$sParam];
                    if (is_object($mParam) && $mParam instanceof DataSource) {
                        $odsParam = $mParam;
                    } elseif (is_array($mParam)) {
                        $odsParam = new DataSourceArray($mParam);
                    }

                    if (isset($sDecorator) && !empty($sDecorator)) {
                        if (!class_exists($sDecorator)) {
                            trigger_error('Decorator "' . $sDecorator . '" could not be loaded', E_USER_NOTICE);
                        } else {
                            $oDecorator = new $sDecorator();
                            $odsParam->setDecorator($oDecorator);
                        }
                    }
                }

                if (isset($odsParam)) {
                    // Remplacement du paramètre par le displayTable
                    $sReplace = $odsParam->displayTable();
                } else {
                    // Suppression du paramètre
                    $sReplace = '';
                }
                $this->sData = preg_replace($sPatternReplace, $sReplace, $this->sData);
            }
        }
    }

    /**
     * Renseigne directement le contenu du template.
     *
     * @param string $sData contenu du template
     *
     * @return void
     */
    public function setData($sData)
    {
        $this->sData = $sData;
    }

    /**
     * Génère la fusion du template avec les paramètres et
     * renvoie le résultat
     *
     * @return string template fusionné
     */
    public function render()
    {
        Log::addInfo(__CLASS__, "Template '" . $this->getTemplateName() . "' : render");

        if ($this->sTemplateFile != null) {
            $this->sData = file_get_contents($this->sTemplateFile);
        }

        $this->aUnusedParams = $this->aParams;

        // parsing des blocs répétés
        $this->parseTables();

        // parsing des blocs conditionnels
        $this->parseConditions();

        // parsing des paramètres uniques
        $this->parseParams();

        // parsing des fonctions genre {url:xxx}
        $this->parseFunctions();

        // nettoyage final
        $this->cleanUp();

        return $this->sData;
    }

    /**
     * Formate une date MySQL.
     *
     * @param string $sDateHeure Date au format MySQL "YYYY-MM-DD HH:MM:SS"
     * @param string $sFormat    Format de la date, par défaut DD/MM/YYYY HH:MM
     *
     * @return string date formatée au format spécifié
     */
    public static function formatDateMySQL($sDateHeure, $sFormat = 'd/m/Y G:i')
    {
        // si on ne matche pas le format MySQL, on renvoie la date
        if (!preg_match('/\d{4}\-\d{2}\-\d{2} \d{2}:\d{2}:\d{2}/', $sDateHeure)) {
            return $sDateHeure;
        }

        $aDate = sscanf($sDateHeure, '%d-%d-%d %d:%d:%d');

        $iDate = mktime($aDate[3], $aDate[4], $aDate[5], $aDate[1], $aDate[2], $aDate[0]);

        $sDate = date($sFormat, $iDate);

        return $sDate;
    }
}
