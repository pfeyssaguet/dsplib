<?php

namespace DspLib;

/**
 * Classe utilitaire d'accès aux fichiers.
 *
 * Contient des méthodes pour faciliter l'accès aux fichiers.
 *
 * @author   deuspi
 */
class FileUtils
{
    /**
     * Renvoie les répertoires trouvés directement au niveau du répertoire demandé
     *
     * @param string $sPath   Répertoire à scanner
     * @param string $sFilter Filtre à appliquer (optionnel)
     *
     * @return array Liste des chemins des répertoires trouvés
     *
     * @throws \InvalidArgumentException Si le répertoire n'existe pas ou est invalide
     */
    public static function getDirs($sPath, $sFilter = null)
    {
        if (!file_exists($sPath)) {
            throw new \InvalidArgumentException('Directory ' . $sPath . ' does not exists');
        }

        if (!is_dir($sPath)) {
            throw new \InvalidArgumentException('Path ' . $sPath . ' is not a directory');
        }

        $oDir = opendir($sPath);

        $aDirs = array();
        while (false !== $sFileName = readdir($oDir)) {
            if ($sFileName != '.' && $sFileName != '..' && is_dir($sPath . '/' . $sFileName)) {
                if (isset($sFilter)) {
                    if (preg_match($sFilter, $sFileName)) {
                        $aDirs[] = $sPath . '/' . $sFileName;
                    }
                } else {
                    $aDirs[] = $sPath . '/' . $sFileName;
                }
            }
        }

        closedir($oDir);

        return $aDirs;
    }

    /**
     * Renvoie les fichiers trouvés directement au niveau du répertoire demandé
     *
     * @param string $sPath Répertoire à scanner
     *
     * @return array Liste des chemins des fichiers trouvés
     *
     * @throws \InvalidArgumentException Si le répertoire n'existe pas ou est invalide
     */
    public static function getFiles($sPath)
    {
        if (!file_exists($sPath)) {
            throw new \InvalidArgumentException('Directory ' . $sPath . ' does not exists');
        }

        if (!is_dir($sPath)) {
            throw new \InvalidArgumentException('Path ' . $sPath . ' is not a directory');
        }

        $oDir = opendir($sPath);

        $aFiles = array();
        while (false !== $sFileName = readdir($oDir)) {
            if ($sFileName != '.' && $sFileName != '..' && is_file($sPath . '/' . $sFileName)) {
                $aFiles[] = $sPath . '/' . $sFileName;
            }
        }

        closedir($oDir);

        return $aFiles;
    }
}
