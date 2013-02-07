<?php
/**
    @author Mateusz Mirosławski
    
    Kontroler administratora (zarządzanie userami)
*/

namespace Budget\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Budget\Model\User;
use Budget\Model\UserMapper;

use Budget\Form\PasswordAdminChangeForm;
use Budget\Form\PasswordAdminChangeFormFilter;

class AdminController extends AbstractActionController
{
    protected $userMapper;
    
    // Pobiera mapper do bazy z user-ami
    private function getUserMapper()
    {
        if (!$this->userMapper) {
            $sm = $this->getServiceLocator();
            $this->userMapper = new UserMapper($sm->get('adapter'));
        }
        
        return $this->userMapper;
    }
    
    // Główna strona
    public function indexAction()
    {
    }
    
    // Użytkownicy
    public function usersAction()
    {
        // Pobranie numeru strony
        $page = (int) $this->params()->fromRoute('page', 1);
        
        // Pobranie userów
        $users = $this->getUserMapper()->getUsers($page);
        
        return array(
            'users' => $users,
            'page' => $page,
        );
    }
    
    // Aktywacja lub deaktywacja wybranego usera
    public function useractivateAction()
    {
        // Pobranie identyfikatora usera, któremu zmieniam stan
        $uid = (int) $this->params()->fromRoute('uid', 0);
        
        // Nowy stan
        $active = (int) $this->params()->fromRoute('active', 0);
        
        // Pobranie numeru strony z której przychodzimy
        $page = (int) $this->params()->fromRoute('page', 1);
        
        // Zmiana stanu
        if ($uid > 0) {
            $this->getUserMapper()->setUserActive($uid, $active);
        }
        
        // Przekierowanie do listy userów
        return $this->redirect()->toRoute('admin-users', array(
                                                                'page' => $page,
                                                                ));
    }
    
    // Zmiana hasła wybranego usera
    public function userpassAction()
    {
        // Ustawienia długości loginu/hasła
        $cfg = $this->getServiceLocator()->get('user_login_cfg');
        
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
                    
                    // Zmiana hasła w bazie
                    $this->getUserMapper()->changeUserPass($uid, $p1);
                    
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