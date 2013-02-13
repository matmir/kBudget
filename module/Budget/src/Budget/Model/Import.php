<?php
/**
    @author Mateusz Mirosławski
    
    Model reprezentujący informacje dotyczące importu wyciągu
*/

namespace Budget\Model;

class Import
{
    // Pola importu
    public $uid;        // Identyfikator usera
    public $fname;      // Nazwa pliku z wyciągiem
    public $bank;       // Nazwa banku
    public $fpos;       // Aktualna pozycja w pliku
    public $nfpos;      // Nowa pozycja w pliku (aktualna pozycja zostanie zaktualizowana po zapisie transakcji)
    public $count;      // Liczba wszystkich transakcji do zaimportowania
    public $counted;    // Zaimportowane transakcje

    /**
        Rozbija tablicę w poszczególne pola obiektu
        @param array() $data Tablica z danymi ($data['pole']=wartość)
    */
    public function exchangeArray($data)
    {
        $this->uid = (isset($data['uid'])) ? $data['uid'] : null;
        $this->fname = (isset($data['fname'])) ? $data['fname'] : null;
        $this->bank = (isset($data['bank'])) ? $data['bank'] : null;
        $this->fpos = (isset($data['fpos'])) ? $data['fpos'] : null;
        $this->nfpos = (isset($data['nfpos'])) ? $data['nfpos'] : null;
        $this->count = (isset($data['count'])) ? $data['count'] : null;
        $this->counted = (isset($data['counted'])) ? $data['counted'] : null;
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
        Zwraca listę obsługiwanych banków
        @return array() Tablica z nazwami banków
    */
    public static function getBankList()
    {
        // Otwarcie katalogu
    	$dir=opendir('module/Budget/src/Budget/Model/Banking/');
        
	$file_list = array();
        
        // Odczyt wszystkich plików w katalogu
	while($file_name=readdir($dir)) {
            
            // Jeśli nie kropki oraz klasa bazowa
            if(($file_name!=".")&&($file_name!="..")&&($file_name!="Bank.php")) {
                
                $fn = explode('.', $file_name);
                array_push($file_list, $fn[0]);
                
            }
	}
	closedir($dir);
        
        return $file_list;
    }
}