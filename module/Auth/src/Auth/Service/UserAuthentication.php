<?php
/**
 *  User authentication service
 *  Copyright (C) 2013 Mateusz Mirosławski
 * 
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 * 
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 * 
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Auth\Service;

use Base\Service\BaseService;
use Auth\Adapter\AuthAdapter;
use Zend\Authentication\AuthenticationService;
use Zend\Db\Adapter\Adapter;

class UserAuthentication extends BaseService
{
    /**
     * Bcrypt cost
     * 
     * @var int
     */
    const bCOST = 15;
    
    /** 
     * Authentication adapter
     * 
     * @var AuthAdapter
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
                \User\Mapper\UserMapper::TABLE,
                'login',
                'pass',
                self::bCOST,
                array('active' => 1)
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
            $storage->write($this->authAdapter->getAuthDataObject(array(
                    'userId',
                    'login',
                    'type',
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
}
