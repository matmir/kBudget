<?php

namespace Auth;

use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $sharedEvents = $eventManager->getSharedManager();
        
        // Auth (ACL)
        $sharedEvents->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', array($this, 'authorize'), 100);
    }
    
    public function authorize(MvcEvent $e)
    {
        $sm = $e->getApplication()->getServiceManager();
        $auth = $sm->get('Auth\Authorization');

        return $auth->doAuthorization($e);
    }
    
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
    public function getServiceConfig()
    {
        return array(
                'factories' => array(
    
                        // Get logged in user id
                        'uid' =>  function($sm) {
                            $userAuth = $sm->get('Auth\UserAuthentication');
                            
                            if (!($userAuth->hasIdentity())) {
                                throw new \Exception('User is not logged in!');
                            }
                            
                            return $userAuth->getIdentity()->uid;
                        },
                        // Get logged in user type
                        'user_type' =>  function($sm) {
                            $userAuth = $sm->get('Auth\UserAuthentication');
                        
                            if (!($userAuth->hasIdentity())) {
                                throw new \Exception('User is not logged in!');
                            }
                            
                            // Default user type
                            $identity = 'anonymous';
                            
                            // Get type
                            switch ($userAuth->getIdentity()->u_type) {
                                case 0: $identity = 'user'; break;
                                case 1: $identity = 'admin'; break;
                                case 2: $identity = 'demo'; break;
                            }
                        
                            return $identity;
                        },
                        // Get logged in user login
                        'user_login' =>  function($sm) {
                            $userAuth = $sm->get('Auth\UserAuthentication');
                        
                            if (!($userAuth->hasIdentity())) {
                                throw new \Exception('User is not logged in!');
                            }
                        
                            return $userAuth->getIdentity()->login;
                        },
                ),
        );
    }
}
