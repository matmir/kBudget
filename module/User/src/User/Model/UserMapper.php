<?php
/**
    @author Mateusz Mirosławski
    
    Klasa zajmująca się operowaniem na danych usera w bazie danych.
*/

namespace User\Model;

use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Adapter;

use User\Model\User;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;

class UserMapper
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
        Sprawdza (po loginie) czy dany user istnieje
        @param string $u_login Login usera
        @return int Zwraca identyfikator istniejącego usera lub 0 gdy user nie istnieje
    */
    public function isUserLoginExists($u_login)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        
        $select->from(array('u' => 'users'))
                ->where(array('u.login' => (string)$u_login));
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        
        $dane = $row->current();
        
        $user = new User();
        $user->exchangeArray($dane);
        
        // Zwrócenie uid-a (gdy brak to 0)
        return ($user->uid==null)?(0):($user->uid);
    }
    
    /**
        Sprawdza czy dany e-mail istnieje w bazie
        @param string $u_email E-mail
        @return bool Zwraca true gdy podany e-mail istnieje w bazie
    */
    public function isEmailExists($u_email)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        
        $select->from(array('u' => 'users'))
                ->where(array('u.email' => (string)$u_email));
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        
        $dane = $row->current();
        
        $user = new User();
        $user->exchangeArray($dane);
        
        // Zwrócenie uid
        return ($user->uid==null)?(0):($user->uid);
    }
    
    /**
        Sprawdza czy dany e-mail należy do podanego usera
        @param string $u_email e-mail usera
        @param int $uid Identyfikator usera
        @return bool Zwraca true gdy podany e-mail należy do usera
    */
    public function isUserEmail($u_email, $uid)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        
        $select->from(array('u' => 'users'))
                ->where(array('u.email' => (string)$u_email,
                              'u.uid' => (int)$uid,
                              ));
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        
        $dane = $row->current();
        
        $user = new User();
        $user->exchangeArray($dane);
        
        // Zwrócenie true gdy jest uid w modelu
        return ($user->uid==null)?(false):(true);
    }
    
    /**
        Dodanie usera
        @param User $user Obiekt reprezentujący usera.
    */
    public function addUser(User $user)
    {
        // Wygenerowanie soli do hasła
        $dynamicSalt = '';
        for ($i = 0; $i < 50; $i++) {
            $dynamicSalt .= chr(rand(33, 126));
        }
        
        // Złożenie danych usera
        $data = array(
            'login'  => $user->login,
            'email'  => $user->email,
            'pass' => md5($user->pass),
            //'pass' => md5($user->pass . $dynamicSalt),
            'passs'=> $dynamicSalt,
            'u_type' => 0,
            'active' => 1,
            'register_date' => date('Y-m-d H:i:s'),
        );
        
        $sql = new Sql($this->adapter);

        $insert = $sql->insert();
        $insert->into('users');
        $insert->values($data);
        
        $statement = $sql->prepareStatementForSqlObject($insert);
        $statement->execute();
    }
    
    /**
        Zmiana hasła
        @param int $uid identyfikator usera
        @param string $new_pass Nowe hasło
    */
    public function changeUserPass($uid, $new_pass)
    {
        // Wygenerowanie soli do hasła
        $dynamicSalt = '';
        for ($i = 0; $i < 50; $i++) {
            $dynamicSalt .= chr(rand(33, 126));
        }
        
        // Złożenie danych usera
        $data = array(
            'pass' => md5($new_pass),
            //'pass' => md5($new_pass . $dynamicSalt),
            'passs'=> $dynamicSalt,
        );
        
        $sql = new Sql($this->adapter);

        $update = $sql->update();
        $update->table('users');
        $update->set($data);
        $update->where(array('uid' => (int)$uid));
        
        $statement = $sql->prepareStatementForSqlObject($update);
        $statement->execute();
    }
    
    /**
        Zmiana emaila
        @param int $uid identyfikator usera
        @param string $new_email Nowe hasło
    */
    public function changeUserEmail($uid, $new_email)
    {
        // Złożenie danych usera
        $data = array(
            'email' => (string)$new_email,
        );
        
        $sql = new Sql($this->adapter);

        $update = $sql->update();
        $update->table('users');
        $update->set($data);
        $update->where(array('uid' => (int)$uid));
        
        $statement = $sql->prepareStatementForSqlObject($update);
        $statement->execute();
    }
    
    /**
        Ustawia flagę aktywacji usera
        @param int $uid Identyfikator usera
        @param int $act Flaga aktywacji (0 lub 1)
    */
    public function setUserActive($uid, $act)
    {
        if (!($act==0 || $act==1)) {
            throw new \Exception("Błędna wartość flagi aktywacji użytkownika!");
        }
        
        // Złożenie danych usera
        $data = array(
            'active' => (int)$act,
        );
        
        $sql = new Sql($this->adapter);

        $update = $sql->update();
        $update->table('users');
        $update->set($data);
        $update->where(array('uid' => (int)$uid));
        
        $statement = $sql->prepareStatementForSqlObject($update);
        $statement->execute();
    }
    
    /**
        Ustawia datę ostatniego logowania usera
        @param int $uid Identyfikator usera
    */
    public function setUserLoginDate($uid)
    {
        $sql = new Sql($this->adapter);

        $update = $sql->update();
        $update->table('users');
        $update->set(array('last_login_date' => date('Y-m-d H:i:s')));
        $update->where(array('uid' => (int)$uid));
        
        $statement = $sql->prepareStatementForSqlObject($update);
        $statement->execute();
    }
    
    /**
        Pobranie danych usera
        @param int $uid Identyfikator usera
        @return User Zwraca obiekt reprezentujący usera
    */
    public function getUser($uid)
    {
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        
        $select->from(array('u' => 'users'))
                ->where(array('u.uid' => (int)$uid));
        
        $statement = $sql->prepareStatementForSqlObject($select);
        $row = $statement->execute();
        
        if (!$row) {
            throw new \Exception("Nie można znaleść rekordu $uid");
        }
        
        $user = new User();
        $user->exchangeArray($row->current());
        
        return $user;
    }
    
    /**
        Pobiera wszystkich userów z systemu
        @param int $pg Numer aktualnej strony do wyświetlenia
        @return Paginator
    */
    public function getUsers($pg)
    {
        if ($pg <=0) {
            throw new \Exception("Niepoprawny numer strony!");
        }
        
        $sql = new Sql($this->adapter);
        $select = $sql->select();
        
        $select->from(array('u' => 'users'));
        
        $paginator = new Paginator(new DbSelect($select, $sql));
        $paginator->setItemCountPerPage(20);
        $paginator->setCurrentPageNumber((int)$pg);
        
        return $paginator;
    }

}
