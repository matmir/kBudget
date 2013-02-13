<?php
/**
    @author Mateusz Mirosławski
    
    Abstrakcyjna klasa do importu wyciągów bankowych
*/

namespace Budget\Model\Banking;

// Klasa wyjątku generowanego przez klasę Bank gdy osiągnie koniec pliku
class EndBankFileException extends \Exception {}

abstract class Bank
{
    private $fname;             // Nazwa pliku na dysku
    private $fpos;                // Aktualna pozycja w pliku
    private $file_handle;       // Uchwyt do pliku na dysku
    protected $max_parse_lines;   // Max. liczba linii które można jednorazowo przetworzyć
    protected $END_PARSE;         // Flaga zakończenia parsowania
    protected $ERR_PARSE;         // Flaga błędu parsowania
    
    /**
        Konstruktor
        @param string $file Nazwa załadowanego pliku z wyciągiem
        @param int $pos Pozycja wskaźnika linii w pliku
        @param int $max_lines Max. liczba linii przetwarzanych jednorazowo
    */
    public function __construct($file, $pos, $max_lines)
    {
        // Spr. liczby parsowanych linii
        if ($max_lines < 2) {
            throw new \Exception('Max. liczba przetwarzanych linii musi być większa od 2!');
        }
        
        // spr. czy plik istnieje na dysku
        if (!(file_exists($file))) {
            throw new \Exception('Podanego pliku nie ma na serwerze!');
        }
        
        // Spr. pozycji wskaźnika linii
        if ($pos < 0) {
            throw new \Exception('Numer linii musi być liczbą dodatnią!');
        }
        
        $this->fname = (string)$file;
        
        // Otwarcie pliku
        $this->file_handle = fopen($this->fname, 'r');
        
        if (!$this->file_handle) {
            throw new \Exception('Nie można otworzyć pliku!');
        }
        
        // Ustawienie wskaźnika linii w pliku
        $this->setPos((int)$pos);
        
        $this->max_parse_lines = (int)$max_lines;
        $this->END_PARSE = false;
        $this->ERR_PARSE = false;
    }
    
    /**
        Destruktor
    */
    public function __destruct() {
        
        if (is_resource($this->file_handle)) {
            
            // Zamknięcie pliku
            fclose($this->file_handle);
            
        }
        
    }
    
    /**
        Przetwarza dane dla wybranego banku
        (musi zostać przesłonięta w klasie pochodnej)
    */
    protected abstract function parse();
    
    /**
        Zwraca liczbę wszystkich transakcji w parsowanym pliku
        (musi zostać przesłonięta w klasie pochodnej)
        @return int Liczba transakcji do przetworzenia
    */
    public abstract function count();
    
    /**
        Przetwarza dane z pliku
        @return array() Tablica obiektów Transaction
    */
    public function parseData()
    {
        
        $ret = $this->parse();
        
        return $ret;
        
    }
    
    /**
        Ustawia pozycję wskaźnika na danej linii
        @param int $pos Numer linii
    */
    protected function setPos($pos)
    {
        if ($pos < 0) {
            throw new \Exception('Numer linii musi być liczbą dodatnią!');
        }
        
        // Ustawienie pozycji
        $this->fpos = (int)$pos;
        
        // Wskazujemy na początek pliku
        fseek($this->file_handle, 0);
        
        // Przesuwamy się linia po linii, aż osiągniemy wartość zadaną
        $line = 0;
        while (($buff = fgets($this->file_handle)) !== false) {
            
            // Spr. czy osiągnęliśmy wartość zadaną
            if ($line == $this->fpos) {
                // Zatrzymanie pętli
                break;
            }
            
            $line++;
            
        }
        
    }
    
    /**
        Pobiera linię z pliku
        @return string Linia z pliku
    */
    protected function getLine()
    {
        if (($line = fgets($this->file_handle)) !== false) {
            
            // Zwiększenie wartości wskaźnika linii
            $this->fpos += 1;
            
            return $line;
            
        } else { // koniec pliku
            // Wyjątek
            throw EndBankFileException();
        }
    }
    
    /**
        Zwraca pozycję aktualnej linii w pliku
    */
    public function getPos()
    {
        return $this->fpos;
    }
    
    /**
        Zwraca informacje o końcu przetwarzania
        @return bool Koniec przetwarzania
    */
    public function isEndParse()
    {
        return $this->END_PARSE;
    }
    
    /**
        Flaga wystąpienia błędu przetwarzania
        @return bool Błąd przetwarzania
    */
    public function isParseError()
    {
        return $this->ERR_PARSE;
    }
}