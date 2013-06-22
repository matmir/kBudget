<?php

namespace Budget\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Model\ViewModel;

class pieChart extends AbstractHelper
{    
    public function __invoke($chartId, $chartTitle, $dataUnit, $chartWidth, $chartHeight, array $chartData)
    {
        $view = new ViewModel(
            array(
                'chartId' => $chartId,
                'chartTitle' => $chartTitle,
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
