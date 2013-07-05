<?php

namespace Budget\Mapper;

use Base\Mapper\BaseMapper;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;

use Budget\Model\Transaction;

/**
 * Transaction mapper
 * 
 * @author Mateusz Mirosławski
 *
 */
class TransactionMapper extends BaseMapper
{
    /**
     * MySQL transaction table name
     * 
     * @var string
     */
    const TABLE = 'transactions';
    
    /**
     * Get user transactions from specified account
     * 
     * @param int $uid User id
     * @param int $aid Bank account id
     * @param array $dt_param {
     *                          'type' => 'month/between/all',
     *                          'dt_month' => 'yyyy-mm' form type 'month' or 'dt_up' and 'dt_down' for 'between'
     *                        }
     * @param array $t_type Array with transaction types
     *                  ('-1' all transactions, '0' profits, '1' expense, '2' outgoing transfers, '3' incoming transfers)
     * @param int $pg Actual page number
     * @param bool $pagged Return paginator
     * @throws \Exception
     * @return \Zend\Paginator\Paginator|multitype:
     */
    public function getTransactions($uid, $aid, $dt_param, array $t_type=array(-1), $pg=1, $pagged=false)
    {
        // Check if the date param is correct
        if (!is_array($dt_param)) {
            throw new \Exception('The date param must be an array!');
        }
        // Check date type
        if (!isset($dt_param['type'])) {
            throw new \Exception('Missing type parameter in dt_param array');
        }
        // Check page number
        if ($pg <= 0) {
            throw new \Exception('The page number must be an positive');
        }
        
        $sql = new Sql($this->getDbAdapter());
        $select = $sql->select();
        
        $select->from(array('t' => self::TABLE))
                ->join(array('c' => \User\Mapper\CategoryMapper::TABLE),'t.categoryId = c.categoryId')
                ->where(array(
                        't.userId' => (int)$uid,
                        't.accountId' => (int)$aid,
                ))
                ->order(array(
                              't.date DESC',
                              't.transactionId DESC',
                              ));
           
        if ($dt_param['type'] == 'month') {
            
            if (!isset($dt_param['dt_month'])) {
                throw new \Exception('No parameter with the month!');
            }
            
            $select->where(array(
                              't.date LIKE ?' => (string)$dt_param['dt_month'].'-%',
                              ));
            
        } elseif ($dt_param['type'] == 'between') {
            
            if (!(isset($dt_param['dt_up'])&&isset($dt_param['dt_down']))) {
                throw new \Exception('No parameter with the range!');
            }
            
            $select->where(array(
                              't.date >= ?' => $dt_param['dt_down'],
                              't.date <= ?' => $dt_param['dt_up'],
                              ));
            
        }
        // If date type is different than above it choose all range
        
        // Transaction type
        if (!in_array(-1, $t_type)) {
            
            if (!(in_array(Transaction::PROFIT, $t_type) || in_array(Transaction::EXPENSE, $t_type) || 
                    in_array(Transaction::OUTGOING_TRANSFER, $t_type) || in_array(Transaction::INCOMING_TRANSFER, $t_type))) {
                throw new \Exception('Wrong transaction type parameter!');
            }
            
            $select->where(array('t.transactionType' => $t_type));
            
        }
        
        // Return Paginator?
        if ($pagged) {
            
            $paginator = new Paginator(new DbSelect($select, $sql));
            $paginator->setItemCountPerPage(15);
            $paginator->setCurrentPageNumber((int)$pg);
            
            return $paginator;
            
        } else { // Return array of the Transaction objects
            
            $statement = $sql->prepareStatementForSqlObject($select);
            $results = $statement->execute();
            
            $retObj = array();
            
            while (($tbl=$results->current())!=null) {
                array_push($retObj, new Transaction($tbl));
            }
            
            return $retObj;
        
        }
    }
    
    /**
     * Get min year from user transactions.
     * 
     * @param int $uid User identifier
     * @return int
     */
    public function getMinYearOfTransaction($uid)
    {
        $sql = new Sql($this->getDbAdapter());
        $select = $sql->select();
        
        $select->from(array('t' => self::TABLE),'MIN(date)')
                ->where(array('t.userId' => (int)$uid));
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        
        $dane = $row->current();
        $dt = new \DateTime($dane['date']);
        
        return (int)$dt->format('Y');
    }
    
