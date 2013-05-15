<?php

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

/**
 * Transaction controller
 * 
 * @author Mateusz Mirosławski
 *
 */
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
            $aid = $this->get('User\UserMapper')->getUser($uid)->default_aid;
            
        } else {
            
            // Check if given account id is user accout
            if (!$this->get('User\AccountMapper')->isUserAccount($aid, $uid)) {
                
                // Load default account id
                $aid = $this->get('User\UserMapper')->getUser($uid)->default_aid;
                
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
        $sum_expense = $this->get('Budget\TransactionMapper')->getSumOfTransactions($uid, $aid, $date_param, 1);
        $sum_profit = $this->get('Budget\TransactionMapper')->getSumOfTransactions($uid, $aid, $date_param, 0);
        $monthBalance = $sum_profit - $sum_expense;
        
        // Get transactions
        $transactions = $this->get('Budget\TransactionMapper')->getTransactions($uid, $aid, $date_param, -1, $page, true);
        
        // Get categories names (tid, main_category, sub_category)
        $transactionsCopy = clone $transactions;
        $categories = array();
        $accountTransfers = array();
        foreach ($transactionsCopy as $transaction) {
            
            // Check if category has parent
            if ($transaction->pcid === null) {
                // Main category
                $categories[$transaction->tid] = array($transaction->c_name, null);
            } else {
                // Subcategory
                $parent = $this->get('User\CategoryMapper')->getCategory($transaction->pcid, $uid);
                
                $categories[$transaction->tid] = array($parent->c_name, $transaction->c_name);
            }
            
        }
        
        return array(
            'transactions' => $transactions,
            'accountsNames' => $accounts,
            'categories' => $categories,
            'formRange' => $form,
            'dt' => array('month' => $m, 'year' => $Y),
            'aid' => $aid,
            'sum_expense' => $sum_expense,
            'sum_profit' => $sum_profit,
            'accountBalance' => $account->balance,
            'monthBalance' => $monthBalance,
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
        $t_type= (int) $this->params()->fromRoute('type', 1);
        // Check type
        if (!($t_type == 0 || $t_type==1)) {
            
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
        $form->get('aid')->setValue($aid);
        
        // Get user main categories
        $userMainCat = $this->get('User\CategoryMapper')->getUserCategoriesToSelect($uid, $t_type);
        $form->get('pcid')->setValueOptions($userMainCat);
        
        // Set the submit value
        $form->get('submit')->setValue('Dodaj');
        // Set the transaction type in form
        $form->get('t_type')->setValue($t_type);

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
                    
                    $data['cid'] = (int)$data['pcid'];
                    
                } else { // there is subcategory
                    
                    $data['cid'] = (int)$data['ccid'];
                    
                }
                
                // Check if there is correct cid
                if (isset($data['cid'])) {
                    
                    // Remove unused fields
                    unset($data['pcid']);
                    unset($data['ccid']);
                    
                    // Create transaction model
                    $transaction = new Transaction($data);
                    
                    // uid
                    $transaction->uid = $uid;
                    // Save
                    $this->get('Budget\TransactionMapper')->saveTransaction($transaction);
                    
                    // Transaction date
                    $t_dt = explode('-', $transaction->t_date);
                    
                    return $this->redirect()->toRoute('transactions', array(
                                                                           'aid' => $aid,
                                                                           'month' => $t_dt[1],
                                                                           'year' => $t_dt[0],
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
        
        $form  = new TransactionForm();
        
        // User main categories
        $userMainCat = $this->get('User\CategoryMapper')->getUserCategoriesToSelect($uid, $transaction->t_type);
        $form->get('pcid')->setValueOptions($userMainCat);
        
        // Check if the transaction category has subcategories
        $category = $this->get('User\CategoryMapper')->getCategory($transaction->cid, $uid);
        
        if ($category->pcid == null) { // this is main category
            
            $data['pcid'] = $category->cid;
            $data['ccid'] = null;
            
            // User subcategories
            $userSubCat = $this->get('User\CategoryMapper')->getUserCategoriesToSelect($uid, $transaction->t_type, $category->cid);
            
        } else { // this is subcategory
            
            $data['pcid'] = $category->pcid;
            $data['ccid'] = $category->cid;
            
            // User subcategories
            $userSubCat = $this->get('User\CategoryMapper')->getUserCategoriesToSelect($uid, $transaction->t_type, $category->pcid);
            
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
                
                $ccid = $this->get('User\CategoryMapper')->getUserCategoriesToSelect($uid, $transaction->t_type, $postData['pcid']);
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
                    
                    $data['cid'] = (int)$data['pcid'];
                    
                } else { // there is subcategory
                    
                    $data['cid'] = (int)$data['ccid'];
                    
                }
                
                // Check if there is correct cid
                if (isset($data['cid'])) {
                    
                    // Remove unused fields
                    unset($data['pcid']);
                    unset($data['ccid']);
                    
                    // Create transaction model
                    $transaction = new Transaction($data);
                    
                    // uid
                    $transaction->uid = $uid;
                    // Save
                    $this->get('Budget\TransactionMapper')->saveTransaction($transaction);
                    
                    // Transaction date
                    $t_dt = explode('-', $transaction->t_date);
                    
                    return $this->redirect()->toRoute('transactions', array(
                                                                           'aid' => $transaction->aid,
                                                                           'month' => $t_dt[1],
                                                                           'year' => $t_dt[0],
                                                                           'page' => $page,
                                                                           ));
                    
                }
                
            }
        }

        return array(
            'tid' => $tid,
            'form' => $form,
            'dt' => array('month' => $m, 'year' => $Y),
            't_type' => $transaction->t_type,
            'aid' => $transaction->aid,
            'page' => $page,
        );
    }

    // Usunięcie transakcji
    public function deleteAction()
    {
        // Identyfikator zalogowanego usera
        $uid = $this->get('userId');
        
        // Pobranie numeru strony
        $page = (int) $this->params()->fromRoute('page', 1);
        
        // Identyfikator transakcji
        $tid = (int) $this->params()->fromRoute('tid', 0);
        if (!$tid) {
            return $this->redirect()->toRoute('transaction');
        }
        
        $transaction = $this->get('Budget\TransactionMapper')->getTransaction($tid, $uid);
        
        // Pobranie miesiąca z adresu
        $m = (int) $this->params()->fromRoute('month', date('m'));
        // Pobranie roku z adresu
        $Y = (int) $this->params()->fromRoute('year', date('Y'));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $tid = (int) $request->getPost('tid');
                $this->get('Budget\TransactionMapper')->deleteTransaction($tid, $uid);
            }

            // Przekierowanie do listy transakcji
            return $this->redirect()->toRoute('transactions', array(
                                                                   'aid' => $transaction->aid,
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
            $form->get('aid')->setValueOptions($accounts);
            $form->get('taid')->setValueOptions($accounts);
            
            // Set bank account id from which we transfer money (default)
            $form->get('aid')->setValue($aid);
            
            // Set the submit value
            $form->get('submit')->setValue('Dodaj');
            
            $request = $this->getRequest();
            if ($request->isPost()) {
            
                $postData = $request->getPost();
            
                $accountsValid = true;
            
                // Check bank accounts
                if ($postData['aid'] == $postData['taid']) {
            
                    $form->get('taid')->setMessages(
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
                    $outTransaction->aid = $data['aid'];
                    $outTransaction->taid = $data['taid'];
                    $outTransaction->uid = $uid;
                    $outTransaction->t_type = 2;
                    $outTransaction->cid = $tcid;
                    $outTransaction->t_date = $data['t_date'];
                    $outTransaction->t_content = $data['t_content'];
                    $outTransaction->t_value = $data['t_value'];
                    
                    // Incoming transfer
                    $inTransaction = new Transaction();
                    $inTransaction->aid = $data['taid'];
                    $inTransaction->taid = $data['aid'];
                    $inTransaction->uid = $uid;
                    $inTransaction->t_type = 3;
                    $inTransaction->cid = $tcid;
                    $inTransaction->t_date = $data['t_date'];
                    $inTransaction->t_content = $data['t_content'];
                    $inTransaction->t_value = $data['t_value'];
            
                    // Save transfer
                    $this->get('Budget\TransferMapper')->saveTransfer($outTransaction, $inTransaction);
                    
                    // Transaction date
                    $t_dt = explode('-', $outTransaction->t_date);
                    
                    return $this->redirect()->toRoute('transactions', array(
                            'aid' => $aid,
                            'month' => $t_dt[1],
                            'year' => $t_dt[0],
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
        if (!($transaction->t_type==2 || $transaction->t_type==3)) {
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
            $form->get('aid')->setValueOptions($accounts);
            $form->get('taid')->setValueOptions($accounts);
        
            // Insert data into the form
            $form->setData($transaction->getArrayCopy());
            
            // Revert bank accounts
            if ($transaction->t_type==3) {
                
                $form->get('aid')->setValue($transaction->taid);
                $form->get('taid')->setValue($transaction->aid);
                
            }
            
            // Set the submit value
            $form->get('submit')->setValue('Edytuj');
        
            $request = $this->getRequest();
            if ($request->isPost()) {
        
                $postData = $request->getPost();
        
                $accountsValid = true;
        
                // Check bank accounts
                if ($postData['aid'] == $postData['taid']) {
        
                    $form->get('taid')->setMessages(
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
                    $tcid = $transaction->cid;
                    
                    // Get transaction identifiers
                    if ($transaction->t_type==2) {
                        $outId = $transaction->tid;
                        $tr = $this->get('Budget\TransferMapper')->getTransaction($tid, $uid, 3);
                        $inId = $tr['transaction']->tid;
                    } else {
                        $tr = $this->get('Budget\TransferMapper')->getTransaction($tid, $uid, 2);
                        $outId = $tr['transaction']->tid;
                        $inId = $transaction->tid;
                    }
        
                    // Outgoing transfer
                    $outTransaction = new Transaction();
                    $outTransaction->tid = $outId;
                    $outTransaction->aid = $data['aid'];
                    $outTransaction->taid = $data['taid'];
                    $outTransaction->uid = $uid;
                    $outTransaction->t_type = 2;
                    $outTransaction->cid = $tcid;
                    $outTransaction->t_date = $data['t_date'];
                    $outTransaction->t_content = $data['t_content'];
                    $outTransaction->t_value = $data['t_value'];
        
                    // Incoming transfer
                    $inTransaction = new Transaction();
                    $inTransaction->tid = $inId;
                    $inTransaction->aid = $data['taid'];
                    $inTransaction->taid = $data['aid'];
                    $inTransaction->uid = $uid;
                    $inTransaction->t_type = 3;
                    $inTransaction->cid = $tcid;
                    $inTransaction->t_date = $data['t_date'];
                    $inTransaction->t_content = $data['t_content'];
                    $inTransaction->t_value = $data['t_value'];
        
                    // Save transactions
                    $this->get('Budget\TransferMapper')->saveTransfer($outTransaction, $inTransaction);
        
                    // Transaction date
                    $t_dt = explode('-', $outTransaction->t_date);
        
                    return $this->redirect()->toRoute('transactions', array(
                            'aid' => $aid,
                            'month' => $t_dt[1],
                            'year' => $t_dt[0],
                            'page' => $page,
                    ));
        
                }
            }
        
        } else {
        
            $ERR = true;
        
        }
        
        return array(
            'form' => $form,
            'tid' => $transaction->tid,
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
        if (!($transaction->t_type==2 || $transaction->t_type==3)) {
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
                    'aid' => $transaction->aid,
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