<?php

namespace Budget\Controller;

use Base\Controller\BaseController;

use Budget\Model\Banking\Exception\EndBankFile;
use Budget\Model\Banking\Exception\ParseBankFileError;

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

/**
 * Import CSV file controller
 * 
 * @author Mateusz Mirosławski
 *
 */
class ImportController extends BaseController
{
    /**
     * Index action
     */
    public function indexAction()
    {
        // Get the logged in user identifier
        $uid = $this->get('userId');
        
        // Get actual information about importing
        $import = $this->get('Budget\ImportMapper')->getUserImport($uid);
        
        // Check if there is import information
        if ($import == null) {
            
            // Upload configuration
            $upload_config = $this->get('upload_cfg');
            
            // Get user bank accounts
            $accounts = $this->get('User\AccountMapper')->getUserAccountsToSelect($uid);
            
            // List of supported banks CSV files
            $bankList = $this->get('Budget\BankService')->getBankList();
            
            $form = new LoadBankFileForm();
            // Insert bank accounts
            $form->get('aid')->setValueOptions($accounts);
            // Insert supported bank list
            $form->get('bank')->setValueOptions($bankList);
            // Filters
            $formFilters = new LoadBankFileFormFilter($upload_config);
    
            $request = $this->getRequest();
            if ($request->isPost()) {
                
                $form->setInputFilter($formFilters->getInputFilter());
                
                // Get data from the form
                $nonFile = $request->getPost()->toArray();
                // Get uploaded file information
                $File = $this->params()->fromFiles('upload_file');
                // Insert all data into the one array
                $request_data = array_merge($nonFile, array('upload_file' => $File));
                
                $form->setData($request_data);
                
                if ($form->isValid()) {
                    
                    // Adapter for the loading files
                    $upload = new Http();
                    
                    // Get information about loading file
                    $file_info = $upload->getFileInfo();
                    $file_type = $file_info['upload_file']['type'];
                    
                    // Check file type
                    if ($file_type == $upload_config['fileType']) {
                        
                        // Generate new file name
                        $FILE_NAME = md5($uid.date('Y-m-d H:i:s')).'.'.$upload_config['fileExtension'];
                        
                        // Set the directory to upload files
                        $upload->setDestination($upload_config['upload_dir']);
                        
                        // Rename file filter
                        $destination = $upload_config['upload_dir'].$FILE_NAME;
                        $upload->setFilters(array('Rename' => array('target' => $destination, 'overwrite' => true)));
                        
                        // Uploading file
                        if ($upload->receive($File['name'])) {
                            
                            // Information about upload
                            $new_import = new Import();
                            $new_import->uid = $uid;
                            $new_import->aid = $form->get('aid')->getValue();
                            $new_import->fname = $FILE_NAME;
                            $new_import->bank = $form->get('bank')->getValue();
                            $new_import->fpos = 0;
                            $new_import->nfpos = 0;
                            $new_import->counted = 0;
                            
                            // Get bank instance
                            $bank = $this->get('Budget\BankService')->getBankInstance(
                                $new_import->bank,
                                $destination,
                                0,
                                $upload_config['maxParseLines']
                            );
                            
                            // Number of the transactions
                            $new_import->count = $bank->count();
                            
                            // Save import information
                            $this->get('Budget\ImportMapper')->setUserImport($new_import);
                            
                            // Redirect to the commiting of the transactions
                            return $this->redirect()->toRoute('import/commit');
                            
                        }
                        
                    } else { // Bad type of loaded file
                        
                        $form->get('upload_file')->setMessages(
                            array(
                                'Bad file type!'
                            )
                        );
                    }
                }
                
            }
            
            return array(
                'form' => $form,
            );
            
        } else { // there are some informations about import
            
            // Redirect to the commiting of the transactions
            return $this->redirect()->toRoute('import/commit');
            
        }
    }
    
