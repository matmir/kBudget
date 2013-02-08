<?php
/**
    @author Mateusz Mirosławski
    
    Kontroler obsługujący transakcje (dodawanie, edycja, usuwanie, sortowanie)
*/

namespace Budget\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Budget\Model\Transaction;
use Budget\Model\TransactionMapper;

use Budget\Model\Category;
use Budget\Model\CategoryMapper;

use Budget\Form\TransactionForm;
use Budget\Form\TransactionFilter;

use Budget\Form\TransactionRangeSelectForm;
use Budget\Form\TransactionRangeSelectFilter;

class TransactionController extends AbstractActionController
{
    protected $transactionMapper;
    protected $categoryMapper;
    
    // Pobiera mapper do bazy z transakcjami
    private function getTransactionMapper()
    {
        if (!$this->transactionMapper) {
            $sm = $this->getServiceLocator();
            $this->transactionMapper = new TransactionMapper($sm->get('adapter'));
        }
        
        return $this->transactionMapper;
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
    
    // Głowna akcja (strona główna transakcji)
    public function indexAction()
    {
        // Identyfikator zalogowanego usera
        $uid = $this->getServiceLocator()->get('user_data')->uid;
        
        // Pobranie numeru strony
        $page = (int) $this->params()->fromRoute('page', 1);
        
        // Formularz od wyboru zakresu transakcji
        $formRange = new TransactionRangeSelectForm();
        
        // Pobranie miesiąca z adresu
        $m = (int) $this->params()->fromRoute('month', date('m'));
        // Pobranie roku z adresu
        $Y = (int) $this->params()->fromRoute('year', date('Y'));
        
        /* SPRAWDZIĆ PODANĄ DATĘ!! */
        
        // Uzupełnienie formularza filtrującego
        $formRange->setData(array(
                                  'month' => $m,
                                  'year' => $Y,
                                  ));
        
        // Dodanie początkowego '0' w miesiącu (dla zapytania SQL)
        $ms = ($m < 10) ? ((string)'0'.$m) : ((string)$m);
        // Złożenie daty
        $dt = $Y.'-'.$ms;
        
        // Parametry daty
        $date_param = array(
            'type' => 'month',
            'dt_month' => $dt,
        );
        
        // Pobranie sumy wydatków
        $sum_expense = $this->getTransactionMapper()->getSumOfTransactions($uid, $date_param, 1);
        // Pobranie sumy przychodów
        $sum_profit = $this->getTransactionMapper()->getSumOfTransactions($uid, $date_param, 0);
        
        // Bilans
        $balance = $sum_profit - $sum_expense;
        
        return array(
            'transactions' => $this->getTransactionMapper()->getTransactions($uid, $date_param, -1, $page, true),
            'formRange' => $formRange,
            'dt' => array('month' => $m, 'year' => $Y),
            'sum_expense' => $sum_expense,
            'sum_profit' => $sum_profit,
            'balance' => $balance,
            'page' => $page,
        );
    }
    
    // Filtracja transakcji
    public function filterAction()
    {
        // Identyfikator zalogowanego usera
        $uid = $this->getServiceLocator()->get('user_data')->uid;
        
        // Minimalny rok w transakcjach usera
        $minYear = $this->getTransactionMapper()->getMinYearOfTransaction($uid);
        
        // Formularz od wyboru zakresu transakcji
        $formRange = new TransactionRangeSelectForm();
        // Filtracja formularza
        $formFilters = new TransactionRangeSelectFilter($minYear);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $formRange->setInputFilter($formFilters->getInputFilter());
            $formRange->setData($request->getPost());

            if ($formRange->isValid()) {
                $Y = $formRange->get('year')->getValue();
                $m = $formRange->get('month')->getValue();
                
                // Przekierowanie do listy transakcji
                return $this->redirect()->toRoute('transaction', array(
                                                                       'month' => (int)$m,
                                                                       'year' => (int)$Y,
                                                                       'page' => 1,
                                                                       ));
            } else { // Błąd formularze (ktoś coś kombinuje)
                // Przekierowanie do głównej strony
                return $this->redirect()->toRoute('transaction', array(
                                                                    'month' => date('m'),
                                                                    'year' => date('Y'),
                                                                    'page' => 1,
                                                                    ));
            }
        } else { // Brak parametrów
            // Przekierowanie do głównej strony
            return $this->redirect()->toRoute('transaction', array(
                                                                    'month' => date('m'),
                                                                    'year' => date('Y'),
                                                                    'page' => 1,
                                                                    ));
        }
    }

