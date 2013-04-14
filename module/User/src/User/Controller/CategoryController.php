<?php
/**
    @author Mateusz Mirosławski
    
    Category management
*/

namespace User\Controller;

use Base\Controller\BaseController;

use User\Model\Category;
use User\Model\CategoryMapper;

use User\Form\CategoryForm;
use User\Form\CategoryFormFilter;

use Zend\Authentication\Adapter\DbTable as AuthAdapter;
use Zend\Authentication\AuthenticationService;

class CategoryController extends BaseController
{
    /**
     * Main page
     */
    public function indexAction()
    {
        // Redirect to the category list
        return $this->redirect()->toRoute('user/category/list');
    }
    
    /**
     * Category list
     */
    public function listAction()
    {
        // Identyfikator zalogowanego usera
        $uid = $this->get('userId');
        
        // Rozdział kategorii na przychód i wydatek
        $cat_profit = $this->get('User\CategoryMapper')->getCategories($uid, 0);
        $cat_expense = $this->get('User\CategoryMapper')->getCategories($uid, 1);
        
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
    
    /**
     * Add category action
     */
    public function addAction()
    {
        // Identyfikator zalogowanego usera
        $uid = $this->get('userId');
        
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
                if ($this->get('User\CategoryMapper')->isCategoryNameExists($category->c_name, $category->c_type, $uid)==0) {
                    // Zapis danych
                    $this->get('User\CategoryMapper')->saveCategory($category);
                }
                
                // Przekierowanie do listy kategorii
                return $this->redirect()->toRoute('user/category/list');
                
            } else { // Niepoprawny formularz
                // Przekierowanie do listy kategorii
                return $this->redirect()->toRoute('user/category/list');
            }
            
        } else { // brak danych z formularza
            // Przekierowanie do listy kategorii
            return $this->redirect()->toRoute('user/category/list');
        }
    }
    
    /**
     * Edit category action
     */
    public function editAction()
    {
        // Identyfikator zalogowanego usera
        $uid = $this->get('userId');
        
        // Pobranie cid-a z adresu
        $cid = (int) $this->params()->fromRoute('cid', 0);
        // Spr. czy jest cid
        if ($cid) {
            
            // Pobranie danych kategorii
            $category = $this->get('User\CategoryMapper')->getCategory($cid, $uid);
            
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
                        if ($this->get('User\CategoryMapper')->isCategoryNameExists($category->c_name, $category->c_type, $uid)==0) {
                            // Zapis danych
                            $this->get('User\CategoryMapper')->saveCategory($category);
                        }
                        
                        // Przekierowanie do listy kategorii
                        return $this->redirect()->toRoute('user/category/list');
                        
                    }
                    
                }
                
                return array(
                    'form' => $form,
                    'cid' => $category->cid,
                );
                
            } else { // brak kategorii z takim idem
                // Przekierowanie do listy kategorii
                return $this->redirect()->toRoute('user/category/list');
            }
            
        } else { // brak parametru
            // Przekierowanie do listy kategorii
            return $this->redirect()->toRoute('user/category/list');
        }
    }
    
    /**
     * Delete category action
     */
    public function deleteAction()
    {
        // Identyfikator zalogowanego usera
        $uid = $this->get('userId');
        
        // Identyfikator kategorii
        $cid = (int) $this->params()->fromRoute('cid', 0);
        if (!$cid) {
            return $this->redirect()->toRoute('user/category/list');
        }
        
        // Spr czy kategoria jest pusta
        $EMPTY = $this->get('User\CategoryMapper')->isCategoryEmpty($cid, $uid);
        if ($EMPTY) {
            
            // Dane kategorii
            $category = $this->get('User\CategoryMapper')->getCategory($cid, $uid);
            
            $request = $this->getRequest();
            if ($request->isPost()) {
                
                $del = $request->getPost('del', 'No');
    
                if ($del == 'Yes') {
                    $cid = (int) $request->getPost('cid');
                    $this->get('User\CategoryMapper')->deleteCategory($cid, $uid);
                }
    
                // Przekierowanie do listy kategorii
                return $this->redirect()->toRoute('user/category/list');
            }
            
        }

        return array(
            'EMPTY' => $EMPTY,
            'cid'    => $cid,
            'category' => (isset($category)?($category):(null)),
        );
    }

}