<?php
/**
    @author Mateusz Mirosławski
    
    Klasa zajmująa się wyciągniem transakcji z bazy danych
*/

namespace Budget\Model;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;
use Zend\Db\Adapter\Adapter;

use Budget\Model\Transaction;

class TransactionMapper
{
    protected $adapter;

    /**
        Konstruktor.
        @param Adapter $adp Obiekt reprezentujący adapter do bazy danych
    */
    public function __construct(Adapter $adp)
    {
        $this->adapter = $adp;
    }
    
    /**
        Pobiera wszystkie elementy transakcji
        @param int $uid Identyfikator usera
        @param array $dt_param tablica z parametrami daty $dt {
                                                        'type' => 'month/between/all',
                                                        'dt_month' => 'yyyy-mm' dla mieniąca lub 'dt_up' i 'dt_down' dla zakresu
                                                    }
        @param int $t_type Typ transakcji (-1 - wszystkie, 0 - przychód, 1 - wydatek)
        @return array() Tablica zawierająca transakcje (Transaction)
    */
    public function getTransactions($uid, $dt_param, $t_type=-1)
    {
        // Spr czy parametr z datą jest tablicą
        if (!is_array($dt_param)) {
            throw new \Exception("Parametr z datą musi być tablicą!");
        }
        // Spr. pola z typem
        if (!isset($dt_param['type'])) {
            throw new \Exception("Brak typu daty w parametrach z tablicą!");
        }
        
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        
        $select->from(array('t' => 'transaction'))
                ->join(array('c' => 'category'),'t.cid = c.cid')
                ->where(array('t.uid' => (int)$uid))
                ->order(array(
                              't.t_date DESC',
                              't.tid DESC',
                              ));
        // Wybrany miesiąc    
        if ($dt_param['type'] == 'month') {
            
            // Spr. parametru
            if (!isset($dt_param['dt_month'])) {
                throw new \Exception("Brak parametru z miesiącem!");
            }
            //echo $dt['dt_month'];
            $select->where(array(
                              't.t_date LIKE ?' => (string)$dt_param['dt_month'].'-%',
                              ));
            
        } elseif ($dt_param['type'] == 'between') { // Wybrany zakres
            
            // Spr. parametru
            if (!(isset($dt_param['dt_up'])&&isset($dt_param['dt_down']))) {
                throw new \Exception("Brak parametru z zakresem dat!");
            }
            
            $select->where(array(
                              't.t_date >= ?' => $dt_param['dt_down'],
                              't.t_date <= ?' => $dt_param['dt_up'],
                              ));
            
        }
        // Jeśli typ daty inny niż 2 powyższe to wybiera cały zakres
        
        // Typ transakcji do pobrania
        if ($t_type != -1) {
            
            // Spr. parametru
            if (!($t_type==0 || $t_type==1)) {
                throw new \Exception("Niepoprawny parametr z typem transakcji!!");
            }
            
            $select->where(array('t.t_type' => (int)$t_type));
            
        }
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();
        
        $retObj = array();
        
        // Przelatuję po wynikach
        while (($tbl=$results->current())!=null)
        {
            $ob = new Transaction();
            $ob->exchangeArray($tbl);
            array_push($retObj, $ob);
        }
        
        return $retObj;
    }
    
    /**
        Pobiera najmniejszą wartość roku z transakcji usera
        @param int $uid Identyfikator usera
        @return int Najmniejszy rok dostępny w transakcjach usera
    */
    public function getMinYearOfTransaction($uid)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        
        $select->from(array('t' => 'transaction'),'MIN(t_date)')
                ->where(array('t.uid' => (int)$uid));
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        
        $dane = $row->current();
        $dt = explode('-',$dane['t_date']);
        