    /**
     * Commit importing the transactions
     * 
     * @throws \Exception
     */
    public function commitAction()
    {
        // Logged in user identifier
        $uid = $this->get('userId');
        
        // Actual information about importing
        $import = $this->get('Budget\ImportMapper')->getUserImport($uid);
        
        // Error flag (0 - ok, 1 - parsing error)
        $ERR = 0;
        
        // Check if there is import information
        if ($import) {
            
            // Upload configuration
            $upload_config = $this->get('upload_cfg');
            
            // Number of not imported transactions
            $not_imported_count = $import->count - $import->counted;
            
            // Get number of importing transaction (for this cycle)
            $tr_count = ($not_imported_count>$upload_config['maxParseLines'])?($upload_config['maxParseLines']):($not_imported_count);
            
            // Get categories for the expense
            $user_cat_expense = $this->get('User\CategoryMapper')->getUserCategoriesToSelect($uid, 1);
            // Get categories for the profit
            $user_cat_profit = $this->get('User\CategoryMapper')->getUserCategoriesToSelect($uid, 0);
            // Get user bank accounts
            $accounts = $this->get('User\AccountMapper')->getUserAccountsToSelect($uid);
            
            $form = new TransactionImportForm($tr_count);
            
            $request = $this->getRequest();
            if ($request->isPost()) {
                
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
                            $n_cid = $this->get('User\CategoryMapper')->isCategoryNameExists($c_name, $transaction->t_type, $uid);
                            if ($n_cid == 0) { // Nie istnieje - dodać nową
                                $new_category = new Category();
                                $new_category->uid = $uid;
                                $new_category->c_type = $transaction->t_type;
                                $new_category->c_name = $c_name;
                                // Dodanie
                                $this->get('User\CategoryMapper')->saveCategory($new_category);
                                // Pobranie nowego id-a kategorii
                                $n_cid = $this->get('User\CategoryMapper')->isCategoryNameExists($c_name, $transaction->t_type, $uid);
                            }
                            
                            // Nadpisać pole transakcji nowym identyfikatorem kategorii
                            $transaction->cid = (int)$n_cid;
                        }
                        
                        // Zapis transakcji
                        $this->get('Budget\TransactionMapper')->saveTransaction($transaction);
                        
                    }
                    
                    // Zwiększenie licznika przeparsowanych transakcji
                    $import->fpos = $import->nfpos;
                    $import->counted += $tr_count;
                    
                    // Spr czy koniec importu
                    if ($import->counted == $import->count) {
                        
                        // Usunięcie informacji o imporcie
                        $this->get('Budget\ImportMapper')->delUserImport($uid);
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
                        $this->get('Budget\ImportMapper')->setUserImport($import);
                        
                        // Przekierowanie do kolejnego zatwierdzania
                        return $this->redirect()->toRoute('import/commit');
                        
                    }
                    
                }
                
            } else { // There is no POST data
                
                // CSV file name
                $FILE_NAME = $upload_config['upload_dir'].$import->fname;
                
                // Get bank instance
                $bank = $this->get('Budget\BankService')->getBankInstance(
                    $import->bank,
                    $FILE_NAME,
                    $import->fpos,
                    $upload_config['maxParseLines']
                );
                
                try {
                    
                    // Parsing data
                    $tr = $bank->parseData();
                    
                    // Number of returned transactions
                    $tr_count = count($tr);
                    
                    // Insert data into the form
                    for ($i=0; $i<$tr_count; $i++) {
                    
                        // Transaction type
                        $form->get('t_type-'.$i)->setValue($tr[$i]->t_type);
                        // Main category list
                        $form->get('pcid-'.$i)->setValueOptions(($tr[$i]->t_type==0)?($user_cat_profit):($user_cat_expense));
                        // Bank account list
                        $form->get('taid-'.$i)->setValueOptions($accounts);
                        // Transaction date
                        $form->get('t_date-'.$i)->setValue($tr[$i]->t_date);
                        // Description
                        $form->get('t_content-'.$i)->setValue($tr[$i]->t_content);
                        // Value
                        $form->get('t_value-'.$i)->setValue($tr[$i]->t_value);
                    }
                    
                    // New position in CSV file
                    $import->nfpos = $bank->getPos();
                    
                    // Update import information
                    $this->get('Budget\ImportMapper')->setUserImport($import);
                    
                } catch (ParseBankFileError $e) {
                    
                    // Set error flag
                    $ERR = 1;
                    
                    // Delete information about import
                    $this->get('Budget\ImportMapper')->delUserImport($uid);
                    // Delete CSV file from the server
                    if (unlink($upload_config['upload_dir'].$import->fname) == false) {
                    
                        throw new \Exception('Can not delete CSV file!');
                    
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
            
        } else { // There is no data to import
            
            // Redirect to the file form
            return $this->redirect()->toRoute('import');
            
        }
        
    }
    
    /**
     * Cancel importing transactions
     */
    public function cancelAction()
    {
        // User identifier
        $uid = $this->get('userId');
        
        // Get import information
        $import = $this->get('Budget\ImportMapper')->getUserImport($uid);
        
        // Get upload configuration
        $upload_config = $this->get('upload_cfg');
        
        // Check if there are informations about import
        if ($import) {
            
            // Delete import information
            $this->get('Budget\ImportMapper')->delUserImport($uid);
            // Delete CSV file from the server
            if (unlink($upload_config['upload_dir'].$import->fname) == false) {
                
                throw new \Exception('Can not delete CSV file!');
                
            }
            
        }
        
        // Redirect to the transaction list
        return $this->redirect()->toRoute('transactions', array(
                                                               'month' => date('n'),
                                                               'year' => date('Y'),
                                                               'page' => 1,
                                                               ));
        
    }

}