    /**
     * Save transaction (edit or add new).
     * Return tid of new transaction. If edited then return 0.
     * 
     * @param Transaction $transaction Transaction object
     * @throws \Exception
     * @return int
     */
    public function saveTransaction(Transaction $transaction)
    {
        $data = $transaction->getArrayCopy();
        $data['date'] = $transaction->getDate()->format('Y-m-d');
        unset($data['transactionId']);
        
        $sql = new Sql($this->getDbAdapter());

        $tid = (int)$transaction->getTransactionId();
        if ($tid == 0) { // Add new entry
            $insert = $sql->insert();
            $insert->into(self::TABLE);
            $insert->values($data);
            
            $statement = $sql->prepareStatementForSqlObject($insert);
            $val = $statement->execute()->getGeneratedValue();
            
            return $val;
        } else { // edit
            // check if given transaction exists in database
            if ($this->getTransaction($tid, $data['userId'])) {
                
                $update = $sql->update();
                
                $update->table(self::TABLE);
                $update->set($data);
                $update->where(array('transactionId' => $tid));
                
                $statement = $sql->prepareStatementForSqlObject($update);
                $statement->execute();
                
                return 0;
            } else {
                throw new \Exception('Transaction not exist!');
            }
        }
    }
    
    /**
     * Get transaction
     * 
     * @param int $tid Transaction identifier
     * @param int $uid User identifier
     * @throws \Exception
     * @return \Budget\Model\Transaction
     */
    public function getTransaction($tid, $uid)
    {
        $sql = new Sql($this->getDbAdapter());
        $select = $sql->select();
        
        $select->from(array('t' => self::TABLE))
                ->join(array('c' => \User\Mapper\CategoryMapper::TABLE),'t.categoryId = c.categoryId')
                ->where(array('t.transactionId' => (int)$tid,
                              't.userId' => (int)$uid));
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        
        if (!$row->count()) {
            throw new \Exception('There is no transaction!');
        }
        
        return new Transaction($row->current());
    }
    
    /**
     * Delete transaction
     * 
     * @param int $tid Transaction identifier
     * @param int $uid User identifier
     */
    public function deleteTransaction($tid, $uid)
    {
        $sql = new Sql($this->getDbAdapter());
        
        $delete = $sql->delete();
        $delete->from(self::TABLE);
        $delete->where(array('transactionId' => (int)$tid,
                             'userId' => (int)$uid));
        
        $statement = $sql->prepareStatementForSqlObject($delete);
        $statement->execute();
    }
    
    /**
     * Get sum of transaction values from specified type
     * 
     * @param int $uid User identifier
     * @param int $aid Bank account identifier
     * @param array $dt {
     *                     'type' => 'month/between/all',
     *                     'dt_month' => 'yyyy-mm' form type 'month' or 'dt_up' and 'dt_down' for 'between'
     *                   }
     * @param array $t_type Array with transaction types
     *                      ('-1' all transactions, '0' profits, '1' expense, '2' outgoing transfers, '3' incoming transfers)
     * @throws \Exception
     * @return float
     */
    public function getSumOfTransactions($uid, $aid, $dt, array $t_type=array(-1))
    {
        // Check if the date param is correct
        if (!is_array($dt)) {
            throw new \Exception('The date param must be an array!');
        }
        // Check date type
        if (!isset($dt['type'])) {
            throw new \Exception('Missing type parameter in dt_param array');
        }
        
        $sql = new Sql($this->getDbAdapter());
        $select = $sql->select();
        
        // Transaction type
        if (!in_array(-1, $t_type)) {
            
            if (!(in_array(Transaction::PROFIT, $t_type) || in_array(Transaction::EXPENSE, $t_type) || 
                    in_array(Transaction::OUTGOING_TRANSFER, $t_type) || in_array(Transaction::INCOMING_TRANSFER, $t_type))) {
                throw new \Exception('Wrong transaction type parameter!');
            }
            
            $select->where(array('t.transactionType' => $t_type));
            
        }
        
        $select->columns(array('sm' => new Expression('SUM(t.value)')));
        $select->from(array('t' => self::TABLE))
                ->where(array('t.userId' => (int)$uid,
                              't.accountId' => (int)$aid,
                              ));
                
        if ($dt['type'] == 'month') {
            
            if (!isset($dt['dt_month'])) {
                throw new \Exception('No parameter with the month!');
            }
            
            $select->where(array(
                              't.date LIKE ?' => (string)$dt['dt_month'].'-%',
                              ));
            
        } elseif ($dt['type'] == 'between') {
            
            if (!(isset($dt['dt_up'])&&isset($dt['dt_down']))) {
                throw new \Exception('No parameter with the range!');
            }
            
            $select->where(array(
                              't.date >= ?' => $dt['dt_down'],
                              't.date <= ?' => $dt['dt_up'],
                              ));
            
        }
        // If date type is different than above it choose all range
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        
        $suma = $row->current();
        
        return ($suma['sm']==null)?(0):($suma['sm']);
    }

}
