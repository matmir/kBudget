<?php

namespace Budget\Controller;

use Base\Controller\BaseController;

use Budget\Model\Banking\Exception\EndBankFile;
use Budget\Model\Banking\Exception\ParseBankFileError;

use Budget\Model\Import;

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
            $upload_config = $this->get('uploadConfig');
            
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
                            $new_import->setUserId($uid);
                            $new_import->setAccountId($form->get('aid')->getValue());
                            $new_import->setFileName($FILE_NAME);
                            $new_import->setBankName($form->get('bank')->getValue());
                            $new_import->setPositionInFile(0);
                            $new_import->setNewPositionInFile(0);
                            $new_import->setCounted(0);
                            
                            // Get bank instance
                            $bank = $this->get('Budget\BankService')->getBankInstance(
                                $new_import->getBankName(),
                                $destination,
                                0,
                                $upload_config['maxParseLines']
                            );
                            
                            // Number of the transactions
                            $new_import->setCount($bank->count());
                            
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
        
        $categoriesValid = true;
        
        // Check if there is import information
        if ($import) {
            
            // Upload configuration
            $upload_config = $this->get('uploadConfig');
            
            // Number of not imported transactions
            $not_imported_count = $import->getCount() - $import->getCounted();
            
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
                
                // Get POST data
                $data = $request->getPost();
                
                // Loading all necessary categories for the import form
                for ($i=0; $i<$tr_count; $i++) {
                    
                    // Bank account list
                    $form->get('taid-'.$i)->setValueOptions($accounts);
                    
                    // Check if the givent transaction is not transfer
                    if ($data['t_type-'.$i] == 0 || $data['t_type-'.$i] == 1) {
                        
                        // Check if we must ignore this transaction
                        if ($data['ignore-'.$i]==1) {
                            // Jump to the next transaction
                            continue;
                        }
                        
                        // Check main category
                        if ($data['pcid-'.$i] == 0 || $data['pcid-'.$i] == -1) {
                            $form->get('pcid-'.$i)->setMessages(
                                array(
                                    'Select category!'
                                )
                            );
            
                            $categoriesValid = false;
                        }
                        
                        // Insert main category
                        $form->get('pcid-'.$i)->setValueOptions(($data['t_type-'.$i]==0)?($user_cat_profit):($user_cat_expense));
                        
                        // Get subcategories
                        $subCategories = $this->get('User\CategoryMapper')->getUserCategoriesToSelect($uid, $data['t_type-'.$i], $data['pcid-'.$i]);
                        
                        // Insert subcategories
                        $form->get('ccid-'.$i)->setValueOptions($subCategories);
                    } else { // Check transfer data
                        // Check transfer account id
                        if ($data['taid-'.$i] == $import->getAccountId()) {
                            $form->get('taid-'.$i)->setMessages(
                                array(
                                    'Bank account must be different than bank account into which importing CSV file!'
                                )
                            );
            
                            $categoriesValid = false;
                        }
                    }
                }
                
                // Insert POST data into the form
                $form->setData($data);
                
                // Filters
                $formFilters = new TransactionImportFormFilter($tr_count);
                $form->setInputFilter($formFilters->getInputFilter());
                
                // Check the form
                if ($form->isValid() && $categoriesValid) {
                    
                    // Add transactions to the database
                    for ($i=0; $i<$tr_count; $i++) {
                        
                        // Check if we must ignore this transaction
                        if ($form->get('ignore-'.$i)->getValue()==1) {
                            // Jump to the next transaction
                            continue;
                        }
                        
                        // Check which transaction type we must add
                        if ($data['t_type-'.$i] == 0 || $data['t_type-'.$i] == 1) {
                            
                            // Create transaction object
                            $transaction = new Transaction();
                            $transaction->aid = $import->getAccountId();
                            $transaction->uid = $uid;
                            // Get category id
                            if ($form->get('ccid-'.$i)->getValue() == -1 || $form->get('ccid-'.$i)->getValue() == 0) {
                                $cid = $form->get('pcid-'.$i)->getValue();
                            } else {
                                $cid = $form->get('ccid-'.$i)->getValue();
                            }
                            $transaction->cid = (int)$cid;
                            $transaction->t_type = (int)$form->get('t_type-'.$i)->getValue();
                            $transaction->t_date = (string)$form->get('t_date-'.$i)->getValue();
                            $transaction->t_content = (string)$form->get('t_content-'.$i)->getValue();
                            $transaction->t_value = (double)$form->get('t_value-'.$i)->getValue();
                            
                            // Add transaction
                            $this->get('Budget\TransactionMapper')->saveTransaction($transaction);
                            
                        } else { // Transfer
                            
                            // Get user transfer category id (hidden category for transfers)
                            $tcid = $this->get('User\CategoryMapper')->getTransferCategoryId($uid);
                            
                            // Outgoing transfer
                            $outTransaction = new Transaction();
                            $outTransaction->aid = ($data['t_type-'.$i]==2)?($import->getAccountId()):($data['taid-'.$i]);
                            $outTransaction->taid = ($data['t_type-'.$i]==2)?($data['taid-'.$i]):($import->getAccountId());
                            $outTransaction->uid = $uid;
                            $outTransaction->t_type = 2;
                            $outTransaction->cid = $tcid;
                            $outTransaction->t_date = $data['t_date-'.$i];
                            $outTransaction->t_content = $data['t_content-'.$i];
                            $outTransaction->t_value = $data['t_value-'.$i];
                            
                            // Incoming transfer
                            $inTransaction = new Transaction();
                            $inTransaction->aid = ($data['t_type-'.$i]==2)?($data['taid-'.$i]):($import->getAccountId());
                            $inTransaction->taid = ($data['t_type-'.$i]==2)?($import->getAccountId()):($data['taid-'.$i]);
                            $inTransaction->uid = $uid;
                            $inTransaction->t_type = 3;
                            $inTransaction->cid = $tcid;
                            $inTransaction->t_date = $data['t_date-'.$i];
                            $inTransaction->t_content = $data['t_content-'.$i];
                            $inTransaction->t_value = $data['t_value-'.$i];
                            
                            // Save transfer
                            $this->get('Budget\TransferMapper')->saveTransfer($outTransaction, $inTransaction);
                            
                        }
                        
                    }
                    
                    // Increment parsing line count
                    $import->setPositionInFile($import->getNewPositionInFile());
                    $import->setCounted($import->getCounted() + $tr_count);
                    
                    // Check if is end importing
                    if ($import->getCounted() == $import->getCount()) {
                        
                        // Delete information about import
                        $this->get('Budget\ImportMapper')->delUserImport($uid);
                        // Delete CSV file
                        if (unlink($upload_config['upload_dir'].$import->getFileName()) == true) {
                            
                            // Get last transaction date
                            if (isset($transaction)) {
                                $t_dt = explode('-', $transaction->t_date);
                            } else {
                                $t_dt[0] = date('Y');
                                $t_dt[1] = date('n');
                            }
                            // redirect to the transaction list
                            return $this->redirect()->toRoute('transactions', array(
                                                                                    'aid' => $import->getAccountId(),
                                                                                    'month' => $t_dt[1],
                                                                                    'year' => $t_dt[0],
                                                                                    'page' => 1,
                                                                                   ));
                            
                        } else {
                            throw new \Exception('Can not delete CSV file!');
                        }
                        
                    } else { // is not end importing
                        
                        // Update import information
                        $this->get('Budget\ImportMapper')->setUserImport($import);
                        
                        // Redirect to the committing
                        return $this->redirect()->toRoute('import/commit');
                        
                    }
                    
                } else { // Form is not valid!
                    foreach ($form->getMessages() as $key => $value) {
                        $form->get($key)->setAttribute('style', 'background: red;');
                        $form->get($key)->setAttribute('title', current($value));
                    }
                }
                
            } else { // There is no POST data
                
                // CSV file name
                $FILE_NAME = $upload_config['upload_dir'].$import->getFileName();
                
                // Get bank instance
                $bank = $this->get('Budget\BankService')->getBankInstance(
                    $import->getBankName(),
                    $FILE_NAME,
                    $import->getPositionInFile(),
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
                    $import->setNewPositionInFile($bank->getPos());
                    
                    // Update import information
                    $this->get('Budget\ImportMapper')->setUserImport($import);
                    
                } catch (ParseBankFileError $e) {
                    
                    // Set error flag
                    $ERR = 1;
                    
                    // Delete information about import
                    $this->get('Budget\ImportMapper')->delUserImport($uid);
                    // Delete CSV file from the server
                    if (unlink($upload_config['upload_dir'].$import->getFileName()) == false) {
                    
                        throw new \Exception('Can not delete CSV file!');
                    
                    }
                }
                
            }
            
            return array(
                'form' => ($ERR==0)?($form):(null),
                'ERR' => $ERR,
                'TR_COUNT' => $tr_count,
                'TR_COUNTED' => $import->getCounted(),
                'ALL_TR_COUNT' => $import->getCount(),
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
        $upload_config = $this->get('uploadConfig');
        
        // Check if there are informations about import
        if ($import) {
            
            // Delete import information
            $this->get('Budget\ImportMapper')->delUserImport($uid);
            // Delete CSV file from the server
            if (unlink($upload_config['upload_dir'].$import->getFileName()) == false) {
                
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
