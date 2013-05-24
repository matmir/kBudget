<?php
/**
    @author Mateusz Mirosławski
    
    User management
*/

namespace Admin\Controller;

use Base\Controller\BaseController;

use User\Model\User;
use User\Model\UserMapper;

use Admin\Form\PasswordAdminChangeForm;
use Admin\Form\PasswordAdminChangeFormFilter;

use Zend\Crypt\Password\Bcrypt;

class UsersController extends BaseController
{
    // Main page
    public function indexAction()
    {
        // Przekierowanie do listy userów
        return $this->redirect()->toRoute('admin/users/list');
    }
    
    // Users list
    public function listAction()
    {
        // Pobranie numeru strony
        $page = (int) $this->params()->fromRoute('page', 1);
        
        // Pobranie userów
        $users = $this->get('User\UserMapper')->getUsers($page);
        
        return array(
            'users' => $users,
            'page' => $page,
        );
    }
    
    // Activate/deactivate user
    public function activateAction()
    {
        // Pobranie identyfikatora usera, któremu zmieniam stan
        $uid = (int) $this->params()->fromRoute('uid', 0);
        
        // Nowy stan
        $active = (int) $this->params()->fromRoute('active', 0);
        
        // Pobranie numeru strony z której przychodzimy
        $page = (int) $this->params()->fromRoute('page', 1);
        
        // Zmiana stanu
        if ($uid > 0) {
            $this->get('User\UserMapper')->setUserActive($uid, $active);
        }
        
        // Przekierowanie do listy userów
        return $this->redirect()->toRoute('admin/users/list', array(
                                                                'page' => $page,
                                                                ));
    }
    
    // Change user password
    public function passwordAction()
    {
        // Ustawienia długości loginu/hasła
        $cfg = $this->get('user_login_cfg');
        
        // Pobranie identyfikatora usera, któremu zmieniam stan
        $uid = (int) $this->params()->fromRoute('uid', 0);
        
        // Pobranie numeru strony z której przychodzimy
        $page = (int) $this->params()->fromRoute('page', 1);
        
        // Formularz
        $form = new PasswordAdminChangeForm($cfg);
        // Filtry
        $formFilters = new PasswordAdminChangeFormFilter($cfg);
        
        // Flaga błędu (2 - nowe hasła się nie zgadzają, 3 - hasło zmieniono)
        $ERR = 0;
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            
            $form->setInputFilter($formFilters->getInputFilter());
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                    
                // Spr. poprawności wprowadzonych nowych haseł
                $p1 = (string)$form->get('pass1')->getValue();
                $p2 = (string)$form->get('pass2')->getValue();
                if ($p1 == $p2) {
                    
                    $bcrypt = new Bcrypt();
                    $bcrypt->setCost(\Auth\Service\UserAuthentication::bCOST);
                    
                    // Zmiana hasła w bazie
                    $this->get('User\UserMapper')->changeUserPass($uid, $bcrypt->create($p1));
                    
                    // Hasło zmieniono
                    $ERR = 3;
                    
                } else { // Wprowadzono błędne nowe hasła
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