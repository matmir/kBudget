<?php
/**
 *  User Controller
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

namespace User\Controller;

use Base\Controller\BaseController;

use User\Model\User;
use User\Model\Account;
use User\Model\Category;

use User\Form\LoginForm;
use User\Form\LoginFormFilter;

use User\Form\RegisterForm;
use User\Form\RegisterFormFilter;

use User\Form\PasswordResetForm;
use User\Form\PasswordResetFormFilter;

use User\Form\PasswordChangeForm;
use User\Form\PasswordChangeFormFilter;

use User\Form\EmailForm;
use User\Form\EmailFormFilter;

use Zend\Crypt\Password\Bcrypt;

class UserController extends BaseController
{
    public function indexAction()
    {
    }
    
    /**
     * Logout action
     */
    public function logoutAction()
    {
        $this->get('Auth\UserAuthentication')->clearIdentity();
        
        return $this->redirect()->toRoute('main');
    }
    
    /**
     * Login action
     */
    public function loginAction()
    {
        // Get the user login/pass length
        $cfg = $this->get('userLoginConfig');
        
        $form = new LoginForm($cfg);
        $formFilters = new LoginFormFilter($cfg);
        
        // Error flag
        $ERR = 0;

        $request = $this->getRequest();
        if ($request->isPost()) {
            
            $form->setInputFilter($formFilters->getInputFilter());
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                
                // Authentication service
                $userAuth = $this->get('Auth\UserAuthentication');
                
                // Authentication
                if ($userAuth->authenticate($form->get('login')->getValue(), $form->get('pass')->getValue())) {
                    
                    // User id
                    $uid = $this->get('userId');
                    
                    // Update login date
                    $this->get('User\UserMapper')->setUserLoginDate($uid);
                    
                    return $this->redirect()->toRoute('transactions', array(
                                                                           'month' => (int)date('m'),
                                                                           'year' => (int)date('Y'),
                                                                           'page' => 1,
                                                                           ));
                } else {
                    $ERR = 1;
                }
            }
        }
        
        return array(
            'form' => $form,
            'ERR' => $ERR,
        );
    }
    
    /**
     * Register action
     */
    public function registerAction()
    {
        // Get the user login/pass length
        $cfg = $this->get('userLoginConfig');
        
        // Logout logged in user
        $this->get('Auth\UserAuthentication')->clearIdentity();
        
        $form = new RegisterForm($cfg);
        $formFilters = new RegisterFormFilter($cfg);
        
        // Err flag (1 - login exist, 2 - e-mail exist, 3 - passwords are different)
        $ERR = 0;
        // Confirm flag (0 - not registered, 1 - registered)
        $CONFIRM = 0;

        $request = $this->getRequest();
        if ($request->isPost()) {
            
            $form->setInputFilter($formFilters->getInputFilter());
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                
                // Check existing of the given login
                $u_login = (string)$form->get('login')->getValue();
                if ($this->get('User\UserMapper')->isUserLoginExists($u_login)==0) {
                    
                    // Check existing of the given e-mail address
                    $u_email = (string)$form->get('email')->getValue();
                    if ($this->get('User\UserMapper')->isEmailExists($u_email)==0) {
                        
                        // Validation of given passwords
                        $p1 = (string)$form->get('pass1')->getValue();
                        $p2 = (string)$form->get('pass2')->getValue();
                        if ($p1 == $p2) {
                            
                            // Create new user
                            $user = new User();
                            $user->setLogin($u_login);
                            $user->setEmail($u_email);
                            
                            $bcrypt = new Bcrypt();
                            $bcrypt->setCost(\Auth\Service\UserAuthentication::bCOST);
                            
                            $user->setPass($bcrypt->create($p1));
                            // Add user to the database
                            $uid = $this->get('User\UserMapper')->addUser($user);
                            
                            // Create bank account
                            $account = new Account(
                                array(
                                    'userId' => $uid,
                                    'accountName' => (string)$form->get('bankAccount')->getValue()
                                )
                            );
                            // Add bank account to the database
                            $aid = $this->get('User\AccountMapper')->saveAccount($account);
                            
                            // Set default user bank account
                            $this->get('User\UserMapper')->setUserDefaultBankAccount($uid, $aid);
                            
                            // Create hidden category to the transfers
                            $category = new Category(
                                array(
                                    'userId' => $uid,
                                    'categoryType' => Category::TRANSFER,
                                    'categoryName' => 'Transfer'
                                )
                            );
                            // Add category to the database
                            $this->get('User\CategoryMapper')->saveCategory($category);
                            
                            $CONFIRM = 1;
                            
                        } else {
                            $ERR = 3;
                        }
                        
                    } else {
                        $ERR = 2;
                    }
                    
                } else {
                    $ERR = 1;
                }
                
            }
        }
        
        return array(
            'form' => $form,
            'ERR' => $ERR,
            'CONFIRM' => $CONFIRM,
        );
    }
    
    /**
     * Password reset action
     */
    public function passrstAction()
    {
        // Logout logged in user
        $this->get('Auth\UserAuthentication')->clearIdentity();
        
        $form = new PasswordResetForm();
        $formFilters = new PasswordResetFormFilter();
        
        // Err flag (1 - login exist, 2 - e-mail exist, 3 - passwords are different)
        $ERR = 0;
        // Confirm flag (0 - password not reset, 1 - password reset)
        $CONFIRM = 0;

        $request = $this->getRequest();
        if ($request->isPost()) {
            
            $form->setInputFilter($formFilters->getInputFilter());
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                
                // Check existing of the given e-mail address
                $u_email = (string)$form->get('email')->getValue();
                $uid = $this->get('User\UserMapper')->isEmailExists($u_email);
                if ($uid) {
                    
                    // Get user data
                    $user = $this->get('User\UserMapper')->getUser($uid);
                    
                    $bcrypt = new Bcrypt();
                    $bcrypt->setCost(\Auth\Service\UserAuthentication::bCOST);
                    // Generate new password
                    $new_pass = $user->genNewPass();
                    $encrypted_pass = $bcrypt->create($new_pass);
                    // Change user password
                    $this->get('User\UserMapper')->changeUserPass($uid, $encrypted_pass);
                    
                    // Prepare e-mail message
                    $msg = array(
                        'toAddress' => $user->getEmail(),
                        'toName' => $user->getLogin(),
                        'subject' => 'Reset hasła',
                        'body' => 'Twój login: '.$user->getLogin()."\nTwoje hasło: ".$new_pass
                    );
                    // Send e-mail
                    $this->get('Base\Mailer')->send($msg);
                    
                    $CONFIRM = 1;
                    
                } else {
                    $ERR = 1;
                }
                
            }
        }
        
        return array(
            'form' => $form,
            'ERR' => $ERR,
            'CONFIRM' => $CONFIRM,
        );
    }
    
    /**
     * Change e-mail action
     */
    public function emailAction()
    {
        // Get user identifier
        $uid = $this->get('userId');
    
        // Get user data
        $user = $this->get('User\UserMapper')->getUser($uid);
    
        $actual_email = $user->getEmail();
    
        $form = new EmailForm();
        $formFilters = new EmailFormFilter();
    
        // Err flag (1 - e-mail exist, 2 - ok)
        $ERR = 0;
    
        $request = $this->getRequest();
        if ($request->isPost()) {
    
            $form->setInputFilter($formFilters->getInputFilter());
            $form->setData($request->getPost());
    
            if ($form->isValid()) {
    
                // Get new e-mail address
                $new_email = (string)$form->get('email')->getValue();
    
                // Check if the given e-mail address exist in the database
                if ($this->get('User\UserMapper')->isEmailExists($new_email)==0) {
    
                    $this->get('User\UserMapper')->changeUserEmail($uid, $new_email);
    
                    $ERR = 2;
    
                    $actual_email = $new_email;
    
                } else {
                    $ERR = 1;
                }
    
            }
    
        }
    
        return array(
                'ERR' => $ERR,
                'actual_email' => $actual_email,
                'form' => $form,
        );
    }
    
    /**
     * Change user password
     */
    public function passwordAction()
    {
        // Get configuration of login/password lenght
        $cfg = $this->get('userLoginConfig');
    
        // User id
        $uid = $this->get('userId');
    
        // Get user data
        $user = $this->get('User\UserMapper')->getUser($uid);
    
        $form = new PasswordChangeForm($cfg);
        $formFilters = new PasswordChangeFormFilter($cfg);
    
        // Err flag (1 - Actual password if wrong, 2 - new passwords are different, 3 - password changed)
        $ERR = 0;
    
        $request = $this->getRequest();
        if ($request->isPost()) {
    
            $form->setInputFilter($formFilters->getInputFilter());
            $form->setData($request->getPost());
    
            if ($form->isValid()) {
    
                $bcrypt = new Bcrypt();
                $bcrypt->setCost(\Auth\Service\UserAuthentication::bCOST);
                
                // Validation of the actual password
                $p = (string)$form->get('pass')->getValue();
                if ($bcrypt->verify($p, $user->getPass())) {
    
                    // Validation of the new passwords
                    $p1 = (string)$form->get('pass1')->getValue();
                    $p2 = (string)$form->get('pass2')->getValue();
                    if ($p1 == $p2) {
    
                        // Save new password
                        $this->get('User\UserMapper')->changeUserPass($uid, $bcrypt->create($p1));
    
                        $ERR = 3;
    
                    } else {
                        $ERR = 2;
                    }
    
                } else {
                    $ERR = 1;
                }
    
            }
    
        }
    
        return array(
            'ERR' => $ERR,
            'form' => $form,
        );
    }

}
