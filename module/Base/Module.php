<?php

namespace Base;

use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{
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
                
                // Configuration of the user login/password
                'userLoginConfig' =>  function($sm) {
                    $cfg = $sm->get('Configuration');
                    return $cfg['userLogin'];
                },
                
                // Configuration of the system e-mail
                'emailConfig' =>  function($sm) {
                    $cfg = $sm->get('Configuration');
                    return $cfg['email'];
                },
        
                // Configuration of the uploading files to the server
                'uploadConfig' =>  function($sm) {
                    $cfg = $sm->get('Configuration');
                    return $cfg['uploadBanking'];
                },
            ),
        );
    }
}
