<?php
/**
    @author Mateusz Mirosławski
    
    Model reprezentujący transakcję
*/

namespace Budget\Model;

class Transaction
{
    // Pola transakcji
    public $tid;
    public $uid;
    public $t_type;
    public $cid;
    public $c_name;
    public $t_date;
    public $t_content;
    public $t_value;

    /**
        Rozbija tablicę w poszczególne pola obiektu
        @param array() $data Tablica z danymi ($data['pole']=wartość)
    */
    public function exchangeArray($data)
    {
        $this->tid = (isset($data['tid'])) ? $data['tid'] : null;
        $this->uid = (isset($data['uid'])) ? $data['uid'] : null;
        $this->t_type = (isset($data['t_type'])) ? $data['t_type'] : null;
        $this->cid = (isset($data['cid'])) ? $data['cid'] : null;
        $this->c_name = (isset($data['c_name'])) ? $data['c_name'] : null;
        $this->t_date = (isset($data['t_date'])) ? $data['t_date'] : null;
        $this->t_content = (isset($data['t_content'])) ? $data['t_content'] : null;
        $this->t_value = (isset($data['t_value'])) ? $data['t_value'] : null;
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