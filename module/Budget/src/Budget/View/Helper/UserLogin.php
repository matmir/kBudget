<?php

namespace Budget\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Model\ViewModel;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UserLogin extends AbstractHelper
{
    /**
     * Service locator instance
     * 
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $serviceLocator;
    
    public function __invoke()
    {
        // Check auth service instance
        if (!($this->serviceLocator instanceof ServiceLocatorInterface)) {
            throw new \Exception('There is no service locator instance!');
        }
        
        // Get user authentication service
        $auth = $this->getServiceLocator()->get('Auth\UserAuthentication');
        
        $view = new ViewModel();
        
        // Check if user is login
        if ($auth->hasIdentity()) {
            $view->setVariable('userName', $auth->getIdentity()->login);
            $view->setVariable('userType', $this->getServiceLocator()->get('userType'));
        }
        
        $view->setTemplate('budget/userLogin');
    
        return $this->getView()->render($view);
    }
    
    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }
    
    /**
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
}