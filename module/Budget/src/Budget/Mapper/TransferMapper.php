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
     * MySQL import table name
     *
     * @var string
     */
    const TABLE = 'transfers';
    
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
    
        $select->from(array('t' => self::TABLE))
                ->where(array('t.transferId' => (int)$trid,
                        't.userId' => (int)$uid));
    
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
    
        if (!$row) {
            throw new \Exception('There is no transfer!');
        }
    
        $transfer = new Transfer($row->current());
    
        $transactionMapper = $this->getServiceLocator()->get('Budget\TransactionMapper');
        
        // Get outgoing transaction
        $out = $transactionMapper->getTransaction($transfer->getOutTransactionId(), $uid);
        // Get incoming transaction
        $in = $transactionMapper->getTransaction($transfer->getInTransactionId(), $uid);
        
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
        
        $select->from(array('t' => self::TABLE))
                ->where(array('t.userId' => (int)$uid));
        
        if ($t_type==Transaction::OUTGOING_TRANSFER) { // Get outgoing
            
            $select->where(array('t.inTransactionId' => (int)$tid));
            
        } else { // Get incoming
            
            $select->where(array('t.outTransactionId' => (int)$tid));
            
        }
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        
        if (!$row) {
            throw new \Exception('There is no transfer!');
        }
        
        $transfer = new Transfer($row->current());
        
        $transactionMapper = $this->getServiceLocator()->get('Budget\TransactionMapper');
        
        if ($t_type==Transaction::OUTGOING_TRANSFER) {
        
            // Get outgoing transaction
            $transaction = $transactionMapper->getTransaction($transfer->getOutTransactionId(), $uid);
        
        } else {
        
            // Get incoming transaction
            $transaction = $transactionMapper->getTransaction($transfer->getInTransactionId(), $uid);
        
        }
        
        return array(
            'trid' => $transfer->getTransferId(),
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
        
        if ($outgoing->getTransactionId()===null) { // new entry
            $data = array(
                'userId' => (int)$incoming->getUserId(),
                'outTransactionId' => (int)$outId,
                'inTransactionId' => (int)$inId,
            );
            
            $sql = new Sql($this->getDbAdapter());
            $insert = $sql->insert();
            $insert->into(self::TABLE);
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
        $uid = $transaction->getUserId();
        
        // Get transaction and transfer id-s
        if ($transaction->getTransactionType()==Transaction::OUTGOING_TRANSFER) {
            $outId = $transaction->getTransactionId();
            // Get incoming transaction id
            $data = $this->getTransaction($transaction->getTransactionId(), $uid, Transaction::INCOMING_TRANSFER);
            $inId = $data['transaction']->getTransactionId();
        } else {
            $inId = $transaction->getTransactionId();
            // Get outgoing transaction id
            $data = $this->getTransaction($transaction->getTransactionId(), $uid, Transfer::OUTGOING_TRANSFER);
            $outId = $data['transaction']->getTransactionId();
        }
        
        // Transfer identifier
        $trId = $data['transferId'];
        
        // Delete transactions
        $sql = new Sql($this->getDbAdapter());
        
        $delete = $sql->delete();
        $delete->from(\Budget\Mapper\TransactionMapper::TABLE);
        $delete->where(
                array(
                    'transactionId' => array((int)$outId, (int)$inId),
                    'userId' => (int)$uid,
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
        $delete->from(self::TABLE);
        $delete->where(array('transferId' => (int)$trId,
                'userId' => (int)$uid));
        
        $statement = $sql->prepareStatementForSqlObject($delete);
        $row = $statement->execute()->count();
        
        if ($row!=1) {
            throw new \Exception('Transfer data are damaged!');
        }
    }
}
