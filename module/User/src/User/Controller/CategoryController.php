<?php

namespace User\Controller;

use Base\Controller\BaseController;

use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;

use User\Model\Category;
use User\Model\CategoryMapper;

use User\Form\CategoryForm;
use User\Form\CategoryFormFilter;

use Zend\Authentication\Adapter\DbTable as AuthAdapter;
use Zend\Authentication\AuthenticationService;

/**
 * Category controller
 * 
 * @author Mateusz Mirosławski
 *
 */
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
        // Identifier of logged in user
        $uid = $this->get('userId');
        
        // Get income and expense categories
        $cat_profit = $this->get('User\CategoryMapper')->getCategories($uid, 0);
        $cat_expense = $this->get('User\CategoryMapper')->getCategories($uid, 1);
        
        $view = new ViewModel();
        
        $view->setVariable('cat_profit', $cat_profit)
            ->setVariable('cat_expense', $cat_expense);
        
        return $view;
    }
    
    /**
     * Get categories. Used in categoryList.js via ajax
     * 
     * @return \Zend\View\Model\JsonModel;
     */
    public function categoryAction()
    {
        // Identifier of logged in user
        $uid = $this->get('userId');
        
        $view = new JsonModel();
        
        $request = $this->getRequest();
        
        // Check POST params
        if ($request->isPost()) {
            
            // Get POST data
            $data = $request->getPost();
            
            // Check if POST data are corrected
            if ((isset($data['cid'])) && (isset($data['c_type']))) {
                
                $data['cid'] = ($data['cid']==0)?(null):($data['cid']);
                
                try {
                    
                    // Get sub categories
                    $categories = $this->get('User\CategoryMapper')->getCategories($uid, $data['c_type'], $data['cid']);
                    
                    // Check if there are sub categories
                    if (!empty($categories)) {
                        
                        $view->setVariable('status', 'OK');
                        
                        foreach ($categories as $category) {
                            
                            $view->setVariable($category->c_name, $category->cid);
                            
                        }
                        
                    } else {
                        
                        $view->setVariable('status', 'noCategories');
                        
                    }
                    
                }
                catch (\Exception $e) {
                    
                    $view->setVariable('status', 'noPostData');
                    
                }
                
                
            } else { // Wrong data
                
                $view->setVariable('status', 'noPostData');
                
            }
            
        } else { // Missing POST data
            
            $view->setVariable("status","noPostData");
            
        }
        
        return $view;
    }
    
    /**
     * Add\edit category action. Used in categoryList.js via ajax
     * 
     * @return \Zend\View\Model\JsonModel;
     */
    public function saveAction()
    {
        // Identifier of logged in user
        $uid = $this->get('userId');
        
        $view = new JsonModel();
        
        // Add form
        $form = new CategoryForm();
        $formFilters = new CategoryFormFilter();

        $request = $this->getRequest();
        if ($request->isPost()) {
            
            $form->setInputFilter($formFilters->getInputFilter());
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                
                $category = new Category();
                
                // Insert data from POST into the category model
                $category->exchangeArray($form->getData());
                
                $category->uid = $uid;
                
                // Check if category name exists
                if ($this->get('User\CategoryMapper')->isCategoryNameExists($category->c_name, $category->c_type, $uid, $category->pcid)==0) {
                    
                    // Add category
                    $this->get('User\CategoryMapper')->saveCategory($category);
                    
                    // Get added category id
                    $cid = $this->get('User\CategoryMapper')->isCategoryNameExists($category->c_name, $category->c_type, $uid, $category->pcid);
                    
                    $view->setVariable('status', 'OK')
                        ->setVariable('name', $category->c_name)
                        ->setVariable('cid', $cid);
                    
                } else {
                    
                    $view->setVariable('status', 'exists');
                    
                }
                
            } else { // Form is not valid
                
                $view->setVariable('status', 'badData');
                
            }
            
        } else { // Missing POST data
            
            $view->setVariable('status', 'noPostData');
            
        }
        
        return $view;
    }
    
    /**
     * Delete category action. Used in categoryList.js via ajax
     * 
     * @return \Zend\View\Model\JsonModel;
     */
    public function deleteAction()
    {
        // Identifier of logged in user
        $uid = $this->get('userId');
        
        $view = new JsonModel();
        
        $request = $this->getRequest();
        if ($request->isPost()) {
    
            $data = $request->getPost();
            
            if (array_key_exists('cid', $data)) {
                
                $cid = (int)$data['cid'];
                
                // Check category
                $EMPTY = $this->get('User\CategoryMapper')->isCategoryEmpty($cid, $uid);
                $SUB = $this->get('User\CategoryMapper')->hasCategorySubCategories($cid, $uid);
                
                if ($EMPTY && !$SUB) {
                    
                    // Delete category
                    if ($this->get('User\CategoryMapper')->deleteCategory($cid, $uid) == 1) {
                        
                        $view->setVariable('status', 'OK');
                        
                    } else {
                        
                        $view->setVariable('status', 'badData');
                        
                    }
                    
                } else if ($EMPTY && $SUB) {
                    
                    $view->setVariable('status', 'hasSubcategories');
                    
                } else if (!$EMPTY && !$SUB) {
                    
                    $view->setVariable('status', 'hasTransactions');
                    
                } else {
                    
                    $view->setVariable('status', 'hasTransactionsAndSubcategories');
                    
                }
                
            } else { // Missing category id
                
                $view->setVariable('status', 'badData');
                
            }
        
        } else { // Missing POST data
        
            $view->setVariable('status', 'noPostData');
        
        }
        
        return $view;
    }

}