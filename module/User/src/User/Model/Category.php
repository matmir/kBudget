<?php
/**
    @author Mateusz Mirosławski
    
    Model reprezentujący kategorię
*/

namespace User\Model;

class Category
{
    // Pola kategorii
    public $cid;        // identyfikator kategorii
    public $uid;        // identyfikator usera
    public $c_type;     // typ kategorii (0 - przychód, 1 - wydatek)
    public $c_name;     // nazwa kategorii

    /**
        Rozbija tablicę w poszczególne pola obiektu
        @param array() $data Tablica z danymi ($data['pole']=wartość)
    */
    public function exchangeArray($data)
    {
        $this->cid = (isset($data['cid'])) ? $data['cid'] : null;
        $this->uid = (isset($data['uid'])) ? $data['uid'] : null;
        $this->c_type = (isset($data['c_type'])) ? $data['c_type'] : null;
        $this->c_name = (isset($data['c_name'])) ? $data['c_name'] : null;
    }
    
    /**
        Zwraca tablice z polami obiektu
        @return array() Tablica z polami obiektu ($tbl['pole']=wartość)
    */
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}