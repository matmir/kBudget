<?php

namespace Auth\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Authentication\Adapter\DbTable as AuthAdapter;
use Zend\Authentication\AuthenticationService;
use Zend\Db\Adapter\Adapter;

/**
 * User authentication service
 */
class UserAuthentication implements ServiceLocatorAwareInterface
{
    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $serviceLocator;
    
    /** 
     * Authentication adapter
     * 
     * @var \Zend\Authentication\Adapter\DbTable
     */
    protected $authAdapter;
    
    /**
     * Authentication service
     * 
     * @var \Zend\Authentication\AuthenticationService
     */
    protected $authService;
    
    /**
     * Create instance of authentication adapter
     * 
     * @return void
     */
    private function initAuthAdapter()
    {
        // Get system configuration
        $config = $this->getServiceLocator()->get('Configuration');
        
        // Create database adapter
        $dbAdapter = new Adapter($config['db']);
        
        // Create auth adapter
        $this->authAdapter = new AuthAdapter($dbAdapter,
                'users',
                'login',
                'pass',
                //'MD5(CONCAT(?, passs)) AND active = 1' // DOPISAĆ SOLENIE!!!
                'MD5(?) AND active = 1'
        );
    }
    
    /**
     * Authenticate user.
     * Return result of authentication.
     * 
     * @param String $identity User login
     * @param String $credential User password
     * @return bool
     */
    public function authenticate($identity, $credential)
    {
        // Check if there is auth adapter
        if (!($this->authAdapter instanceof AuthAdapter)) {
            $this->initAuthAdapter();
        }
        
        // Check if there is authentication service
        if (!($this->authService instanceof AuthenticationService)) {
            $this->authService = new AuthenticationService();
        }
        
        // Set data
        $this->authAdapter->setIdentity($identity)
                            ->setCredential($credential);
        
        // Authenticate
        $result = $this->authService->authenticate($this->authAdapter);
        
        // Positive authentication
        if ($result->isValid()) {
        
            $storage = $this->authService->getStorage();
            $storage->write($this->authAdapter->getResultRowObject(array(
                    'uid',
                    'login',
                    'u_type',
            )));
            
            return true;
            
        } else { // Negative authentication
            
            return false;
            
        }
    }
    
    /**
     * Check if user is authenticated
     * 
     * @return bool
     */
    public function hasIdentity()
    {
        // Check if there is authentication service
        if (!($this->authService instanceof AuthenticationService)) {
            $this->authService = new AuthenticationService();
        }
        
        return $this->authService->hasIdentity();
    }
    
    /**
     * Get logged in user data
     * 
     * @return mixed|null
     */
    public function getIdentity()
    {
        // Check if there is authentication service
        if (!($this->authService instanceof AuthenticationService)) {
            $this->authService = new AuthenticationService();
        }
        
        return $this->authService->getIdentity();
    }
    
    /**
     * Logout user
     * 
     * @return void
     */
    public function clearIdentity()
    {
        // Check if there is authentication service
        if (!($this->authService instanceof AuthenticationService)) {
            $this->authService = new AuthenticationService();
        }
    
        return $this->authService->clearIdentity();
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