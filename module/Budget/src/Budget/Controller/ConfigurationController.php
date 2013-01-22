<?php
/**
    @author Mateusz Mirosławski
    
    Kontroler konfiguracji ustawień usera (kategorie/zmiana hasła/maila)
*/

namespace Budget\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Budget\Model\User;
use Budget\Model\UserMapper;

use Budget\Model\Category;
use Budget\Model\CategoryMapper;

use Budget\Form\CategoryForm;
use Budget\Form\CategoryFormFilter;

use Budget\Form\PasswordChangeForm;
use Budget\Form\PasswordChangeFormFilter;

use Budget\Form\EmailForm;
use Budget\Form\EmailFormFilter;

use Zend\Authentication\Adapter\DbTable as AuthAdapter;
use Zend\Authentication\AuthenticationService;

class ConfigurationController extends AbstractActionController
{
    protected $userMapper;
    protected $categoryMapper;
    
    // Pobiera mapper do bazy z user-ami
    private function getUserMapper()
    {
        if (!$this->userMapper) {
            $sm = $this->getServiceLocator();
            $this->userMapper = new UserMapper($sm->get('adapter'));
        }
        
        return $this->userMapper;
    }
    
    // Pobiera mapper do bazy z kategoriami
    private function getCategoryMapper()
    {
        if (!$this->categoryMapper) {
            $sm = $this->getServiceLocator();
            $this->categoryMapper = new CategoryMapper($sm->get('adapter'));
        }
        
        return $this->categoryMapper;
    }
    
    // Główna strona
    public function indexAction()
    {
    }
    
    // Lista kategorii
    public function categoryAction()
    {
        // Identyfikator zalogowanego usera
        $uid = $this->getServiceLocator()->get('user_data')->uid;
        
        // Rozdział kategorii na przychód i wydatek
        $cat_profit = $this->getCategoryMapper()->getCategories($uid, 0);
        $cat_expense = $this->getCategoryMapper()->getCategories($uid, 1);
        
        // Formularze na dodanie kategorii
        $form_add_profit = new CategoryForm();
        $form_add_profit->get('c_type')->setValue(0);
        $form_add_expense = new CategoryForm();
        $form_add_expense->get('c_type')->setValue(1);
        
        return array(
            'cat_profit' => $cat_profit,
            'cat_expense' => $cat_expense,
            'form_profit' => $form_add_profit,
            'form_expense' => $form_add_expense,
        );
    }
    
