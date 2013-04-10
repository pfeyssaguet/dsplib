<?php

/**
 * GoogleCharts class
 *
 * @package DspLib
 * @author  Pierre Feyssaguet <pfeyssaguet@gmail.com>
 */

namespace DspLib;

/**
 * GoogleCharts class
 *
 * @package DspLib
 * @author  Pierre Feyssaguet <pfeyssaguet@gmail.com>
 */
class GoogleCharts
{
    private $sSize = '250x100';

    const TYPE_VERTICAL_CHART = 'bvs';
    const TYPE_PIE = 'p3';
    const TYPE_LINE = 'lc';

    private $sType = self::TYPE_VERTICAL_CHART;

    private $aData = array();
    private $aLibs = array();

    private $sAxis = 'x,y';

    private $sScale = '';

    public function getURL()
    {
        $sGoogleChart = 'https://chart.googleapis.com/chart';

        // chs : taille du graphe
        $sGoogleChart .= '?chs=' . $this->sSize;

        // cht : type de graphe
        $sGoogleChart .= '&amp;cht=' . $this->sType;

        // chd : données
        //$chd = self::textEncode($this->aData);
        $chd = self::simpleEncode($this->aData);
        $sGoogleChart .= '&amp;chd=' . $chd;

        // chm
        if ($this->sType == self::TYPE_VERTICAL_CHART) {
            // ajouter une courbe sur l'histogramme
            $chm = 'D,FF00FF,0,-1,2';
            // ajouter une numérotation
            //$chm .= '|N,000000,-1,-1,11';
            $sGoogleChart .= '&amp;chm=' . $chm;
        }
        // chl : libellés
        $chl = implode('|', $this->aLibs);
        $sGoogleChart .= '&amp;chl=' . $chl;

        // chxt : visible axes
        if ($this->sAxis != '') {
            $sGoogleChart .= '&amp;chxt=' . $this->sAxis;
        }

        // chxr : axis range (scale)
        if ($this->sScale != '') {
            $sGoogleChart .= '&amp;chxr=' . $this->sScale;
        }

        return $sGoogleChart;
    }

    public function setType($sType)
    {
        $this->sType = $sType;
    }

    public function setSize($sSize)
    {
        $this->sSize = $sSize;
    }

    public function setScale($sScale)
    {
        $this->sScale = $sScale;
    }

    public function setData($aData)
    {
        $this->aData = array_values($aData);
        $this->setLibs(array_keys($aData));
        $this->recalcScale();
    }

    public function setLibs($aLibs)
    {
        if ($this->sType != self::TYPE_PIE) {
            array_walk(
                $aLibs,
                function (&$sVal, $iKey) {
                    $sVal = substr($sVal, 0, 3);
                }
            );
        }
        $this->aLibs = $aLibs;
    }

    public function addRow($sLib, $iVal)
    {
        // si on est pas en type camembert, on réduit le libellé à 3 caractères
        if ($this->sType != self::TYPE_PIE) {
            $sLib = substr($sLib, 0, 3);
        }
        $this->aLibs[] = urlencode($sLib);
        $this->aData[] = $iVal;
        $this->recalcScale();
    }

    private function recalcScale()
    {
        $iMin = min($this->aData);
        if ($iMin > 0) {
            $iMin = 0;
        }
        $iMax = max($this->aData);

        $this->sScale = '1,' . $iMin . ',' . $iMax;
    }

    public static function textEncode($aValues)
    {
        return 't:' . implode(',', $aValues);
    }

    public static function simpleEncode($aValues)
    {
        $sSimpleEncoding = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $iMaxVal = max($aValues);

        $aChartData = array('s:');
        foreach ($aValues as $iCurrentValue) {
            if (is_numeric($iCurrentValue) && $iCurrentValue >= 0) {
                $iPos = round((strlen($sSimpleEncoding)-1) * $iCurrentValue / max(1, $iMaxVal));
                $aChartData[] = substr($sSimpleEncoding, $iPos, 1);
            } else {
                $aChartData[] = '_';
            }
        }
        return implode('', $aChartData);
    }

    public static function extendedEncode($aValues)
    {
        $sExtendedEncoding = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-.';
        $sChartData = 'e:';
        // TODO code ci-dessous en javascript à traduire en PHP
        /*
        var chartData = 'e:';

        for(i = 0, len = arrVals.length; i < len; i++) {
          // In case the array vals were translated to strings.
          var numericVal = new Number(arrVals[i]);
          // Scale the value to maxVal.
          var scaledVal = Math.floor(EXTENDED_MAP_LENGTH *
              EXTENDED_MAP_LENGTH * numericVal / maxVal);

          if(scaledVal > (EXTENDED_MAP_LENGTH * EXTENDED_MAP_LENGTH) - 1) {
            chartData += "..";
          } else if (scaledVal < 0) {
            chartData += '__';
          } else {
            // Calculate first and second digits and add them to the output.
            var quotient = Math.floor(scaledVal / EXTENDED_MAP_LENGTH);
            var remainder = scaledVal - EXTENDED_MAP_LENGTH * quotient;
            chartData += EXTENDED_MAP.charAt(quotient) + EXTENDED_MAP.charAt(remainder);
          }
        }

        return chartData;
        */
        return $sChartData;
    }
}
