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
        $eventManager->attach('dispatch', array($this, 'loadConfiguration'), 100);
    }

    public function loadConfiguration(MvcEvent $e)
    {
        $application   = $e->getApplication();
	$sm            = $application->getServiceManager();
	$sharedManager = $application->getEventManager()->getSharedManager();

	$sharedManager->attach('Zend\Mvc\Controller\AbstractActionController','dispatch', 
             function($e) use ($sm) {
		$sm->get('ControllerPluginManager')->get('MyAcl')
                   ->doAuthorization($e);
	    }
        );
        
        // ---------------- Knefel do logowania/wylogowania
        $dt = $sm->get('user_data');
        $router = $e->getRouter();
        
        // Jest zalogowany
        if ($dt->uid) {
            $url = $router->assemble(array('controller' => 'user/logout'), array('name' => 'user/logout'));
            $title = 'Wyloguj';
        } else { // nie zalogowany
            $url = $router->assemble(array('controller' => 'user/login'), array('name' => 'user/login'));
            $title = 'Zaloguj';
        }
        // Przekazanie do layout-u
        $e->getViewModel()->setVariable('log_btn', array('url' => $url,
                                                            'title' => $title));
        // Przekazanie loginu
        $e->getViewModel()->setVariable('user_name', $dt->login);
	
	// Przekazanie flagi admina
	$e->getViewModel()->setVariable('admin', ($dt->u_type==1)?(true):(false));
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
                
                // Pobiera dane zalogowanego usera
                'user_data' =>  function($sm) {
                    $auth = new AuthenticationService();
                    // Poprawnie zalogowany
                    if ($auth->hasIdentity()) {
                        return $auth->getIdentity();
                    } else {
                        return (object)array('uid' => 0,
                                     'login' => 'anonymous',
				     'u_type' => -1);
                    }
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
