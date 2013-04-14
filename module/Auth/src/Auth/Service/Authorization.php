<?php

namespace Auth\Service;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Mvc\MvcEvent;
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;

class Authorization implements ServiceLocatorAwareInterface
{
    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $serviceLocator;
    
    /**
     * Auth event to controll acces on site
     *
     * @param \Zend\Mvc\MvcEvent $event
     */
    public function doAuthorization(MvcEvent $event)
    {
        $acl = new Acl();
        
        // Role
        $acl->addRole(new Role('anonymous'));
        $acl->addRole(new Role('user'),  'anonymous');
        $acl->addRole(new Role('demo'),  'user');
        $acl->addRole(new Role('admin'), 'user');
        
        // Resources
        $acl->addResource(new Resource('Auth\Index'));
        $acl->addResource(new Resource('Budget\Analysis'));
        $acl->addResource(new Resource('Budget\Import'));
        $acl->addResource(new Resource('Budget\Main'));
        $acl->addResource(new Resource('Budget\Transaction'));
        $acl->addResource(new Resource('User\Category'));
        $acl->addResource(new Resource('User\Config'));
        $acl->addResource(new Resource('User\User'));
        $acl->addResource(new Resource('Admin\Index'));
        $acl->addResource(new Resource('Admin\Users'));
        
        // Anonymous
        $acl->deny('anonymous', 'Budget\Analysis', null);
        $acl->deny('anonymous', 'Budget\Import', null);
        $acl->deny('anonymous', 'Budget\Transaction', null);
        $acl->deny('anonymous', 'User\Category', null);
        $acl->deny('anonymous', 'User\Config', null);
        $acl->deny('anonymous', 'User\User', 'email');
        $acl->deny('anonymous', 'User\User', 'password');
        $acl->deny('anonymous', 'Admin\Index', null);
        $acl->deny('anonymous', 'Admin\Users', null);
        
        $acl->allow('anonymous', 'Auth\Index', 'denied');
        $acl->allow('anonymous', 'Budget\Main', 'index');
        $acl->allow('anonymous', 'User\User', 'login');
        $acl->allow('anonymous', 'User\User', 'logout');
        $acl->allow('anonymous', 'User\User', 'register');
        $acl->allow('anonymous', 'User\User', 'passrst');
        
        // User
        $acl->allow('user', 'Budget\Analysis', null);
        $acl->allow('user', 'Budget\Import', null);
        $acl->allow('user', 'Budget\Main', null);
        $acl->allow('user', 'Budget\Transaction', null);
        $acl->allow('user', 'User\Category', null);
        $acl->allow('user', 'User\Config', null);
        $acl->allow('user', 'User\User', null);
        
        // Demo
        $acl->deny('demo', 'User\User', 'email');
        $acl->deny('demo', 'User\User', 'password');
        $acl->deny('demo', 'Budget\Import', null);
        
        // Admin
        $acl->allow('admin', null, null);

        $routeMatch = $event->getRouteMatch();
        
        // Get full controller name
        $fullControllerName = $routeMatch->getParam('controller');
        // Exploding, because I have full name with module name and namespace
        list($moduleName, $namespaceName, $controllerName) = explode('\\', $fullControllerName);
        // Resource name
        $resource = $moduleName.'\\'.$controllerName;
        // Action name
        $actionName = $routeMatch->getParam('action');
        
        // Auth service
        $userAuth = $this->getServiceLocator()->get('Auth\UserAuthentication');
        // User role
        if ($userAuth->hasIdentity()) {
            $role = $this->getServiceLocator()->get('userType');
        } else {
            $role = 'anonymous';
        }
        
        // Check access
        if (!$acl->isAllowed($role, $resource, $actionName)) {
        
            switch ($role) {
                case 'anonymous':
                    $options['name'] = 'user/login';
                    break;
                case 'demo':
                    $options['name'] = 'access-denied';
                    break;
                case 'user':
                    $options['name'] = 'access-denied';
                    break;
                case 'admin':
                    $options['name'] = 'access-denied';
                    break;
                default:
                    $options['name'] = 'user/login';
                    break;
            }
        
            $url = $event->getRouter()->assemble(array(), $options);
            $response = $event->getResponse();
            //redirect to login route...
            $response->getHeaders()->addHeaderLine('Location', $url);
            $response->setStatusCode(302);
            $response->sendHeaders();
            exit;
        }
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
