<?php
/**
 *  Main controller
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

namespace Budget\Controller;

use Base\Controller\BaseController;

use Budget\Model\Transaction;
use User\Model\Category;

use Budget\Form\TransactionForm;
use Budget\Form\TransactionFilter;

use Budget\Form\TransactionFilterForm;
use Budget\Form\TransactionFilterFormFilter;

use Budget\Form\TransferForm;
use Budget\Form\TransferFilter;

class TransactionController extends BaseController
{
    /**
     * View transaction list
     */
    public function indexAction()
    {
        // User identifier
        $uid = $this->get('userId');
        
        $page = (int) $this->params()->fromRoute('page', 1);
        
        // Bank account id
        $aid = (int) $this->params()->fromRoute('aid', 0);
        if (!$aid) {
            
            // Load default account id
            $aid = $this->get('User\UserMapper')->getUser($uid)->getDefaultAccountId();
            
        } else {
            
            // Check if given account id is user accout
            if (!$this->get('User\AccountMapper')->isUserAccount($aid, $uid)) {
                
                // Load default account id
                $aid = $this->get('User\UserMapper')->getUser($uid)->getDefaultAccountId();
                
            }
            
        }
        
        // Get user bank accounts to select object
        $accounts = $this->get('User\AccountMapper')->getUserAccountsToSelect($uid);
        
        // Get actual account data
        $account = $this->get('User\AccountMapper')->getAccount($aid, $uid);
        
        // Filter form
        $form = new TransactionFilterForm();
        $form->get('aid')->setValueOptions($accounts);
        $form->get('aid')->setValue($aid);
        
        // Get date
        $m = (int) $this->params()->fromRoute('month', date('m'));
        $Y = (int) $this->params()->fromRoute('year', date('Y'));
        
        // Insert date to the form
        $form->setData(
            array(
                'month' => $m,
                'year' => $Y,
            )
        );
        
        // Parse date
        $dt = new \DateTime($Y.'-'.$m.'-01');
        
        // Date params
        $date_param = array(
            'type' => 'month',
            'dt_month' => $dt->format('Y-m'),
        );
        
        // Get sum of expenses and profits
        $balanceData = $this->get('Budget\AnalysisService')->makeTransactionsBalanceData(
            $uid,
            $aid,
            $date_param
        );
        
        // Get transactions joned with category
        $transactions = $this->get('Budget\TransactionMapper')->getTransactions(
            $uid,
            $aid,
            $date_param,
            array(-1),
            $page,
            true
        );
        
        // Get categories names (tid, main_category, sub_category)
        $transactionsCopy = clone $transactions;
        $categories = array();
        $accountTransfers = array();
        foreach ($transactionsCopy as $transaction) {
            
            // Check if category has parent
            if ($transaction->parentCategoryId === null) {
                // Main category
                $categories[$transaction->transactionId] = array($transaction->categoryName, null);
            } else {
                // Subcategory
                $parent = $this->get('User\CategoryMapper')->getCategory($transaction->parentCategoryId, $uid);
                
                $categories[$transaction->transactionId] = array($parent->getCategoryName(), $transaction->categoryName);
            }
            
        }
        
        return array(
            'transactions' => $transactions,
            'accountsNames' => $accounts,
            'categories' => $categories,
            'formRange' => $form,
            'dt' => array('month' => $m, 'year' => $Y),
            'aid' => $aid,
            'sum_expense' => $balanceData['expenses'],
            'sum_profit' => $balanceData['profits'],
            'accountBalance' => $account->getBalance(),
            'monthBalance' => $balanceData['balance'],
            'page' => $page,
        );
    }
    
    /**
     * Filter transaction list
     */
    public function filterAction()
    {
        // User identifier
        $uid = $this->get('userId');
        
        // Min year of user transactions
        $minYear = $this->get('Budget\TransactionMapper')->getMinYearOfTransaction($uid);
        
        // Get user bank accounts
        $accounts = $this->get('User\AccountMapper')->getUserAccountsToSelect($uid);
        
        // Filter form
        $formRange = new TransactionFilterForm();
        $formRange->get('aid')->setValueOptions($accounts);
        $formFilters = new TransactionFilterFormFilter($minYear);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $formRange->setInputFilter($formFilters->getInputFilter());
            $formRange->setData($request->getPost());
            
            if ($formRange->isValid()) {
                
                $data = $formRange->getData();
                
                return $this->redirect()->toRoute('transactions', array_merge($data, array('page' => 1)));
                
            }
            
        }
        
        return $this->redirect()->toRoute('transactions', array(
                                                            'month' => date('m'),
                                                            'year' => date('Y'),
                                                            'page' => 1,
        ));
    }

    /**
     * Add new transaction
     */
    public function addAction()
    {
        // User identifier
        $uid = $this->get('userId');
        
        // Get type of transaction
        $t_type= (int) $this->params()->fromRoute('type', Transaction::EXPENSE);
        // Check type
        if (!($t_type==Transaction::PROFIT || $t_type==Transaction::EXPENSE)) {
            
            return $this->redirect()->toRoute('main');
            
        }
        
        // Get account id
        $aid = (int) $this->params()->fromRoute('aid', 0);
        // Check if given account id is user accout
        if (!$this->get('User\AccountMapper')->isUserAccount($aid, $uid)) {
        
            return $this->redirect()->toRoute('transactions');
        
        }
        
        $form = new TransactionForm();
        
        // Set account id
        $form->get('accountId')->setValue($aid);
        
        // Get user main categories
        $userMainCat = $this->get('User\CategoryMapper')->getUserCategoriesToSelect($uid, $t_type);
        $form->get('pcid')->setValueOptions($userMainCat);
        
        // Set the submit value
        $form->get('submit')->setValue('Dodaj');
        // Set the transaction type in form
        $form->get('transactionType')->setValue($t_type);
        // Set default date in the form
        $form->get('date')->setValue((new \DateTime())->format('Y-m-d'));

        $request = $this->getRequest();
        if ($request->isPost()) {
            
            $postData = $request->getPost();
            
            $categoryValid = true;
            
            // Check main category id
            if ($postData['pcid'] == 0) {
                
                $form->get('pcid')->setMessages(
                    array(
                        'Category must be added before transaction!'
                    )
                );
                
                $categoryValid = false;
                
            } else if ($postData['pcid'] == -1) {
                
                $form->get('pcid')->setMessages(
                    array(
                        'Select transaction category!'
                    )
                );
                
                $categoryValid = false;
                
            } else {
                
                $ccid = $this->get('User\CategoryMapper')->getUserCategoriesToSelect($uid, $t_type, $postData['pcid']);
                $form->get('ccid')->setValueOptions($ccid);
                
            }
            // Check subcategory id
            if ($postData['ccid'] == 0) {
            
                $form->get('ccid')->setMessages(
                    array(
                        'Category must be added before transaction!'
                    )
                );
                
                $categoryValid = false;
            
            }
            
            // Insert POST data into the form
            $form->setData($postData);
            
            $formFilters = new TransactionFilter();
            $form->setInputFilter($formFilters->getInputFilter());
            
            if ($form->isValid() && $categoryValid) {
                
                // Get data from form
                $data = $form->getData();
                
                // Read category id
                if ($data['ccid'] == -1) { // no subcategory
                    
                    $data['categoryId'] = (int)$data['pcid'];
                    
                } else { // there is subcategory
                    
                    $data['categoryId'] = (int)$data['ccid'];
                    
                }
                
                // Check if there is correct cid
                if (isset($data['categoryId'])) {
                    
                    // Remove unused fields
                    unset($data['pcid']);
                    unset($data['ccid']);
                    
                    // Create transaction model
                    $transaction = new Transaction($data);
                    
                    // uid
                    $transaction->setUserId($uid);
                    // Save
                    $this->get('Budget\TransactionMapper')->saveTransaction($transaction);
                    
                    return $this->redirect()->toRoute('transactions', array(
                                                                           'aid' => $aid,
                                                                           'month' => $transaction->getDate()->format('m'),
                                                                           'year' => $transaction->getDate()->format('Y'),
                                                                           'page' => 1,
                                                                           ));
                    
                }
                
            }
        }
        
        return array(
            'form' => $form,
            'aid' => $aid,
            't_type' => $t_type,
        );
    }

    /**
     * Edit transaction
     */
    public function editAction()
    {
        // User identifier
        $uid = $this->get('userId');
        
        $page = (int) $this->params()->fromRoute('page', 1);
        
        // Get date from the url
        $m = (int) $this->params()->fromRoute('month', date('m'));
        $Y = (int) $this->params()->fromRoute('year', date('Y'));
        
        // Get the transaction id
        $tid = (int) $this->params()->fromRoute('tid', 0);
        if (!$tid) {
            return $this->redirect()->toRoute('main');
        }
        
        // Get transaction data
        $transaction = $this->get('Budget\TransactionMapper')->getTransaction($tid, $uid);
        $data = $transaction->getArrayCopy();
        $data['date'] = $transaction->getDate()->format('Y-m-d');
        
        $form  = new TransactionForm();
        
        // User main categories
        $userMainCat = $this->get('User\CategoryMapper')->getUserCategoriesToSelect($uid, $transaction->getTransactionType());
        $form->get('pcid')->setValueOptions($userMainCat);
        
        // Check if the transaction category has subcategories
        $category = $this->get('User\CategoryMapper')->getCategory($transaction->getCategoryId(), $uid);
        
        if ($category->getParentCategoryId() == null) { // this is main category
            
            $data['pcid'] = $category->getCategoryId();
            $data['ccid'] = null;
            
            // User subcategories
            $userSubCat = $this->get('User\CategoryMapper')->getUserCategoriesToSelect(
                $uid,
                $transaction->getTransactionType(),
                $category->getCategoryId()
            );
            
        } else { // this is subcategory
            
            $data['pcid'] = $category->getParentCategoryId();
            $data['ccid'] = $category->getCategoryId();
            
            // User subcategories
            $userSubCat = $this->get('User\CategoryMapper')->getUserCategoriesToSelect(
                $uid,
                $transaction->getTransactionType(),
                $category->getParentCategoryId()
            );
            
        }
        
        $form->get('ccid')->setValueOptions($userSubCat);
        
        // Insert data into the form
        $form->setData($data);
        
        // Submit button value
        $form->get('submit')->setAttribute('value', 'Edytuj');

        $request = $this->getRequest();
        if ($request->isPost()) {
        
            $postData = $request->getPost();
            
            $categoryValid = true;
            
            // Check main category id
            if ($postData['pcid'] == 0) {
                
                $form->get('pcid')->setMessages(
                    array(
                        'Category must be added before transaction!'
                    )
                );
                
                $categoryValid = false;
                
            } else if ($postData['pcid'] == -1) {
                
                $form->get('pcid')->setMessages(
                    array(
                        'Select transaction category!'
                    )
                );
                
                $categoryValid = false;
                
            } else {
                
                $ccid = $this->get('User\CategoryMapper')->getUserCategoriesToSelect(
                    $uid,
                    $transaction->getTransactionType(),
                    $postData['pcid']
                );
                $form->get('ccid')->setValueOptions($ccid);
                
            }
            // Check subcategory id
            if ($postData['ccid'] == 0) {
            
                $form->get('ccid')->setMessages(
                    array(
                        'Category must be added before transaction!'
                    )
                );
                
                $categoryValid = false;
            
            }
            
            // Insert POST data into the form
            $form->setData($postData);
            
            $formFilters = new TransactionFilter();
            $form->setInputFilter($formFilters->getInputFilter());
            
            if ($form->isValid() && $categoryValid) {
                
                // Get data from form
                $data = $form->getData();
                
                // Read category id
                if ($data['ccid'] == -1) { // no subcategory
                    
                    $data['categoryId'] = (int)$data['pcid'];
                    
                } else { // there is subcategory
                    
                    $data['categoryId'] = (int)$data['ccid'];
                    
                }
                
                // Check if there is correct cid
                if (isset($data['categoryId'])) {
                    
                    // Remove unused fields
                    unset($data['pcid']);
                    unset($data['ccid']);
                    
                    // Create transaction model
                    $transaction = new Transaction($data);
                    
                    // uid
                    $transaction->setUserId($uid);
                    // Save
                    $this->get('Budget\TransactionMapper')->saveTransaction($transaction);
                    
                    return $this->redirect()->toRoute('transactions', array(
                                                                           'aid' => $transaction->getAccountId(),
                                                                           'month' => $transaction->getDate()->format('m'),
                                                                           'year' => $transaction->getDate()->format('Y'),
                                                                           'page' => $page,
                                                                           ));
                    
                }
                
            }
        }

        return array(
            'tid' => $tid,
            'form' => $form,
            'dt' => array('month' => $m, 'year' => $Y),
            't_type' => $transaction->getTransactionType(),
            'aid' => $transaction->getAccountId(),
            'page' => $page,
        );
    }

    /**
     * Delete transaction
     */
    public function deleteAction()
    {
        // User identifier
        $uid = $this->get('userId');
        
        $page = (int) $this->params()->fromRoute('page', 1);
        
        // Transaction identifier
        $tid = (int) $this->params()->fromRoute('tid', 0);
        if (!$tid) {
            return $this->redirect()->toRoute('transaction');
        }
        
        $transaction = $this->get('Budget\TransactionMapper')->getTransaction($tid, $uid);
        
        // Get date params from route
        $m = (int) $this->params()->fromRoute('month', date('m'));
        $Y = (int) $this->params()->fromRoute('year', date('Y'));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $tid = (int) $request->getPost('tid');
                $this->get('Budget\TransactionMapper')->deleteTransaction($tid, $uid);
            }

            // Redirect to the transaction list
            return $this->redirect()->toRoute('transactions', array(
                                                                   'aid' => $transaction->getAccountId(),
                                                                   'month' => (int)$m,
                                                                   'year' => (int)$Y,
                                                                   'page' => $page,
                                                                   ));
        }

        return array(
            'tid'    => $tid,
            'dt' => array('month' => $m, 'year' => $Y),
            'transaction' => $transaction,
            'page' => $page,
        );
    }
    
    /**
     * Add transfer between user bank accounts
     */
    public function transferAddAction()
    {
        // User identifier
        $uid = $this->get('userId');
        
        // Get account id
        $aid = (int) $this->params()->fromRoute('aid', 0);
        // Check if given account id is user accout
        if (!$this->get('User\AccountMapper')->isUserAccount($aid, $uid)) {
        
            return $this->redirect()->toRoute('transactions');
        
        }
        
        $form = new TransferForm();
        
        // Get user number of accounts
        $count = $this->get('User\AccountMapper')->getUserAccountCount($uid);
        
        // Flag - if there is only one bank account
        $ERR = false;
        
        if ($count > 1) {
            
            // Get user bank accounts
            $accounts = $this->get('User\AccountMapper')->getUserAccountsToSelect($uid);
            
            // Init transfer form
            $form->get('accountId')->setValueOptions($accounts);
            $form->get('transferAccountId')->setValueOptions($accounts);
            
            // Set bank account id from which we transfer money (default)
            $form->get('accountId')->setValue($aid);
            
            // Set the submit value
            $form->get('submit')->setValue('Dodaj');
            
            $request = $this->getRequest();
            if ($request->isPost()) {
            
                $postData = $request->getPost();
            
                $accountsValid = true;
            
                // Check bank accounts
                if ($postData['accountId'] == $postData['transferAccountId']) {
            
                    $form->get('transferAccountId')->setMessages(
                        array(
                            'Bank account must be different than above bank account!'
                        )
                    );
            
                    $accountsValid = false;
            
                }
            
                // Insert POST data into the form
                $form->setData($postData);
            
                $formFilters = new TransferFilter();
                $form->setInputFilter($formFilters->getInputFilter());
            
                if ($form->isValid() && $accountsValid) {
            
                    // Get data from form
                    $data = $form->getData();
            
                    // Get user transfer category id (hidden category for transfers)
                    $tcid = $this->get('User\CategoryMapper')->getTransferCategoryId($uid);
                    
                    // Outgoing transfer
                    $outTransaction = new Transaction();
                    $outTransaction->setAccountId($data['accountId']);
                    $outTransaction->setTransferAccountId($data['transferAccountId']);
                    $outTransaction->setUserId($uid);
                    $outTransaction->setTransactionType(Transaction::OUTGOING_TRANSFER);
                    $outTransaction->setCategoryId($tcid);
                    $outTransaction->setDate(new \DateTime($data['date']));
                    $outTransaction->setContent($data['content']);
                    $outTransaction->setValue($data['value']);
                    
                    // Incoming transfer
                    $inTransaction = new Transaction();
                    $inTransaction->setAccountId($data['transferAccountId']);
                    $inTransaction->setTransferAccountId($data['accountId']);
                    $inTransaction->setUserId($uid);
                    $inTransaction->setTransactionType(Transaction::INCOMING_TRANSFER);
                    $inTransaction->setCategoryId($tcid);
                    $inTransaction->setDate($data['date']);
                    $inTransaction->setContent($data['content']);
                    $inTransaction->setValue($data['value']);
            
                    // Save transfer
                    $this->get('Budget\TransferMapper')->saveTransfer($outTransaction, $inTransaction);
                    
                    return $this->redirect()->toRoute('transactions', array(
                            'aid' => $aid,
                            'month' => $outTransaction->getDate()->format('m'),
                            'year' => $outTransaction->getDate()->format('Y'),
                            'page' => 1,
                    ));
            
                }
            }
            
        } else {
            
            $ERR = true;
            
        }
        
        return array(
            'form' => $form,
            'aid' => $aid,
            'ERR' => $ERR,
        );
    }
    
    /**
     * Edit transfer between user bank accounts
     */
    public function transferEditAction()
    {
        // User identifier
        $uid = $this->get('userId');
        
        $page = (int) $this->params()->fromRoute('page', 1);
        
        // Get date from the url
        $m = (int) $this->params()->fromRoute('month', date('m'));
        $Y = (int) $this->params()->fromRoute('year', date('Y'));
        
        // Get transaction id
        $tid = (int) $this->params()->fromRoute('tid', 0);
        // Check if given account id is user accout
        if (!$tid) {
        
            return $this->redirect()->toRoute('transactions');
        
        }
        
        // Get account id
        $aid = (int) $this->params()->fromRoute('aid', 0);
        // Check if given account id is user accout
        if (!$this->get('User\AccountMapper')->isUserAccount($aid, $uid)) {
        
            return $this->redirect()->toRoute('transactions');
        
        }
        
        // Get transaction data
        $transaction = $this->get('Budget\TransactionMapper')->getTransaction($tid, $uid);
        
        // Check if there is the transaction
        if ($transaction === null) {
            return $this->redirect()->toRoute('transactions');
        }
        
        // Check if the given transaction is transfer
        if (!($transaction->getTransactionType()==Transaction::OUTGOING_TRANSFER || $transaction->getTransactionType()==Transaction::INCOMING_TRANSFER)) {
            return $this->redirect()->toRoute('transactions');
        }
        
        $form = new TransferForm();
        
        // Get user number of accounts
        $count = $this->get('User\AccountMapper')->getUserAccountCount($uid);
        
        // Flag - if there is only one bank account
        $ERR = false;
        
        if ($count > 1) {
        
            // Get user bank accounts
            $accounts = $this->get('User\AccountMapper')->getUserAccountsToSelect($uid);
        
            // Init transfer form
            $form->get('accountId')->setValueOptions($accounts);
            $form->get('transferAccountId')->setValueOptions($accounts);
        
            // Insert data into the form
            $form->setData($transaction->getArrayCopy());
            
            // Revert bank accounts
            if ($transaction->getTransactionType()==Transaction::INCOMING_TRANSFER) {
                
                $form->get('accountId')->setValue($transaction->getTransferAccountId());
                $form->get('transferAccountId')->setValue($transaction->getAccountId());
                
            }
            
            // Set the submit value
            $form->get('submit')->setValue('Edytuj');
        
            $request = $this->getRequest();
            if ($request->isPost()) {
        
                $postData = $request->getPost();
        
                $accountsValid = true;
        
                // Check bank accounts
                if ($postData['accountId'] == $postData['transferAccountId']) {
        
                    $form->get('transferAccountId')->setMessages(
                        array(
                            'Bank account must be different than above bank account!'
                        )
                    );
        
                    $accountsValid = false;
        
                }
        
                // Insert POST data into the form
                $form->setData($postData);
        
                $formFilters = new TransferFilter();
                $form->setInputFilter($formFilters->getInputFilter());
        
                if ($form->isValid() && $accountsValid) {
        
                    // Get data from form
                    $data = $form->getData();
        
                    // Get user transfer category id (hidden category for transfers)
                    $tcid = $transaction->getCategoryId();
                    
                    // Get transaction identifiers
                    if ($transaction->getTransactionType()==Transaction::OUTGOING_TRANSFER) {
                        $outId = $transaction->getTransactionId();
                        $tr = $this->get('Budget\TransferMapper')->getTransaction($tid, $uid, Transaction::INCOMING_TRANSFER);
                        $inId = $tr['transaction']->getTransactionId();
                    } else {
                        $tr = $this->get('Budget\TransferMapper')->getTransaction($tid, $uid, Transaction::OUTGOING_TRANSFER);
                        $outId = $tr['transaction']->getTransactionId();
                        $inId = $transaction->getTransactionId();
                    }
        
                    // Outgoing transfer
                    $outTransaction = new Transaction();
                    $outTransaction->setTransactionId($outId);
                    $outTransaction->setAccountId($data['accountId']);
                    $outTransaction->setTransferAccountId($data['transferAccountId']);
                    $outTransaction->setUserId($uid);
                    $outTransaction->setTransactionType(Transaction::OUTGOING_TRANSFER);
                    $outTransaction->setCategoryId($tcid);
                    $outTransaction->setDate(new \DateTime($data['date']));
                    $outTransaction->setContent($data['content']);
                    $outTransaction->setValue($data['value']);
        
                    // Incoming transfer
                    $inTransaction = new Transaction();
                    $inTransaction->setTransactionId($inId);
                    $inTransaction->setAccountId($data['transferAccountId']);
                    $inTransaction->setTransferAccountId($data['accountId']);
                    $inTransaction->setUserId($uid);
                    $inTransaction->setTransactionType(Transaction::INCOMING_TRANSFER);
                    $inTransaction->setCategoryId($tcid);
                    $inTransaction->setDate(new \DateTime($data['date']));
                    $inTransaction->setContent($data['content']);
                    $inTransaction->setValue($data['value']);
        
                    // Save transactions
                    $this->get('Budget\TransferMapper')->saveTransfer($outTransaction, $inTransaction);
        
                    return $this->redirect()->toRoute('transactions', array(
                            'aid' => $aid,
                            'month' => $outTransaction->getDate()->format('m'),
                            'year' => $outTransaction->getDate()->format('Y'),
                            'page' => $page,
                    ));
        
                }
            }
        
        } else {
        
            $ERR = true;
        
        }
        
        return array(
            'form' => $form,
            'tid' => $transaction->getTransactionId(),
            'aid' => $aid,
            'ERR' => $ERR,
            'dt' => array('month' => $m, 'year' => $Y),
            'page' => $page,
        );
    }
    
    /**
     * Delete user transfer
     */
    public function transferDeleteAction()
    {
        // User id
        $uid = $this->get('userId');
        
        $page = (int) $this->params()->fromRoute('page', 1);
        
        $tid = (int) $this->params()->fromRoute('tid', 0);
        if (!$tid) {
            return $this->redirect()->toRoute('transaction');
        }
        
        $transaction = $this->get('Budget\TransactionMapper')->getTransaction($tid, $uid);
        
        // Check if there is the transaction
        if ($transaction === null) {
            return $this->redirect()->toRoute('transactions');
        }
        
        // Check if the given transaction is transfer
        if (!($transaction->getTransactionType()==Transaction::OUTGOING_TRANSFER || $transaction->getTransactionType()==Transaction::INCOMING_TRANSFER)) {
            return $this->redirect()->toRoute('transactions');
        }
        
        // Get date from address
        $m = (int) $this->params()->fromRoute('month', date('m'));
        $Y = (int) $this->params()->fromRoute('year', date('Y'));
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');
        
            if ($del == 'Yes') {
                $tid = (int) $request->getPost('tid');
                
                $this->get('Budget\TransferMapper')->deleteTransfer($transaction);
            }
        
            // Redirect to the transaction list
            return $this->redirect()->toRoute('transactions', array(
                    'aid' => $transaction->getAccountId(),
                    'month' => (int)$m,
                    'year' => (int)$Y,
                    'page' => $page,
            ));
        }
        
        return array(
                'tid'    => $tid,
                'dt' => array('month' => $m, 'year' => $Y),
                'transaction' => $transaction,
                'page' => $page,
        );
    }
}
