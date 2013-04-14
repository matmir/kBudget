<?php

namespace Base\Mapper;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Db\Adapter\Adapter;

/**
 * Base mapper service class
 * 
 * @author Mateusz Mirosławski
 *
 */
class BaseMapper implements ServiceLocatorAwareInterface
{
    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    private $serviceLocator;
    
    /**
     * Instance of Zend db adapter
     * 
     * @var \Zend\Db\Adapter\Adapter
     */
    private $adapter;
    
    /**
     * Get database adapter instance
     * 
     * @return \Zend\Db\Adapter\Adapter
     */
    public function getDbAdapter()
    {
        // Check if there is adapter instance
        if (!($this->adapter instanceof Adapter)) {
            
            // Get system configuration
            $config = $this->getServiceLocator()->get('Configuration');
            
            // Create db adapter
            $this->adapter = new Adapter($config['db']);
            
        }
        
        return $this->adapter;
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
