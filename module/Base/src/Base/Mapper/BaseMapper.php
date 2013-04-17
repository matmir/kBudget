<?php

namespace Base\Mapper;

use Base\Service\BaseService;

use Zend\Db\Adapter\Adapter;

/**
 * Base mapper service class
 * 
 * @author Mateusz Mirosławski
 *
 */
class BaseMapper extends BaseService
{
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
}