        return (int)$dt[0];
    }
    
    /**
        Zapis transakcji (dodawanie lub edycja)
        @param Transaction $transaction Obiekt reprezentujący transakcję.
    */
    public function saveTransaction(Transaction $transaction)
    {
        $data = array(
            'uid' => $transaction->uid,
            'cid'  => $transaction->cid,
            't_type'  => $transaction->t_type,
            't_date'  => $transaction->t_date,
            't_content'  => $transaction->t_content,
            't_value'  => $transaction->t_value,
        );
        
        $sql = new Sql($this->adapter);

        $tid = (int)$transaction->tid;
        if ($tid == 0) { // dodanie nowego wpisu
            $insert = $sql->insert();
            $insert->into('transaction');
            $insert->values($data);
            
            $statement = $sql->prepareStatementForSqlObject($insert);
            $statement->execute();
        } else { // edycja
            // Spr. czy istnieje
            if ($this->getTransaction($tid, $data['uid'])) {
                
                $update = $sql->update();
                
                $update->table('transaction');
                $update->set($data);
                $update->where(array('tid' => $tid));
                
                $statement = $sql->prepareStatementForSqlObject($update);
                $statement->execute();
            } else {
                throw new \Exception('Wybrana transakcja nie istnieje!');
            }
        }
    }
    
    /**
        Pobranie wybranej transakcji
        @param int $tid Identyfikator transakcji
        @param int $uid Identyfikator usera
        @return Transaction Zwraca obiekt reprezentujący transakcję
    */
    public function getTransaction($tid, $uid)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        
        $select->from(array('t' => 'transaction'))
                ->join(array('c' => 'category'),'t.cid = c.cid')
                ->where(array('t.tid' => (int)$tid,
                              't.uid' => (int)$uid));
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        
        if (!$row) {
            throw new \Exception("Nie można znaleźć rekordu $tid");
        }
        
        $transaction = new Transaction();
        $transaction->exchangeArray($row->current());
        
        return $transaction;
    }
    
    /**
        Usunięcie transakcji
        @param int $tid Identyfikator transakcji
        @param int $uid Identyfikator usera
    */
    public function deleteTransaction($tid, $uid)
    {
        $sql = new Sql($this->adapter);
        
        $delete = $sql->delete();
        $delete->from('transaction');
        $delete->where(array('tid' => (int)$tid,
                             'uid' => (int)$uid));
        
        $statement = $sql->prepareStatementForSqlObject($delete);
        $row = $statement->execute();
    }
    
    /**
        Pobiera sumę transakcji wybranego typu z wybranej daty lub zakresu dat
        @param int $uid Identyfikator usera
        @param array $dt tablica z parametrami daty $dt {
                                                        'type' => 'month/between/all',
                                                        'dt_month' => 'yyyy-mm' dla mieniąca lub 'dt_up' i 'dt_down' dla zakresu
                                                    }
        @param int $t_type Typ transakcji (0 - przychód, 1 - wydatek)
        @return int Najmniejszy rok dostępny w transakcjach usera
    */
    public function getSumOfTransactions($uid, $dt, $t_type)
    {
        // Spr czy parametr z datą jest tablicą
        if (!is_array($dt)) {
            throw new \Exception("Parametr z datą musi być tablicą!");
        }
        // Spr. pola z typem
        if (!isset($dt['type'])) {
            throw new \Exception("Brak typu daty w parametrach z tablicą!");
        }
        
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        
        $select->columns(array('sm' => new Expression('SUM(t.t_value)')));
        $select->from(array('t' => 'transaction'))
                ->where(array('t.uid' => (int)$uid,
                              't.t_type' => (int)$t_type,
                              ));
            
        // Wybrany miesiąc    
        if ($dt['type'] == 'month') {
            
            // Spr. parametru
            if (!isset($dt['dt_month'])) {
                throw new \Exception("Brak parametru z miesiącem!");
            }
            //echo $dt['dt_month'];
            $select->where(array(
                              't.t_date LIKE ?' => (string)$dt['dt_month'].'-%',
                              ));
            
        } elseif ($dt['type'] == 'between') { // Wybrany zakres
            
            // Spr. parametru
            if (!(isset($dt['dt_up'])&&isset($dt['dt_down']))) {
                throw new \Exception("Brak parametru z zakresem dat!");
            }
            
            $select->where(array(
                              't.t_date >= ?' => $dt['dt_down'],
                              't.t_date <= ?' => $dt['dt_up'],
                              ));
            
        }
        // Jeśli typ inny niż 2 powyższe to sumuje cały zakres
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        
        $suma = $row->current();
        
        return ($suma['sm']==null)?(0):($suma['sm']);
    }

}
