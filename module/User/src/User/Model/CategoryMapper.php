<?php
/**
    @author Mateusz Mirosławski
    
    Klasa zajmująca się wyciągniem kategorii z bazy danych
*/

namespace User\Model;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;
use Zend\Db\Adapter\Adapter;

use User\Model\Category;

class CategoryMapper
{
    protected $adapter;

    /**
        Konstruktor.
        @param Adapter $adp Obiekt reprezentujący adapter podłączony do bazy danych
    */
    public function __construct(Adapter $adp)
    {
        $this->adapter = $adp;
    }
    
    /**
        Pobiera wszystkie kategorie
        @param int $uid Identyfikator usera
        @param int $c_type Typ kategorii (-1 - wszystkie, 0 - przychód, 1 - wydatek)
        @return array() Tablica zawierająca kategorie (Category)
    */
    public function getCategories($uid, $c_type=-1)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        
        $select->from(array('c' => 'category'))
                ->where(array(
                              'c.uid' => (int)$uid,
                              )
                       )
                ->order(array(
                              'c.c_name ASC',
                              ));
                
        // Typ kategorii do pobrania
        if ($c_type != -1) {
            
            // Spr. parametru
            if (!($c_type==0 || $c_type==1)) {
                throw new \Exception("Niepoprawny parametr z typem kategorii!!");
            }
            
            $select->where(array('c.c_type' => (int)$c_type));
            
        }
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();
        
        $retObj = array();
        
        // Przelatuję po wynikach
        while (($tbl=$results->current())!=null)
        {
            $ob = new Category();
            $ob->exchangeArray($tbl);
            array_push($retObj, $ob);
        }
        
        return $retObj;
    }
    
    /**
        Pobiera kategorie usera dla elementu select formularza
        @param int $uid Identyfikator usera
        @param int $c_type Typ kategorii (0 - przychód, 1 - wydatek)
        @return array() Tablica z kategoriami (ret[id_kategorii] = nazwa_kategorii)
    */
    public function getUserCategoriesToSelect($uid, $c_type)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        
        $select->from(array('c' => 'category'))
                ->where(array('c.uid' => (int)$uid,
                              'c.c_type' => (int)$c_type));
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $results = $statement->execute();
        
        // Umieszczenie w tablicy
        $retObj = array();
        // Domyślny wpis
        $retObj['0'] = 'Wybierz...';
        
        // Przelatuję po wynikach
        while (($tbl=$results->current())!=null)
        {
            $retObj[$tbl['cid']] = $tbl['c_name'];
        }
        
        // Zwrócenie tablicy
        return $retObj;
    }
    
    /**
        Sprawdza (po nazwie) czy dana kategoria istnieje
        @param string $c_name Nazwa kategorii
        @param int $c_type Typ kategorii (kategorie mogą się powtarzać jeśli są innego typu)
        @param int $uid Identyfikator usera
        @return int Zwraca identyfikator istniejącej kategorii lub 0 gdy kategoria nie istnieje
    */
    public function isCategoryNameExists($c_name, $c_type, $uid)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        
        $select->from(array('c' => 'category'))
                ->where(array('c.c_name' => (string)$c_name,
                              'c.uid' => (int)$uid,
                              'c.c_type' => (int)$c_type,
                              ));
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        
        $dane = $row->current();
        
        $category = new Category();
        $category->exchangeArray($dane);
        
        // Zwrócenie cid-a (gdy brak to 0)
        return ($category->cid==null)?(0):($category->cid);
    }
    
    /**
        Zapis kategorii (dodawanie lub edycja)
        @param Category $category Obiekt reprezentujący kategorię.
    */
    public function saveCategory(Category $category)
    {
        $data = array(
            'uid' => (int)$category->uid,
            'c_type'  => (int)$category->c_type,
            'c_name'  => (string)$category->c_name,
        );
        
        $sql = new Sql($this->adapter);

        $cid = (int)$category->cid;
        if ($cid == 0) { // dodanie nowego wpisu
            $insert = $sql->insert();
            $insert->into('category');
            $insert->values($data);
            
            $statement = $sql->prepareStatementForSqlObject($insert);
            $statement->execute();
        } else { // edycja
            // Spr. czy istnieje
            if ($this->getCategory($cid, $data['uid'])) {
                
                $update = $sql->update();
                
                $update->table('category');
                $update->set($data);
                $update->where(array('cid' => $cid));
                
                $statement = $sql->prepareStatementForSqlObject($update);
                $statement->execute();
            } else {
                throw new \Exception('Wybrana kategoria nie istnieje!');
            }
        }
    }
    
    /**
        Pobranie wybranej kategorii
        @param int $cid Identyfikator kategorii
        @param int $uid Identyfikator usera
        @return Category Zwraca obiekt reprezentujący kategorię
    */
    public function getCategory($cid, $uid)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        
        $select->from(array('c' => 'category'))
                ->where(array('c.cid' => (int)$cid,
                              'c.uid' => (int)$uid));
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        
        if (!$row) {
            throw new \Exception("Nie można znaleźć rekordu $cid");
        }
        
        $category = new Category();
        $category->exchangeArray($row->current());
        
        return $category;
    }
    
    /**
        Sprawdza czy dana kategoria jest pusta (nie zawiera transakcji)
        @param int $cid Identyfikator kategorii
        @param int $uid Identyfikator usera
        @return bool True jeśli kategoria jest pusta
    */
    public function isCategoryEmpty($cid, $uid)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        
        $select->columns(array('cn' => new Expression('count(*)')))
                ->from(array('t' => 'transaction'))
                ->where(array('t.cid' => (int)$cid,
                              't.uid' => (int)$uid,
                              ));
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        
        $dane = $row->current();
        
        // Zwrócenie true jeśli cn = 0 (brak transakcji)
        return ($dane['cn']==0)?(true):(false);
    }
    
    /**
        Usunięcie kategorii
        @param int $tid Identyfikator kategorii
        @param int $uid Identyfikator usera
    */
    public function deleteCategory($cid, $uid)
    {
        $sql = new Sql($this->adapter);
    
        $delete = $sql->delete();
        $delete->from('category');
        $delete->where(array('cid' => (int)$cid,
                             'uid' => (int)$uid));
        
        $statement = $sql->prepareStatementForSqlObject($delete);
        $row = $statement->execute();
    }

}
