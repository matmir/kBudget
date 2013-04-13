<?php

namespace Budget;

use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

use Zend\Authentication\AuthenticationService;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{
    
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $eventManager->attach('dispatch', array($this, 'loadConfiguration'), 10);
    }

    public function loadConfiguration(MvcEvent $e)
    {
        $application = $e->getApplication();
	    $sm = $application->getServiceManager();
	    
        // ---------------- Knefel do logowania/wylogowania
        $router = $e->getRouter();
        
        // Jest zalogowany
        if ($sm->get('Auth\UserAuthentication')->hasIdentity()) {
            $url = $router->assemble(array('controller' => 'user/logout'), array('name' => 'user/logout'));
            $title = 'Wyloguj';
            // Przekazanie loginu
            $e->getViewModel()->setVariable('user_name', $sm->get('user_login'));
            // Przekazanie flagi admina
            $e->getViewModel()->setVariable('admin', ($sm->get('user_type')=='admin')?(true):(false));
        } else { // nie zalogowany
            $url = $router->assemble(array('controller' => 'user/login'), array('name' => 'user/login'));
            $title = 'Zaloguj';
            // Przekazanie loginu
            $e->getViewModel()->setVariable('user_name', 'anonymous');
            // Przekazanie flagi admina
            $e->getViewModel()->setVariable('admin', false);
        }
        // Przekazanie do layout-u
        $e->getViewModel()->setVariable('log_btn', array('url' => $url,
                                                            'title' => $title));
    }
    
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
                __DIR__ . '/../../vendor/jpgraph/autoload_classmap.php',
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
                
                // Pobiera adapter do bazy danych
                'adapter' =>  function($sm) {
                    $config = $sm->get('Configuration');
                    $dbAdapter = new \Zend\Db\Adapter\Adapter($config['db']);
                    return $dbAdapter;
                },
                
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