    // Dodanie kategorii
    public function categoryaddAction()
    {
        // Identyfikator zalogowanego usera
        $uid = $this->getServiceLocator()->get('user_data')->uid;
        
        // Formularz
        $form = new CategoryForm();
        // Filtry
        $formFilters = new CategoryFormFilter();

        $request = $this->getRequest();
        if ($request->isPost()) {
            
            $form->setInputFilter($formFilters->getInputFilter());
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                
                $category = new Category();
                
                // Uzupełnienie modelu danymi z formularza
                $category->exchangeArray($form->getData());
                
                $category->uid = $uid;
                
                // Spr. czy nazwa już istnieje
                if ($this->getCategoryMapper()->isCategoryNameExists($category->c_name, $category->c_type, $uid)==0) {
                    // Zapis danych
                    $this->getCategoryMapper()->saveCategory($category);
                }
                
                // Przekierowanie do listy kategorii
                return $this->redirect()->toRoute('configuration-category');
                
            } else { // Niepoprawny formularz
                // Przekierowanie do listy kategorii
                return $this->redirect()->toRoute('configuration-category');
            }
            
        } else { // brak danych z formularza
            // Przekierowanie do listy kategorii
            return $this->redirect()->toRoute('configuration-category');
        }
    }
    
    // Edycja kategorii
    public function categoryeditAction()
    {
        // Identyfikator zalogowanego usera
        $uid = $this->getServiceLocator()->get('user_data')->uid;
        
        // Pobranie cid-a z adresu
        $cid = (int) $this->params()->fromRoute('cid', 0);
        // Spr. czy jest cid
        if ($cid) {
            
            // Pobranie danych kategorii
            $category = $this->getCategoryMapper()->getCategory($cid, $uid);
            
            // Spr. czy jest taka kategoria
            if ($category->cid != 0) {
                
                // Formularz
                $form = new CategoryForm();
                // Filtry
                $formFilters = new CategoryFormFilter();
                // Knefel
                $form->get('submit')->setAttribute('value', 'Edytuj');
        
                // Wstawienie danych do formularza
                $form->bind($category);
                
                $request = $this->getRequest();
                if ($request->isPost()) {
                    
                    $form->setInputFilter($formFilters->getInputFilter());
                    $form->setData($request->getPost());
                    
                    if ($form->isValid()) {
                        
                        // Uzupełnienie modelu nowymi danymi z formularza
                        $category = $form->getData();
                        
                        $category->uid = $uid;
                        
                        // Spr. czy nazwa już istnieje
                        if ($this->getCategoryMapper()->isCategoryNameExists($category->c_name, $category->c_type, $uid)==0) {
                            // Zapis danych
                            $this->getCategoryMapper()->saveCategory($category);
                        }
                        
                        // Przekierowanie do listy kategorii
                        return $this->redirect()->toRoute('configuration-category');
                        
                    }
                    
                }
                
                return array(
                    'form' => $form,
                    'cid' => $category->cid,
                );
                
            } else { // brak kategorii z takim idem
                // Przekierowanie do listy kategorii
                return $this->redirect()->toRoute('configuration-category');
            }
            
        } else { // brak parametru
            // Przekierowanie do listy kategorii
            return $this->redirect()->toRoute('configuration-category');
        }
    }
    
    // Usunięcie kategorii
    public function categorydelAction()
    {
        // Identyfikator zalogowanego usera
        $uid = $this->getServiceLocator()->get('user_data')->uid;
        
        // Identyfikator kategorii
        $cid = (int) $this->params()->fromRoute('cid', 0);
        if (!$cid) {
            return $this->redirect()->toRoute('configuration-category');
        }
        
        // Spr czy kategoria jest pusta
        $EMPTY = $this->getCategoryMapper()->isCategoryEmpty($cid, $uid);
        if ($EMPTY) {
            
            // Dane kategorii
            $category = $this->getCategoryMapper()->getCategory($cid, $uid);
            
            $request = $this->getRequest();
            if ($request->isPost()) {
                
                $del = $request->getPost('del', 'No');
    
                if ($del == 'Yes') {
                    $cid = (int) $request->getPost('cid');
                    $this->getCategoryMapper()->deleteCategory($cid, $uid);
                }
    
                // Przekierowanie do listy kategorii
                return $this->redirect()->toRoute('configuration-category');
            }
            
        }

        return array(
            'EMPTY' => $EMPTY,
            'cid'    => $cid,
            'category' => (isset($category)?($category):(null)),
        );
    }
    
    // Zmiana emaila
    public function emailAction()
    {
        // Identyfikator zalogowanego usera
        $uid = $this->getServiceLocator()->get('user_data')->uid;
            
        // Pobranie danych usera
        $user = $this->getUserMapper()->getUser($uid);
        
        // Aktualny e-mail
        $actual_email = $user->email;
        
        // Formularz
        $form = new EmailForm();
        // Filtry
        $formFilters = new EmailFormFilter();
        
        // Flaga błędu (1 - podany e-mail istnieje, 2 - wszystko ok)
        $ERR = 0;
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            
            $form->setInputFilter($formFilters->getInputFilter());
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                
                // Nowy e-mail
                $new_email = (string)$form->get('email')->getValue();
                
                // Spr. Czy podanego e-maila nie ma w bazie
                if ($this->getUserMapper()->isEmailExists($new_email)==0) {
                    
                    // Zmiana e-maila w bazie
                    $this->getUserMapper()->changeUserEmail($uid, $new_email);
                    
                    // Wszystko ok
                    $ERR = 2;
                    
                    $actual_email = $new_email;
                    
                } else { // Podany adres istnieje
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
    
    // Zmiana hasła
    public function passwordAction()
    {
        // Ustawienia długości loginu/hasła
        $cfg = $this->getServiceLocator()->get('user_login_cfg');
        
        // Identyfikator zalogowanego usera
        $uid = $this->getServiceLocator()->get('user_data')->uid;
            
        // Pobranie danych usera
        $user = $this->getUserMapper()->getUser($uid);
        
        // Formularz
        $form = new PasswordChangeForm($cfg);
        // Filtry
        $formFilters = new PasswordChangeFormFilter($cfg);
        
        // Flaga błędu (1 - Błędne aktualne hasło, 2 - nowe hasła się nie zgadzają, 3 - hasło zmieniono)
        $ERR = 0;
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            
            $form->setInputFilter($formFilters->getInputFilter());
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                
                // Sprawdzenie poprawności aktualnego hasła
                $p = (string)$form->get('pass')->getValue();
                if ($user->pass == md5($p)) {
                    
                    // Spr. poprawności wprowadzonych nowych haseł
                    $p1 = (string)$form->get('pass1')->getValue();
                    $p2 = (string)$form->get('pass2')->getValue();
                    if ($p1 == $p2) {
                        
                        // Zmiana hasła w bazie
                        $this->getUserMapper()->changeUserPass($uid, $p1);
                        
                        // Flaga
                        $ERR = 3;
                        
                    } else { // Wprowadzono błędne nowe hasła
                        $ERR = 2;
                    }
                    
                } else { // Podane hasło jest błędne
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