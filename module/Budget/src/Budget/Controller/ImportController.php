<?php
/**
    @author Mateusz Mirosławski
    
    Kontroler zajmujący się importem wyciągów
*/

namespace Budget\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Budget\Model\Banking\mBank;

use Budget\Model\Import;
use Budget\Model\ImportMapper;

use Budget\Form\LoadBankFileForm;
use Budget\Form\LoadBankFileFormFilter;

use Budget\Form\TransactionImportForm;
use Budget\Form\TransactionImportFormFilter;

use User\Model\Category;
use User\Model\CategoryMapper;

use Budget\Model\Transaction;
use Budget\Model\TransactionMapper;

use Zend\File\Transfer\Adapter\Http;

class ImportController extends AbstractActionController
{
    protected $importMapper;
    protected $categoryMapper;
    protected $transactionMapper;
    
    // Pobiera mapper do bazy z importem
    private function getImportMapper()
    {
        if (!$this->importMapper) {
            $sm = $this->getServiceLocator();
            $this->importMapper = new ImportMapper($sm->get('adapter'));
        }
        
        return $this->importMapper;
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
    
    // Pobiera mapper do bazy z transakcjami
    private function getTransactionMapper()
    {
        if (!$this->transactionMapper) {
            $sm = $this->getServiceLocator();
            $this->transactionMapper = new TransactionMapper($sm->get('adapter'));
        }
        
        return $this->transactionMapper;
    }
    
    // Główna strona
    public function indexAction()
    {
        // Identyfikator zalogowanego usera
        $uid = $this->getServiceLocator()->get('uid');
        
        // Aktualny import
        $import = $this->getImportMapper()->getUserImport($uid);
        
        // Spr czy są dane
        if ($import == null) {
            
            // Ustawienia ładowania wyciągów
            $upload_config = $this->getServiceLocator()->get('upload_cfg');
            
            // Lista obsługiwanych banków
            $bankList = Import::getBankList();
            
            // Formularz
            $form = new LoadBankFileForm();
            // Wprowadzenie listy banków
            $form->get('bank')->setValueOptions($bankList);
            // Filtry
            $formFilters = new LoadBankFileFormFilter($upload_config);
    
            $request = $this->getRequest();
            if ($request->isPost()) {
                
                $form->setInputFilter($formFilters->getInputFilter());
                
                // Przygotowanie danych z formularza
                $nonFile = $request->getPost()->toArray();
                $File = $this->params()->fromFiles('upload_file');
                $request_data = array_merge($nonFile, array('upload_file' => $File));
                
                $form->setData($request_data);
                
                if ($form->isValid()) {
                    
                    // Adapter do ładowania plików na serwer
                    $upload = new Http();
                    
                    // Typ ładowanego pliku
                    $file_info = $upload->getFileInfo();
                    $file_type = $file_info['upload_file']['type'];
                    
                    // Spr. zgodności typów
                    if ($file_type == $upload_config['fileType']) {
                        
                        // Nazwa pliku na serwerze
                        $FILE_NAME = md5($uid.date('Y-m-d H:i:s')).'.'.$upload_config['fileExtension'];
                        
                        // Katalog do ładowania plików
                        $upload->setDestination($upload_config['upload_dir']);
                        
                        // Filtr zmieniający nazwę ładowanego pliku
                        $destination = $upload_config['upload_dir'].$FILE_NAME;
                        $upload->setFilters(array('Rename' => array('target' => $destination, 'overwrite' => true)));
                        
                        // Załadowanie pliku
                        if ($upload->receive($File['name'])) {
                            
                            // Informacje dotyczące importu
                            $new_import = new Import();
                            $new_import->uid = $uid;
                            $new_import->fname = $FILE_NAME;
                            $new_import->bank = $bankList[$form->get('bank')->getValue()];
                            $new_import->fpos = 0;
                            $new_import->nfpos = 0;
                            $new_import->counted = 0;
                            
                            // Utworzenie obiektu Banku do zliczenia liczby transakcji
                            switch ($new_import->bank) {
                                case 'mBank': $bank = new mBank($destination, 0, $upload_config['maxParseLines']); break;
                            }
                            // Spr. banku
                            if (!(isset($bank))) {
                                throw new \Exception('Błędna nazwa banku!');
                            }
                            // Liczba transakcji
                            $new_import->count = $bank->count();
                            
                            // Zapis do bazy
                            $this->getImportMapper()->setUserImport($new_import);
                            
                            // Przekierowanie do zatwierdzania transakcji
                            return $this->redirect()->toRoute('import/commit');
                            
                        }
                        
                    } else { // Błędny typ ładowanego pliku
                        
                        $form->setMessages(array('upload_file' => array('badFileType' => 'Bad file type!')));
                        
                    }
                }
                
            }
            
            return array(
                'form' => $form,
            );
            
        } else { // są dane
            
            // Przekierowanie do zatwierdzania transakcji
            return $this->redirect()->toRoute('import/commit');
            
        }
    }
    
    // Zatwierdzanie importowanych transakcji
    public function commitAction()
    {
        // Identyfikator zalogowanego usera
        $uid = $this->getServiceLocator()->get('uid');
        
        // Aktualny import
        $import = $this->getImportMapper()->getUserImport($uid);
        
        // Flaga wystąpienia błędu (0 - ok, 1 - błąd parsowania)
        $ERR = 0;
        
        // Czy są dane do importu
        if ($import) {
            
            // Ustawienia ładowania wyciągów
            $upload_config = $this->getServiceLocator()->get('upload_cfg');
            
            // Pozostałe transakcje do zaimportowania
            $not_imported_count = $import->count - $import->counted;
            
            // Spr. ograniczenia na przetwarzanie za jednym razem
            $tr_count = ($not_imported_count>$upload_config['maxParseLines'])?($upload_config['maxParseLines']):($not_imported_count);
            
            // Kategorie dla wydatków
            $user_cat_expense = $this->getCategoryMapper()->getUserCategoriesToSelect($uid, 1);
            // Kategorie dla przychodów
            $user_cat_profit = $this->getCategoryMapper()->getUserCategoriesToSelect($uid, 0);
            
            // Formularz
            $form = new TransactionImportForm($tr_count);
            
            // Spr. czy są dane z formularza
            $request = $this->getRequest();
            if ($request->isPost()) {
                
                // Dane z formularza
                $request_data = $request->getPost();
                
                // Wygenerowanie wszystkich kategorii oraz uzupełnienie flag nowych kategorii
                $ncr = array();
                for ($i=0; $i<$tr_count; $i++) {
                    
                    // Lista kategorii
                    $form->get('cid'.$i)->setValueOptions(($request_data['t_type'.$i]==0)?($user_cat_profit):($user_cat_expense));
                    // Flagi
                    array_push($ncr, ((($request_data['cid'.$i]==0)&&($request_data['ignore'.$i]==0)))?(true):(false));
                }
                
                // Uzupełnienie formularza
                $form->setData($request_data);
                
                // Filtry
                $formFilters = new TransactionImportFormFilter($tr_count, $ncr);
                $form->setInputFilter($formFilters->getInputFilter());
                
                // Poprawność formularza
                if ($form->isValid()) {
                    
                    // Dodanie poszczególnych transakcji do bazy danych
                    for ($i=0; $i<$tr_count; $i++) {
                        
                        // Spr. czy ignorować wybraną transakcję
                        if ($form->get('ignore'.$i)->getValue()==1) {
                            // Ominięcie wykonania reszty pętli
                            continue;
                        }
                        
                        // Przygotowanie transakcji
                        $transaction = new Transaction();
                        $transaction->tid = 0;
                        $transaction->uid = $uid;
                        $transaction->cid = (int)$form->get('cid'.$i)->getValue();
                        $transaction->t_type = (int)$form->get('t_type'.$i)->getValue();
                        $transaction->t_date = (string)$form->get('t_date'.$i)->getValue();
                        $transaction->t_content = (string)$form->get('t_content'.$i)->getValue();
                        $transaction->t_value = (double)$form->get('t_value'.$i)->getValue();
                        
                        // spr. czy podano nową kategorię
                        $c_name = $form->get('c_name'.$i)->getValue();
                        if (($c_name!=null) && ($ncr[$i]==true)) {
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
                        
                        // Zapis transakcji
                        $this->getTransactionMapper()->saveTransaction($transaction);
                        
                    }
                    
                    // Zwiększenie licznika przeparsowanych transakcji
                    $import->fpos = $import->nfpos;
                    $import->counted += $tr_count;
                    
                    // Spr czy koniec importu
                    if ($import->counted == $import->count) {
                        
                        // Usunięcie informacji o imporcie
                        $this->getImportMapper()->delUserImport($uid);
                        // Usunięcie pliku z wyciągiem
                        if (unlink($upload_config['upload_dir'].$import->fname) == true) {
                            
                            // Ostatnia data z dodanej transakcji
                            if (isset($transaction)) {
                                $t_dt = explode('-', $transaction->t_date);
                            } else { // Nie importowano niczego (ignorowano)
                                $t_dt[0] = date('Y');
                                $t_dt[1] = date('n');
                            }
                            // Przekierowanie do listy transakcji do daty z dodawanej transakcji
                            return $this->redirect()->toRoute('transactions', array(
                                                                                   'month' => $t_dt[1],
                                                                                   'year' => $t_dt[0],
                                                                                   'page' => 1,
                                                                                   ));
                            
                        } else {
                            throw new \Exception("Nie można usunąć pliku z wyciągiem!");
                        }
                        
                    } else { // jeszcze nie koniec importu
                        
                        // Aktualizacja informacji o imporcie
                        $this->getImportMapper()->setUserImport($import);
                        
                        // Przekierowanie do kolejnego zatwierdzania
                        return $this->redirect()->toRoute('import/commit');
                        
                    }
                    
                }
                
            } else { // Brak danych z formularza
                
                // Nazwa pliku
                $FILE_NAME = $upload_config['upload_dir'].$import->fname;
                
                // Rodzaj banku
                switch ($import->bank) {
                    case 'mBank': $bank = new mBank($FILE_NAME, $import->fpos, $upload_config['maxParseLines']); break;
                }
                
                // Spr. banku
                if (!(isset($bank))) {
                    throw new \Exception('Błędna nazwa banku!');
                }
                
                // Przetworzenie
                $tr = $bank->parseData();
                
                // Liczba uzyskanych transakcji
                $tr_count = count($tr);
                
                // Spr. czy wystąpił błąd parsowania
                if (!$bank->isParseError()) {
                    
                    // Uzupełnienie formularza danymi
                    for ($i=0; $i<$tr_count; $i++) {
                        
                        // Typ transakcji (0 - przychód, 1 - wydatek)
                        $form->get('t_type'.$i)->setValue($tr[$i]->t_type);
                        // Lista kategorii
                        $form->get('cid'.$i)->setValueOptions(($tr[$i]->t_type==0)?($user_cat_profit):($user_cat_expense));
                        // Nowa kategoria
                        $form->get('c_name'.$i)->setValue('');
                        // Data
                        $form->get('t_date'.$i)->setValue($tr[$i]->t_date);
                        // Opis
                        $form->get('t_content'.$i)->setValue($tr[$i]->t_content);
                        // Wartość
                        $form->get('t_value'.$i)->setValue($tr[$i]->t_value);
                        
                    }
                    
                    // Nowa pozycja w pliku
                    $import->nfpos = $bank->getPos();
                    
                    // Aktualizacja informacji o imporcie
                    $this->getImportMapper()->setUserImport($import);
                    
                } else { // Błąd parsowania
                    
                    // Ustawienie flagi błędu
                    $ERR = 1;
                    
                    // Usunięcie informacji o imporcie
                    $this->getImportMapper()->delUserImport($uid);
                    // Usunięcie pliku z wyciągiem
                    if (unlink($upload_config['upload_dir'].$import->fname) == false) {
                        
                        throw new \Exception("Nie można usunąć pliku z wyciągiem!");
                        
                    }
                    
                }
                
            }
            
            return array(
                'form' => ($ERR==0)?($form):(null),
                'ERR' => $ERR,
                'TR_COUNT' => $tr_count,
                'TR_COUNTED' => $import->counted,
                'ALL_TR_COUNT' => $import->count,
            );
            
        } else { // brak danych do importu
            
            // Przekierowanie do wyboru banku i pliku
            return $this->redirect()->toRoute('import');
            
        }
        
    }
    
    // Anulowanie importu transakcji
    public function cancelAction()
    {
        // Identyfikator zalogowanego usera
        $uid = $this->getServiceLocator()->get('uid');
        
        // Aktualny import
        $import = $this->getImportMapper()->getUserImport($uid);
        
        // Ustawienia ładowania wyciągów
        $upload_config = $this->getServiceLocator()->get('upload_cfg');
        
        // Czy są dane z importem
        if ($import) {
            
            // Usunięcie informacji o imporcie
            $this->getImportMapper()->delUserImport($uid);
            // Usunięcie pliku z wyciągiem
            if (unlink($upload_config['upload_dir'].$import->fname) == false) {
                
                throw new \Exception("Nie można usunąć pliku z wyciągiem!");
                
            }
            
        }
        
        // Przekierowanie do listy transakcji
        return $this->redirect()->toRoute('transactions', array(
                                                               'month' => date('n'),
                                                               'year' => date('Y'),
                                                               'page' => 1,
                                                               ));
        
    }

}