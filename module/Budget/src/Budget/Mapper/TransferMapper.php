<?php

namespace Budget\Mapper;

use Base\Mapper\BaseMapper;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;

use Budget\Model\Transaction;
use Budget\Model\Transfer;

/**
 * Transfer mapper
 * 
 * @author Mateusz Mirosławski
 *
 */
class TransferMapper extends BaseMapper
{
    /**
     * Get transfer transactions.
     * array('incoming','outgoing')
     * 
     * @param int $trid Transfer identifier
     * @param int $uid User identifier
     * @throws \Exception
     * @return array
     */
    public function getTransfer($trid, $uid)
    {
        $sql = new Sql($this->getDbAdapter());
        $select = $sql->select();
    
        $select->from(array('t' => 'transfers'))
                ->where(array('t.trid' => (int)$trid,
                        't.uid' => (int)$uid));
    
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
    
        if (!$row) {
            throw new \Exception('There is no transfer!');
        }
    
        $transfer = new Transfer($row->current());
    
        $transactionMapper = $this->getServiceLocator()->get('Budget\TransactionMapper');
        
        // Get outgoing transaction
        $out = $transactionMapper->getTransaction($transfer->tid_out, $uid);
        // Get incoming transaction
        $in = $transactionMapper->getTransaction($transfer->tid_in, $uid);
        
        return array(
            'outgoing' => $out,
            'incoming' => $in,
        );
    }
    
    /**
     * Get second transaction of the transfer.
     * array(
     *     'trid' => transfer identifier
     *     'transaction' => Transaction
     * )
     * 
     * @param int $tid Transaction identifier which we have
     * @param int $uid User identifier
     * @param int $t_type Transaction type which we have to receive (2 - outgoing, 3 - incoming)
     * @throws \Exception
     * @return array
     */
    public function getTransaction($tid, $uid, $t_type)
    {
        $sql = new Sql($this->getDbAdapter());
        $select = $sql->select();
        
        $select->from(array('t' => 'transfers'))
                ->where(array('t.uid' => (int)$uid));
        
        if ($t_type==2) { // Get outgoing
            
            $select->where(array('t.tid_in' => (int)$tid));
            
        } else { // Get incoming
            
            $select->where(array('t.tid_out' => (int)$tid));
            
        }
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        
        if (!$row) {
            throw new \Exception('There is no transfer!');
        }
        
        $transfer = new Transfer($row->current());
        
        $transactionMapper = $this->getServiceLocator()->get('Budget\TransactionMapper');
        
        if ($t_type==2) {
        
            // Get outgoing transaction
            $transaction = $transactionMapper->getTransaction($transfer->tid_out, $uid);
        
        } else {
        
            // Get incoming transaction
            $transaction = $transactionMapper->getTransaction($transfer->tid_in, $uid);
        
        }
        
        return array(
            'trid' => $transfer->trid,
            'transaction' => $transaction,
        );
    }
    
    /**
     * Add or edit transfer.
     * 
     * @param Transaction $outgoing Outgoing transaction
     * @param Transaction $incoming Incoming transaction
     */
    public function saveTransfer(Transaction $outgoing, Transaction $incoming)
    {
        // Transaction mapper
        $transactionMapper = $this->getServiceLocator()->get('Budget\TransactionMapper');
        
        // Save transactions
        $outId = $transactionMapper->saveTransaction($outgoing);
        $inId = $transactionMapper->saveTransaction($incoming);
        
        if ($outgoing->tid===null) { // new entry
            $data = array(
                'uid' => (int)$incoming->uid,
                'tid_out' => (int)$outId,
                'tid_in' => (int)$inId,
            );
            
            $sql = new Sql($this->getDbAdapter());
            $insert = $sql->insert();
            $insert->into('transfers');
            $insert->values($data);
        
            $statement = $sql->prepareStatementForSqlObject($insert);
            $statement->execute();
        } 
    }
    
    /**
     * Delete transfer.
     * 
     * @param Transaction $transaction One of the transfer transaction
     */
    public function deleteTransfer(Transaction $transaction)
    {
        $uid = $transaction->uid;
        
        // Get transaction and transfer id-s
        if ($transaction->t_type==2) {
            $outId = $transaction->tid;
            // Get incoming transaction id
            $data = $this->getTransaction($transaction->tid, $uid, 3);
            $inId = $data['transaction']->tid;
        } else {
            $inId = $transaction->tid;
            // Get outgoing transaction id
            $data = $this->getTransaction($transaction->tid, $uid, 2);
            $outId = $data['transaction']->tid;
        }
        
        // Transfer identifier
        $trId = $data['trid'];
        
        // Delete transactions
        $sql = new Sql($this->getDbAdapter());
        
        $delete = $sql->delete();
        $delete->from('transaction');
        $delete->where(
                array(
                    'tid' => array((int)$outId, (int)$inId),
                    'uid' => (int)$uid,
                )
        );
        
        $statement = $sql->prepareStatementForSqlObject($delete);
        $row = $statement->execute()->count();
        
        if ($row!=2) {
            throw new \Exception('Transfer data are damaged!');
        }
        
        // Delete transfer
        $sql = new Sql($this->getDbAdapter());
        
        $delete = $sql->delete();
        $delete->from('transfers');
        $delete->where(array('trid' => (int)$trId,
                'uid' => (int)$uid));
        
        $statement = $sql->prepareStatementForSqlObject($delete);
        $row = $statement->execute()->count();
        
        if ($row!=1) {
            throw new \Exception('Transfer data are damaged!');
        }
    }
}
