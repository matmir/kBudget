<?php

namespace Admin\Controller;

use Base\Controller\BaseController;

use User\Model\User;
use Admin\Form\PasswordAdminChangeForm;
use Admin\Form\PasswordAdminChangeFormFilter;
use Zend\Crypt\Password\Bcrypt;

/**
 * Admin user controller
 * 
 * @author Mateusz Mirosławski
 * 
 */
class UsersController extends BaseController
{
    /**
     * Main page
     */
    public function indexAction()
    {
        // Redirect to the users list
        return $this->redirect()->toRoute('admin/users/list');
    }
    
    /**
     * User list
     */
    public function listAction()
    {
        // Get number of the page
        $page = (int) $this->params()->fromRoute('page', 1);
        
        // Get users
        $users = $this->get('User\UserMapper')->getUsers($page);
        
        return array(
            'users' => $users,
            'page' => $page,
        );
    }
    
    /**
     * Activate or deactivate selected user
     */
    public function activateAction()
    {
        // Get user identifier which we want change status
        $uid = (int) $this->params()->fromRoute('uid', 0);
        
        // New active state
        $active = (int) $this->params()->fromRoute('active', 0);
        
        // Get number of page from we comming
        $page = (int) $this->params()->fromRoute('page', 1);
        
        // Change state
        if ($uid > 0) {
            $this->get('User\UserMapper')->setUserActive($uid, $active);
        }
        
        // Redirect to the user list
        return $this->redirect()->toRoute('admin/users/list', array(
                                                                'page' => $page,
                                                                ));
    }
    
    /**
     * Change user password
     */
    public function passwordAction()
    {
        // login/pass configuration
        $cfg = $this->get('userLoginConfig');
        
        // Get user identifier which we want change password
        $uid = (int) $this->params()->fromRoute('uid', 0);
        
        // Get number of page from we comming
        $page = (int) $this->params()->fromRoute('page', 1);
        
        // Password change form
        $form = new PasswordAdminChangeForm($cfg);
        $formFilters = new PasswordAdminChangeFormFilter($cfg);
        
        // Error flag (2 - new password are different, 3 - password changed)
        $ERR = 0;
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            
            $form->setInputFilter($formFilters->getInputFilter());
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                    
                // Check passwords
                $p1 = (string)$form->get('pass1')->getValue();
                $p2 = (string)$form->get('pass2')->getValue();
                if ($p1 == $p2) {
                    
                    $bcrypt = new Bcrypt();
                    $bcrypt->setCost(\Auth\Service\UserAuthentication::bCOST);
                    
                    // Change user password
                    $this->get('User\UserMapper')->changeUserPass($uid, $bcrypt->create($p1));
                    
                    // Set flag to the OK
                    $ERR = 3;
                    
                } else {
                    $ERR = 2;
                }
                
            }
            
        }
        
        return array(
            'ERR' => $ERR,
            'form' => $form,
            'page' => $page,
            'uid' => $uid,
        );
    }

}
