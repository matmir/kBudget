<?php
/**
 *  Bank account controller
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

use Zend\View\Model\ViewModel;

use User\Model\Account;

use User\Form\CategoryForm;
use User\Form\CategoryFormFilter;
use User\Form\AccountForm;
use User\Form\AccountFormFilter;

class AccountController extends BaseController
{
    /**
     * List of user bank accounts.
     * Return array of Account objects.
     * 
     * @return array
     */
    public function indexAction()
    {
        // Get user identifier
        $uid = $this->get('userId');
        
        // Get user bank account list
        $accounts = $this->get('User\AccountMapper')->getAccounts($uid);
        
        // Get user default bank id
        $daid = $this->get('User\UserMapper')->getUser($uid)->getDefaultAccountId();
        
        $view = new ViewModel();
        
        $view->setVariable('accounts', $accounts);
        $view->setVariable('defaultAccount', $daid);
        
        return $view;
    }
    
    /**
     * Add new bank account
     * 
     * @return \Zend\View\Model\ViewModel
     */
    public function addAction()
    {
        // Get user identifier
        $uid = $this->get('userId');
        
        $form = new AccountForm();
        
        $request = $this->getRequest();
        if ($request->isPost()) {
        
            // Insert POST data into the form
            $form->setData($request->getPost());
        
            $formFilters = new AccountFormFilter();
            $form->setInputFilter($formFilters->getInputFilter());
        
            if ($form->isValid()) {
        
                // Create account model
                $account = new Account($form->getData());
                $account->setUserId($uid);
        
                // Save
                $this->get('User\AccountMapper')->saveAccount($account);
        
                return $this->redirect()->toRoute('user/account');
        
            }
        }
        
        return new ViewModel(array(
            'form' => $form,
        ));
    }
    
    /**
     * Edit bank account
     * 
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        // Get user identifier
        $uid = $this->get('userId');
        
        // Get account id
        $aid = (int) $this->params()->fromRoute('aid', 0);
        if (!$aid) {
            return $this->redirect()->toRoute('user/account');
        }
        
        $form = new AccountForm();
        $form->get('submit')->setValue('Edytuj');
        
        $account = $this->get('User\AccountMapper')->getAccount($aid, $uid);
        
        if ($account === null) {
            return $this->redirect()->toRoute('user/account');
        }
        
        // Insert data into the form
        $form->setData($account->getArrayCopy());
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            
            // Insert POST data into the form
            $form->setData($request->getPost());
            
            $formFilters = new AccountFormFilter();
            $form->setInputFilter($formFilters->getInputFilter());
            
            if ($form->isValid()) {
                
                // Create account model
                $account->exchangeArray($form->getData());
                
                // Save
                $this->get('User\AccountMapper')->saveAccount($account);
                
                return $this->redirect()->toRoute('user/account');
                
            }
        }
        
        return new ViewModel(array(
            'aid' => $aid,
            'form' => $form,
        ));
    }
    
    /**
     * Delete bank account
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function deleteAction()
    {
        // Get user identifier
        $uid = $this->get('userId');
        
        // Get account id
        $aid = (int) $this->params()->fromRoute('aid', 0);
        if (!$aid) {
            return $this->redirect()->toRoute('user/account');
        }
        
        $account = $this->get('User\AccountMapper')->getAccount($aid, $uid);
        
        if ($account === null) {
            return $this->redirect()->toRoute('user/account');
        }
        
        // is last bank account flag
        $LAST = ($this->get('User\AccountMapper')->getUserAccountCount($uid)>1)?(false):(true);
        
        // Check if account has no transaction
        $EMPTY = $this->get('User\AccountMapper')->isAccountEmpty($aid, $uid);
        if ($EMPTY) {
            
            $request = $this->getRequest();
            if ($request->isPost()) {
                
                $del = $request->getPost('del', 'No');
    
                if ($del == 'Yes') {
                    
                    $aid = (int) $request->getPost('aid');
                    
                    $this->get('User\AccountMapper')->deleteAccount($aid, $uid);
                    
                }
                
                return $this->redirect()->toRoute('user/account');
    
            }
            
        }
        
        return array(
            'EMPTY' => $EMPTY,
            'LAST' => $LAST,
            'aid' => $aid,
            'account' => $account,
        );
    }
    
    /**
     * Set default bank account
     *
     */
    public function defaultAction()
    {
        // Get user identifier
        $uid = $this->get('userId');
        
        // Get account id
        $aid = (int) $this->params()->fromRoute('aid', 0);
        if (!$aid) {
            return $this->redirect()->toRoute('user/account');
        }
        
        if ($this->get('User\AccountMapper')->isUserAccount($aid, $uid)) {
            
            // Save default account id
            $this->get('User\UserMapper')->setUserDefaultBankAccount($uid, $aid);
            
        }
        
        return $this->redirect()->toRoute('user/account');
        
    }
}
