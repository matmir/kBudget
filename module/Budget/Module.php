<?php

namespace Budget;

use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

use Zend\Authentication\AuthenticationService;

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
    
    public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
                'userMenu' => function ($sm) {
                    $userMenu = new View\Helper\UserMenu();
                    $userMenu->setServiceLocator($sm->getServiceLocator());

                    return $userMenu;
                }
            )
        );
    }
    
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                
                // Konfiguracja długości loginu/hasła usera
                'user_login_cfg' =>  function($sm) {
                    $cfg = $sm->get('Configuration');
                    return $cfg['user_login'];
                },
                
                // Konfiguracja e-maila systemowego
                'email_cfg' =>  function($sm) {
                    $cfg = $sm->get('Configuration');
                    return $cfg['email'];
                },
                
                // Konfiguracja ścieżek dla generowanych obrazków
                'img_dirs' =>  function($sm) {
                    $cfg = $sm->get('Configuration');
                    return $cfg['img_dir'];
                },
                
                // Konfiguracja nazw dla generowanych obrazków
                'img_nm' =>  function($sm) {
                    $cfg = $sm->get('Configuration');
                    return $cfg['img_names'];
                },
		
		        // Konfiguracja ładowania wyciągów bankowych
                'upload_cfg' =>  function($sm) {
                    $cfg = $sm->get('Configuration');
                    return $cfg['upload_banking'];
                },
            ),
        );
    }
}
