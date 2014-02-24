<?php

/**
 * Utility class for filesystem access.
 *
 * Contains methods to facilitate filesystem management.
 *
 * @package DspLib
 * @author  Pierre Feyssaguet <pfeyssaguet@gmail.com>
 */

namespace DspLib;

/**
 * Utility class for filesystem access.
 *
 * Contains methods to facilitate filesystem management.
 *
 * @package DspLib
 * @author  Pierre Feyssaguet <pfeyssaguet@gmail.com>
 */
class FileUtils
{
    /**
     * Returns directories found directly under asked directory.
     *
     * @param string $path   Directory to scan
     * @param string $filter Filter to apply (optional)
     *
     * @return array List of found directories paths
     *
     * @throws \InvalidArgumentException If the asked directory does not exists or is invalid
     */
    public static function getDirs($path, $filter = null)
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException('Directory ' . $path . ' does not exists');
        }

        if (!is_dir($path)) {
            throw new \InvalidArgumentException('Path ' . $path . ' is not a directory');
        }

        $dir = opendir($path);

        $dirs = array();
        while (false !== $fileName = readdir($dir)) {
            if ($fileName != '.' && $fileName != '..' && is_dir($path . '/' . $fileName)) {
                if (isset($filter)) {
                    if (preg_match($filter, $fileName)) {
                        $dirs[] = $path . '/' . $fileName;
                    }
                } else {
                    $dirs[] = $path . '/' . $fileName;
                }
            }
        }

        closedir($dir);

        return $dirs;
    }

    /**
     * Returns files found directly under asked directory.
     *
     * @param string $path Directory to scan
     *
     * @return array List of found files paths
     *
     * @throws \InvalidArgumentException If the asked directory does not exists or is invalid
     */
    public static function getFiles($path)
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException('Directory ' . $path . ' does not exists');
        }

        if (!is_dir($path)) {
            throw new \InvalidArgumentException('Path ' . $path . ' is not a directory');
        }

        $dir = opendir($path);

        $files = array();
        while (false !== $fileName = readdir($dir)) {
            if ($fileName != '.' && $fileName != '..' && is_file($path . '/' . $fileName)) {
                $files[] = $path . '/' . $fileName;
            }
        }

        closedir($dir);

        return $files;
    }
}
