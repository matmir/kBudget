<?php

namespace Budget\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Model\ViewModel;

class balanceChart extends AbstractHelper
{    
    public function __invoke($chartId, $chartTitle, $yTitle, $dataUnit, $chartWidth, $chartHeight, array $chartData)
    {
        $view = new ViewModel(
            array(
                'chartId' => $chartId,
                'chartTitle' => $chartTitle,
                'yTitle' => $yTitle,
                'dataUnit' => $dataUnit,
                'chartWidth' => $chartWidth,
                'chartHeight' => $chartHeight,
                'chartData' => $chartData
            )
        );
        
        $view->setTemplate('budget/balanceChart');
    
        return $this->getView()->render($view);
    }
}