    // Dodanie nowej transakcji
    public function addAction()
    {
        // Identyfikator zalogowanego usera
        $uid = $this->getServiceLocator()->get('user_data')->uid;
        
        // Pobranie rodzaju transakcji
        $t_type= (int) $this->params()->fromRoute('type', 1);
        // Poprawność typu
        if (!($t_type == 0 || $t_type==1)) {
            // Przekierowanie do głównej strony
            return $this->redirect()->toRoute('main');
        }
        
        // Formularz
        $form = new TransactionForm();
        
        // Lista kategori usera
        $user_cat = $this->getCategoryMapper()->getUserCategoriesToSelect($uid, $t_type);
        $form->get('cid')->setValueOptions($user_cat);
        
        // Knefel
        $form->get('submit')->setValue('Dodaj');
        // Typ transakcji w formularzu
        $form->get('t_type')->setValue($t_type);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $transaction = new Transaction();
            
            $form->setData($request->getPost());
            
            // Spr. czy wybrano kategorię z listy -> wymagamy nowej kategorii gdy nie wybrano)
            $ncr = ($form->get('cid')->getValue()==0)?(true):(false);
            
            // Filtry
            $formFilters = new TransactionFilter($ncr);
            $form->setInputFilter($formFilters->getInputFilter());
            
            if ($form->isValid()) {
                
                // Uzupełnienie modelu danymi z formularza
                $transaction->exchangeArray($form->getData());
                
                // spr. czy podano nową kategorię
                $c_name = $form->get('c_name')->getValue();
                if (($c_name!=null) && ($ncr==true)) {
                    // spr. czy taka kategoria istnieje (jeśli tak, to zwraca cid)
                    $n_cid = $this->getCategoryMapper()->isCategoryNameExists($c_name, $t_type, $uid);
                    if ($n_cid == 0) { // Nie istnieje - dodać nową
                        $new_category = new Category();
                        $new_category->uid = $uid;
                        $new_category->c_type = $t_type;
                        $new_category->c_name = $c_name;
                        // Dodanie
                        $this->getCategoryMapper()->saveCategory($new_category);
                        // Pobranie nowego id-a kategorii
                        $n_cid = $this->getCategoryMapper()->isCategoryNameExists($c_name, $t_type, $uid);
                    }
                    
                    // Nadpisać pole transakcji nowym identyfikatorem kategorii
                    $transaction->cid = (int)$n_cid;
                }
                
                // uid
                $transaction->uid = $uid;
                // Zapis
                $this->getTransactionMapper()->saveTransaction($transaction);
                
                // Data dodanej transakcji
                $t_dt = explode('-', $transaction->t_date);
                
                // Przekierowanie do listy transakcji do daty z dodawanej transakcji
                return $this->redirect()->toRoute('transaction', array(
                                                                       'month' => $t_dt[1],
                                                                       'year' => $t_dt[0],
                                                                       'page' => 1,
                                                                       ));
            }
        }
        return array(
            'form' => $form,
            't_type' => $t_type,
        );
    }

    // Edycja transakcji
    public function editAction()
    {
        // Identyfikator zalogowanego usera
        $uid = $this->getServiceLocator()->get('user_data')->uid;
        
        // Pobranie numeru strony
        $page = (int) $this->params()->fromRoute('page', 1);
        
        // Pobranie miesiąca z adresu
        $m = (int) $this->params()->fromRoute('month', date('m'));
        // Pobranie roku z adresu
        $Y = (int) $this->params()->fromRoute('year', date('Y'));
        
        // Pobranie identyfikatora transakcji
        $tid = (int) $this->params()->fromRoute('tid', 0);
        if (!$tid) {
            return $this->redirect()->toRoute('main');
        }
        
        // Pobranie danych transakcji
        $transaction = $this->getTransactionMapper()->getTransaction($tid, $uid);

        // Formularz
        $form  = new TransactionForm();
        
        // Lista kategori usera
        $user_cat = $this->getCategoryMapper()->getUserCategoriesToSelect($uid, $transaction->t_type);
        $form->get('cid')->setValueOptions($user_cat);
        
        // Wstawienie danych do formularza
        $form->bind($transaction);
        
        // Knefel
        $form->get('submit')->setAttribute('value', 'Edytuj');
        // Wyczyszczenie formatki od nowej kategorii
        $form->get('c_name')->setValue('');

        $request = $this->getRequest();
        if ($request->isPost()) {
            // Uzupełnienie formularza zmienionymi danymi
            $form->setData($request->getPost());
            
            // Spr. czy wybrano kategorię z listy -> wymagamy nowej kategorii gdy nie wybrano)
            $ncr = ($form->get('cid')->getValue()==0)?(true):(false);
            
            // Filtry
            $formFilters = new TransactionFilter($ncr);
            $form->setInputFilter($formFilters->getInputFilter());

            if ($form->isValid()) {
                
                // Uzupełnienie modelu nowymi danymi z formularza
                $transaction = $form->getData();
                
                // spr. czy podano nową kategorię
                $c_name = $form->get('c_name')->getValue();
                if (($c_name!=null) && ($ncr==true)) {
                    // spr. czy taka kategoria istnieje (jeśli tak, to zwraca cid)
                    $n_cid = $this->getCategoryMapper()->isCategoryNameExists($c_name, $transaction->t_type, $uid);
                    if ($n_cid == 0) { // Nie istnieje - dodać nową
                        $new_category = new Category();
                        $new_category->uid = $uid;
                        $new_category->c_type = $transaction->t_type;
                        $new_category->c_name = $c_name;
                        // Dodanie
                        $this->getCategoryMapper()->saveCategory($new_category);
                        // Pobranie nowego id-a kategorii
                        $n_cid = $this->getCategoryMapper()->isCategoryNameExists($c_name, $transaction->t_type, $uid);
                    }
                    
                    // Nadpisać pole transakcji nowym identyfikatorem kategorii
                    $transaction->cid = (int)$n_cid;
                }
                
                $transaction->uid = $uid;
                // Zapis
                $this->getTransactionMapper()->saveTransaction($transaction);

                // Data dodanej transakcji
                $t_dt = explode('-', $transaction->t_date);

                // Przekierowanie do listy transakcji do daty z edytowanej transakcji
                return $this->redirect()->toRoute('transaction', array(
                                                                       'month' => $t_dt[1],
                                                                       'year' => $t_dt[0],
                                                                       'page' => $page,
                                                                       ));
            }
        }

        return array(
            'tid' => $tid,
            'form' => $form,
            'dt' => array('month' => $m, 'year' => $Y),
            't_type' => $transaction->t_type,
            'page' => $page,
        );
    }

    // Usunięcie transakcji
    public function deleteAction()
    {
        // Identyfikator zalogowanego usera
        $uid = $this->getServiceLocator()->get('user_data')->uid;
        
        // Pobranie numeru strony
        $page = (int) $this->params()->fromRoute('page', 1);
        
        // Identyfikator transakcji
        $tid = (int) $this->params()->fromRoute('tid', 0);
        if (!$tid) {
            return $this->redirect()->toRoute('transaction');
        }
        
        // Pobranie miesiąca z adresu
        $m = (int) $this->params()->fromRoute('month', date('m'));
        // Pobranie roku z adresu
        $Y = (int) $this->params()->fromRoute('year', date('Y'));

        $request = $this->getRequest();
        if ($request->isPost()) {
            $del = $request->getPost('del', 'No');

            if ($del == 'Yes') {
                $tid = (int) $request->getPost('tid');
                $this->getTransactionMapper()->deleteTransaction($tid, $uid);
            }

            // Przekierowanie do listy transakcji
            return $this->redirect()->toRoute('transaction', array(
                                                                   'month' => (int)$m,
                                                                   'year' => (int)$Y,
                                                                   'page' => $page,
                                                                   ));
        }

        return array(
            'tid'    => $tid,
            'dt' => array('month' => $m, 'year' => $Y),
            'transaction' => $this->getTransactionMapper()->getTransaction($tid, $uid),
            'page' => $page,
        );
    }
}