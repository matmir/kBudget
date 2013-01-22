<?php
/**
    @author Mateusz Mirosławski
    
    Model reprezentujący usera
*/

namespace Budget\Model;

class User
{
    // Pola usera
    public $uid;        // Identyfikator usera
    public $email;
    public $login;
    public $pass;
    public $passs;
    public $u_type;     // Typ usera 0 - user, 1 - admin
    public $active;     // flaga aktywności usera 0/1

    /**
        Rozbija tablicę w poszczególne pola obiektu
        @param array() $data Tablica z danymi ($data['pole']=wartość)
    */
    public function exchangeArray($data)
    {
        $this->uid = (isset($data['uid'])) ? $data['uid'] : null;
        $this->email = (isset($data['email'])) ? $data['email'] : null;
        $this->login = (isset($data['login'])) ? $data['login'] : null;
        $this->pass = (isset($data['pass'])) ? $data['pass'] : null;
        $this->passs = (isset($data['passs'])) ? $data['passs'] : null;
        $this->u_type = (isset($data['u_type'])) ? $data['u_type'] : null;
        $this->active = (isset($data['active'])) ? $data['active'] : null;
    }
    
    /**
        Zwraca tablice z polami obiektu
        @return array() Tablica z polami obiektu ($tbl['pole']=wartość)
    */
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
    
    /**
        Generuje nowe hasło.
        @return string String z hasłem
    */
    public function genNewPass()
    {
        $pas = '';
        for ($i = 0; $i < 8; $i++) {
            $pas .= chr(rand(33, 126));
        }
        
        return $pas;
    }
}