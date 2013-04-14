<?php
/**
    @author Mateusz Mirosławski
    
    Category management
*/

namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use User\Model\Category;
use User\Model\CategoryMapper;

use User\Form\CategoryForm;
use User\Form\CategoryFormFilter;

use Zend\Authentication\Adapter\DbTable as AuthAdapter;
use Zend\Authentication\AuthenticationService;

class CategoryController extends AbstractActionController
{
    protected $categoryMapper;
    
    // Pobiera mapper do bazy z kategoriami
    private function getCategoryMapper()
    {
        if (!$this->categoryMapper) {
            $sm = $this->getServiceLocator();
            $this->categoryMapper = new CategoryMapper($sm->get('adapter'));
        }
        
        return $this->categoryMapper;
    }
    
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
        $uid = $this->getServiceLocator()->get('userId');
        
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
    
    /**
     * Add category action
     */
    public function addAction()
    {
        // Identyfikator zalogowanego usera
        $uid = $this->getServiceLocator()->get('userId');
        
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
        $uid = $this->getServiceLocator()->get('userId');
        
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
        $uid = $this->getServiceLocator()->get('userId');
        
        // Identyfikator kategorii
        $cid = (int) $this->params()->fromRoute('cid', 0);
        if (!$cid) {
            return $this->redirect()->toRoute('user/category/list');
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