<?php

namespace Budget\Controller;

use Base\Controller\BaseController;

use Budget\Model\Transaction;
use User\Model\Category;

use Budget\Form\TransactionForm;
use Budget\Form\TransactionFilter;

use Budget\Form\TransactionFilterForm;
use Budget\Form\TransactionFilterFormFilter;

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
        
        // Get user bank accounts
        $accounts = $this->get('User\AccountMapper')->getUserAccountsToSelect($uid);
        
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
            'categories' => $categories,
            'formRange' => $form,
            'dt' => array('month' => $m, 'year' => $Y),
            'aid' => $aid,
            'sum_expense' => $sum_expense,
            'sum_profit' => $sum_profit,
            'balance' => $monthBalance,
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
}