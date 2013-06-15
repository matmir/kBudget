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
                ->join(array('c' => \User\Mapper\CategoryMapper::TABLE),'t.cid = c.cid')
                ->where(array(
                        't.uid' => (int)$uid,
                        't.aid' => (int)$aid,
                ))
                ->order(array(
                              't.t_date DESC',
                              't.tid DESC',
                              ));
           
        if ($dt_param['type'] == 'month') {
            
            if (!isset($dt_param['dt_month'])) {
                throw new \Exception('No parameter with the month!');
            }
            
            $select->where(array(
                              't.t_date LIKE ?' => (string)$dt_param['dt_month'].'-%',
                              ));
            
        } elseif ($dt_param['type'] == 'between') {
            
            if (!(isset($dt_param['dt_up'])&&isset($dt_param['dt_down']))) {
                throw new \Exception('No parameter with the range!');
            }
            
            $select->where(array(
                              't.t_date >= ?' => $dt_param['dt_down'],
                              't.t_date <= ?' => $dt_param['dt_up'],
                              ));
            
        }
        // If date type is different than above it choose all range
        
        // Transaction type
        if (!in_array(-1, $t_type)) {
            
            if (!(in_array(0, $t_type) || in_array(1, $t_type) || in_array(2, $t_type) || in_array(3, $t_type))) {
                throw new \Exception('Wrong transaction type parameter!');
            }
            
            $select->where(array('t.t_type' => $t_type));
            
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
        
        $select->from(array('t' => self::TABLE),'MIN(t_date)')
                ->where(array('t.uid' => (int)$uid));
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        
        $dane = $row->current();
        $dt = explode('-',$dane['t_date']);
        
        return (int)$dt[0];
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
        $data = array(
            'uid' => $transaction->uid,
            'aid' => $transaction->aid,
            'taid' => $transaction->taid,
            'cid'  => $transaction->cid,
            't_type'  => $transaction->t_type,
            't_date'  => $transaction->t_date,
            't_content'  => $transaction->t_content,
            't_value'  => $transaction->t_value,
        );
        
        $sql = new Sql($this->getDbAdapter());

        $tid = (int)$transaction->tid;
        if ($tid == 0) { // dodanie nowego wpisu
            $insert = $sql->insert();
            $insert->into(self::TABLE);
            $insert->values($data);
            
            $statement = $sql->prepareStatementForSqlObject($insert);
            $val = $statement->execute()->getGeneratedValue();
            
            return $val;
        } else { // edycja
            // Spr. czy istnieje
            if ($this->getTransaction($tid, $data['uid'])) {
                
                $update = $sql->update();
                
                $update->table(self::TABLE);
                $update->set($data);
                $update->where(array('tid' => $tid));
                
                $statement = $sql->prepareStatementForSqlObject($update);
                $statement->execute();
                
                return 0;
            } else {
                throw new \Exception('Wybrana transakcja nie istnieje!');
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
                ->join(array('c' => \User\Mapper\CategoryMapper::TABLE),'t.cid = c.cid')
                ->where(array('t.tid' => (int)$tid,
                              't.uid' => (int)$uid));
        
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
        $delete->where(array('tid' => (int)$tid,
                             'uid' => (int)$uid));
        
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
            
            if (!(in_array(0, $t_type) || in_array(1, $t_type) || in_array(2, $t_type) || in_array(3, $t_type))) {
                throw new \Exception('Wrong transaction type parameter!');
            }
            
            $select->where(array('t.t_type' => $t_type));
            
        }
        
        $select->columns(array('sm' => new Expression('SUM(t.t_value)')));
        $select->from(array('t' => self::TABLE))
                ->where(array('t.uid' => (int)$uid,
                              't.aid' => (int)$aid,
                              ));
                
        if ($dt['type'] == 'month') {
            
            if (!isset($dt['dt_month'])) {
                throw new \Exception('No parameter with the month!');
            }
            
            $select->where(array(
                              't.t_date LIKE ?' => (string)$dt['dt_month'].'-%',
                              ));
            
        } elseif ($dt['type'] == 'between') {
            
            if (!(isset($dt['dt_up'])&&isset($dt['dt_down']))) {
                throw new \Exception("Brak parametru z zakresem dat!");
            }
            
            $select->where(array(
                              't.t_date >= ?' => $dt['dt_down'],
                              't.t_date <= ?' => $dt['dt_up'],
                              ));
            
        }
        // If date type is different than above it choose all range
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        
        $suma = $row->current();
        
        return ($suma['sm']==null)?(0):($suma['sm']);
    }

}
