<?php
/**
 *  Import CSV file controller
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
                            if ($new_import->getCount() == 0) {
                                // Delete CSV file from the server
                                if (unlink($upload_config['upload_dir'].$new_import->getFileName()) == false) {
                                    
                                    throw new \Exception('Can not delete CSV file!');
                                    
                                }
                                // Redirect to the error page
                                return $this->redirect()->toRoute('import/error');
                            }
                            
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
            $user_cat_expense = $this->get('User\CategoryMapper')->getUserCategoriesToSelect($uid, Transaction::EXPENSE);
            // Get categories for the profit
            $user_cat_profit = $this->get('User\CategoryMapper')->getUserCategoriesToSelect($uid, Transaction::PROFIT);
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
                    if ($data['t_type-'.$i] == Transaction::PROFIT || $data['t_type-'.$i] == Transaction::EXPENSE) {
                        
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

                    $fData = $form->getData();
                    
                    // Add transactions to the database
                    for ($i=0; $i<$tr_count; $i++) {
                        
                        // Check if we must ignore this transaction
                        if ($form->get('ignore-'.$i)->getValue()==1) {
                            // Jump to the next transaction
                            continue;
                        }
                        
                        // Check which transaction type we must add
                        if ($fData['t_type-'.$i] == Transaction::PROFIT || $fData['t_type-'.$i] == Transaction::EXPENSE) {
                            
                            // Create transaction object
                            $transaction = new Transaction();
                            $transaction->setAccountId($import->getAccountId());
                            $transaction->setUserId($uid);
                            // Get category id
                            if ($fData['ccid-'.$i] == -1 || $fData['ccid-'.$i] == 0) {
                                $cid = $fData['pcid-'.$i];
                            } else {
                                $cid = $fData['ccid-'.$i];
                            }
                            $transaction->setCategoryId((int)$cid);
                            $transaction->setTransactionType((int)$fData['t_type-'.$i]);
                            $transaction->setDate(new \DateTime($fData['t_date-'.$i]));
                            $transaction->setContent((string)$fData['t_content-'.$i]);
                            $transaction->setValue($fData['t_value-'.$i]);
                            
                            // Add transaction
                            $this->get('Budget\TransactionMapper')->saveTransaction($transaction);
                            
                        } else { // Transfer
                            
                            // Get user transfer category id (hidden category for transfers)
                            $tcid = $this->get('User\CategoryMapper')->getTransferCategoryId($uid);
                            
                            // Outgoing transfer
                            $outTransaction = new Transaction();
                            $outTransaction->setAccountId(($fData['t_type-'.$i]==2)?($import->getAccountId()):($fData['taid-'.$i]));
                            $outTransaction->setTransferAccountId(($fData['t_type-'.$i]==2)?($fData['taid-'.$i]):($import->getAccountId()));
                            $outTransaction->setUserId($uid);
                            $outTransaction->setTransactionType(Transaction::OUTGOING_TRANSFER);
                            $outTransaction->setCategoryId($tcid);
                            $outTransaction->setDate(new \DateTime($fData['t_date-'.$i]));
                            $outTransaction->setContent($fData['t_content-'.$i]);
                            $outTransaction->setValue($fData['t_value-'.$i]);
                            
                            // Incoming transfer
                            $inTransaction = new Transaction();
                            $inTransaction->setAccountId(($fData['t_type-'.$i]==2)?($fData['taid-'.$i]):($import->getAccountId()));
                            $inTransaction->setTransferAccountId(($fData['t_type-'.$i]==2)?($import->getAccountId()):($fData['taid-'.$i]));
                            $inTransaction->setUserId($uid);
                            $inTransaction->setTransactionType(Transaction::INCOMING_TRANSFER);
                            $inTransaction->setCategoryId($tcid);
                            $inTransaction->setDate(new \DateTime($fData['t_date-'.$i]));
                            $inTransaction->setContent($fData['t_content-'.$i]);
                            $inTransaction->setValue($fData['t_value-'.$i]);
                            
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
                                $lastDate = $transaction->getDate();
                            } else {
                                $lastDate = new \DateTime();
                            }
                            // redirect to the transaction list
                            return $this->redirect()->toRoute('transactions', array(
                                                                                    'aid' => $import->getAccountId(),
                                                                                    'month' => $lastDate->format('m'),
                                                                                    'year' => $lastDate->format('Y'),
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
                        $form->get('t_type-'.$i)->setValue($tr[$i]->getTransactionType());
                        // Main category list
                        $form->get('pcid-'.$i)->setValueOptions(($tr[$i]->getTransactionType()==Transaction::PROFIT)?($user_cat_profit):($user_cat_expense));
                        // Bank account list
                        $form->get('taid-'.$i)->setValueOptions($accounts);
                        // Transaction date
                        $form->get('t_date-'.$i)->setValue($tr[$i]->getDate()->format('Y-m-d'));
                        // Description
                        $form->get('t_content-'.$i)->setValue($tr[$i]->getContent());
                        // Value
                        $form->get('t_value-'.$i)->setValue($tr[$i]->getValue());
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

    /**
     * Error action
     */
    public function errorAction()
    {

    }

}
