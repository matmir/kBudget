<?php

namespace Budget\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Model\ViewModel;

class pieChart extends AbstractHelper
{    
    public function __invoke($chartId, $chartTitle, $chartSubtitle, $dataUnit, $chartWidth, $chartHeight, array $chartData)
    {
        $view = new ViewModel(
            array(
                'chartId' => $chartId,
                'chartTitle' => $chartTitle,
                'chartSubtitle' => $chartSubtitle,
                'dataUnit' => $dataUnit,
                'chartWidth' => $chartWidth,
                'chartHeight' => $chartHeight,
                'chartData' => $chartData
            )
        );
        
        $view->setTemplate('budget/pieChart');
    
        return $this->getView()->render($view);
    }
}
