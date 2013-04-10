<?php

namespace DspLib\Test;

use DspLib\GoogleCharts;
/**
 * GoogleCharts test class
 *
 * @author Pierre Feyssaguet <pfeyssaguet@gmail.com>
 * @since  10 avr. 2013 09:42:50
 */

class GoogleChartsTest extends \PHPUnit_Framework_TestCase
{
    public function testChart()
    {
        $oChart = new GoogleCharts();
        $oChart->setType(GoogleCharts::TYPE_LINE);

        $oChart->setSize('500x200');
        $oChart->addRow('April', 17);
        $oChart->addRow('May', 9);
        $oChart->addRow('June', 7);
        $oChart->addRow('July', 4);
        $oChart->addRow('August', 3);
        $oChart->addRow('September', 3);
        $oChart->addRow('October', 3);
        $oChart->addRow('November', 3);
        $oChart->addRow('December', 3);
        $oChart->addRow('January', 1);
        $oChart->addRow('February', 3);
        $oChart->addRow('March', 4);

        $sActualUrl = $oChart->getURL();
        $sExpectedUrl = 'https://chart.googleapis.com/chart?chs=500x200&amp;cht=lc&amp;chd=s:9gZOLLLLLELO&amp;chl=Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec|Jan|Feb|Mar&amp;chxt=x,y&amp;chxr=1,0,17';
        $this->assertEquals($sExpectedUrl, $sActualUrl);
    }

    public function testChartWithSetData()
    {
        $oChart = new GoogleCharts();
        $oChart->setType(GoogleCharts::TYPE_LINE);

        $oChart->setSize('500x200');
        $aData = array(
            'April' => 17,
            'May' => 9,
            'June' => 7,
            'July' => 4,
            'August' => 3,
            'September' => 3,
            'October' => 3,
            'November' => 3,
            'December' => 3,
            'January' => 1,
            'February' => 3,
            'March' => 4,
        );

        $oChart->setData($aData);

        $sActualUrl = $oChart->getURL();
        $sExpectedUrl = 'https://chart.googleapis.com/chart?chs=500x200&amp;cht=lc&amp;chd=s:9gZOLLLLLELO&amp;chl=Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec|Jan|Feb|Mar&amp;chxt=x,y&amp;chxr=1,0,17';
        $this->assertEquals($sExpectedUrl, $sActualUrl);
    }
}
