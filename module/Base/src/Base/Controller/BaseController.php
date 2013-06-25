<?php

namespace Base\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Base action controller class
 * 
 * @author Mateusz Mirosławski
 *
 */
class BaseController extends AbstractActionController
{
    
    /**
     * Get registered instance
     * 
     * @param string $name Name of instance
     * @return object|array
     */
    public function get($name)
    {
        return $this->getServiceLocator()->get($name);
    }

}
