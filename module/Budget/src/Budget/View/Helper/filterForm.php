<?php

namespace Budget\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Model\ViewModel;

class filterForm extends AbstractHelper
{    
    public function __invoke($form, $formAction)
    {
        $view = new ViewModel(
            array(
                'form' => $form,
                'formAction' => $formAction
            )
        );
        
        $view->setTemplate('budget/filterForm');
    
        return $this->getView()->render($view);
    }
}
