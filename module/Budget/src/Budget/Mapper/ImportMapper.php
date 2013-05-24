<?php
/**
    @author Mateusz Mirosławski
    
    Klasa zajmująca się rejestrowaniem przebiegu importu w bazie
*/

namespace Budget\Mapper;

use Base\Mapper\BaseMapper;

use Zend\Db\Sql\Sql;

use Budget\Model\Import;

class ImportMapper extends BaseMapper
{
    /**
     * MySQL import table name
     *
     * @var string
     */
    const TABLE = 'imports';
    
    /**
        Pobiera informacje dotyczące aktualnego importu wyciągu
        @param int $uid Identyfikator usera
        @return Jeśli są informacja to Obiekt Import inaczej null
    */
    public function getUserImport($uid)
    {
        $sql = new Sql($this->getDbAdapter());
        $select = $sql->select();
        
        $select->from(array('i' => self::TABLE))
                ->where(array('i.uid' => (int)$uid));
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        
        $data = $row->current();
        
        // Spr. czy są informacje
        if ($data==null) {
            
            return null;
        
        } else {
            
            $import = new Import();
            $import->exchangeArray($data);
            
            return $import;
            
        }
    }
    
    /**
        Ustawia informacje dotyczące importu wyciągu
        @param Import Obiekt z informacjami dotyczącymi importu
    */
    public function setUserImport($import)
    {
        $data = array(
            'fname' => (string)$import->fname,
            'bank' => (string)$import->bank,
            'fpos' => (int)$import->fpos,
            'nfpos' => (int)$import->nfpos,
            'count' => (int)$import->count,
            'counted' => (int)$import->counted,
        );
        
        $sql = new Sql($this->getDbAdapter());

        if ($this->getUserImport($import->uid) == null) { // dodanie nowego wpisu
            
            $data['uid'] = (int)$import->uid;
            
            $insert = $sql->insert();
            $insert->into(self::TABLE);
            $insert->values($data);
            
            $statement = $sql->prepareStatementForSqlObject($insert);
            $statement->execute();
            
        } else { // edycja
                
            $update = $sql->update();
            
            $update->table(self::TABLE);
            $update->set($data);
            $update->where(array('uid' => (int)$import->uid));
            
            $statement = $sql->prepareStatementForSqlObject($update);
            $statement->execute();
        }
    }
    
    /**
        Usunięcie informacji o imporcie z bazy
        @param int $uid Identyfikator usera
    */
    public function delUserImport($uid)
    {
        $sql = new Sql($this->getDbAdapter());
    
        $delete = $sql->delete();
        $delete->from(self::TABLE);
        $delete->where(array('uid' => (int)$uid));
        
        $statement = $sql->prepareStatementForSqlObject($delete);
        $row = $statement->execute();
    }

}
