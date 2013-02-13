<?php
/**
    @author Mateusz Mirosławski
    
    Ustawienia uprawnień w systemie (ACL).
*/

namespace Budget\Controller\Plugin;
 
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;

use Zend\Authentication\AuthenticationService;
 
class MyAcl extends AbstractPlugin
{
    
    private $_auth;
    
    // Pobiera aktualną rolę
    private function getRole()
    {
        if (!$this->_auth) {
            $this->_auth = new AuthenticationService();
        }
        
        // Domyślna rola
        $identity = 'anonymous';
        
        // Czy ktoś zalogowany
        if ($this->_auth->hasIdentity()) {
            
            // Identyfikator
            $ident = $this->_auth->getIdentity();
            
            // Przydzielenie roli
            switch ($ident->u_type) {
                case 0: $identity = 'user'; break;
                case 1: $identity = 'admin'; break;
            }
        }
        
        return $identity;
    }
    
    // Autoryzacja
    public function doAuthorization($e)
    {
        
        $acl = new Acl();
        
        // Role
        $acl->addRole(new Role('anonymous'));
        $acl->addRole(new Role('user'),  'anonymous');
        $acl->addRole(new Role('admin'), 'user');
        
        // Zasoby
        $acl->addResource(new Resource('Main'));
        $acl->addResource(new Resource('Transaction'));
        $acl->addResource(new Resource('Analysis'));
        $acl->addResource(new Resource('User'));
        $acl->addResource(new Resource('Configuration'));
        $acl->addResource(new Resource('Admin'));
        $acl->addResource(new Resource('Import'));
        
        // Gość
        $acl->deny('anonymous', 'Transaction');
        $acl->deny('anonymous', 'Analysis');
        $acl->deny('anonymous', 'Configuration');
        $acl->deny('anonymous', 'Admin');
        $acl->deny('anonymous', 'Import');
        $acl->allow('anonymous', 'Main');
        $acl->allow('anonymous', 'User');
        
        // User
        $acl->allow('user',
            array('Main', 'Transaction', 'Analysis', 'User', 'Configuration','Import')
        );
        $acl->deny('user', 'Admin');
        
        // Admin
        $acl->allow('admin', null);
        
        
        $routeMatch = $e->getRouteMatch();
        $controller_nm = $routeMatch->getParam('controller');
        // Rozbijam, bo uzyskałem nazwe z przestrzenią nazw i katalogiem controller
        $c_e = explode('\\',$controller_nm);
        $controller = $c_e[2];
        $action = $routeMatch->getParam('action');
        
        // Aktualna rola
        $role = $this->getRole();
        
        //echo $role;
        
        if (!$acl->isAllowed($role, $controller, $action)){
            $router = $e->getRouter();
            $url = $router->assemble(array('controller' => 'user-login'), array('name' => 'user-login'));
            
            $response = $e->getResponse();
            $response->setStatusCode(302);
            //redirect to login route...
            $response->getHeaders()->addHeaderLine('Location', $url);
        }
    }
}