<?php
/**
    @author Mateusz Mirosławski
    
    Klasa importująca wyciągi z mBank-u
*/

namespace Budget\Model\Banking;

use Budget\Model\Banking\Bank;
use Budget\Model\Transaction;

class mBank extends Bank
{
    
    // Wzorzec nagłówka z danymi w wyciągu
    private $header_template = "#Data operacji;#Data księgowania;#Opis operacji;#Tytuł;#Nadawca/Odbiorca;#Numer konta;#Kwota;#Saldo po operacji;";
    
    /**
        Konstruktor
        @param string $file Nazwa załadowanego pliku z wyciągiem
        @param int $pos Pozycja wskaźnika linii w pliku
        @param int $max_lines Max. liczba linii przetwarzanych jednorazowo
    */
    public function __construct($file, $pos, $max_lines)
    {
        parent::__construct($file, $pos, $max_lines);
    }
    
    /**
        Przetwarza dane z wyciągu mBank-owego
        @return array() Tablica obiektów Transaction
    */
    protected function parse()
    {
        $ret = array();
        
        // Spr. pozycji w pliku
        if ($this->getPos() == 0) {
            // Pozycja nagłówka z danymi +1
            $hpos = $this->findDataHeaderPos()+1;
            // Ustawienie pozycji
            $this->setPos($hpos);
        }
        
        // Flaga zakończenia przetwarzania
        $this->END_PARSE = false;
        
        // Flaga zatrzymania nieskończonej pętli
        $stop = false;
        
        // znacznik przeparsowanych linii
        $pl = 0;
        
        try {
            
            // Przechodzę po kolejnych liniach w pliku zaczynając od pozycji nagłówka danych +1
            while ($stop == false) {
                
                // Pobranie linii
                $line = $this->convert(trim($this->getLine()));
                
                // Spr. czy nie trafiono na pusą linię
                if ($line == '') {
                    
                    // Koniec danych => koniec parsowania
                    $this->END_PARSE = true;
                    // Zatrzymanie pętli
                    break;
                }
                
                // Wyciągnięcie danych z linii
                $dane = explode(';', $line);
                
                if (count($dane) != 9) {
                    
                    // Flaga błędu
                    $this->ERR_PARSE = true;
                    // Zatrzymanie pętli
                    break;
                }
                
                // Umieszczenie danych w obiekcie
                $tr = new Transaction();
                $tr->t_date = $dane[0];
                $tr->t_value = abs(str_replace(array(',',' '),array('.',''),$dane[6]));
                $tr->t_type = ($dane[6]<0)?(1):(0);
                $tr->t_content = str_replace('"', '', $dane[3]);
                
                // Do tablicy wynikowej
                array_push($ret, $tr);
                
                // Zwiększenie licznika przeparsowanych linii
                $pl++;
                
                // Spr. liczby przetworzonych linii
                if ($pl == ($this->max_parse_lines)) {
                    // Zatrzymanie pętli
                    break;
                }
                
            }
            
        } catch (EndBankFileException $e) {
            // Koniec pliku - zatrzymać pętle
            $stop = true;
        }
        
        return $ret;
    }
    
    /**
        Zwraca liczbę wszystkich transakcji w parsowanym pliku
        @return int Liczba transakcji do przetworzenia
    */
    public function count()
    {
        // Odczyt starej pozycji w pliku
        $old_pos = $this->getPos();
        
        // Ustawienie pozycji na początek pliku
        $this->setPos(0);
        
        // Znalezienie nagłówka danych
        $hpos = $this->findDataHeaderPos();
        
        // Ustawienie wskaźnik na pierszą linię z danymi
        $this->setPos($hpos+1);
        
        // Flaga zatrzymania pętli
        $stop = false;
        
        // Liczba transakcji
        $tr_count = 0;
        
        try {
            
            // Pętla zliczająca
            while ($stop == false) {
                
                // Pobranie linii
                $line = $this->convert($this->getLine());
                
                // Spr. czy nie trafiono na pusą linię
                if ($line == '') {
                    // Zatrzymanie pętli
                    break;
                }
                
                // Zwiększenie liczby transakcji
                $tr_count += 1;
                
            }
            
        } catch (EndBankFileException $e) {
            // Koniec pliku - zatrzymać pętle
            $stop = true;
        }
        
        // Ustawienie wskaźnika pliku na poprzednią wartość
        $this->setPos($old_pos);
        
        return $tr_count;
    }
    
    /**
        Konwersja znaków na utf-8
        @param string $line Linia z napisami
        @return string Przekonwertowana linia znaków
    */
    private function convert($line)
    {
        $fc = iconv('windows-1250', 'utf-8', $line);
        
        return trim($fc);
    }
    
    /**
        Wyszukuje pozycję nagłówka z danymi
        @return int Pozycja (numer linii) nagłówka z danymi w pliku
    */
    private function findDataHeaderPos()
    {
        // Pierwotna pozycja wskaźnika
        $old_pos = $this->getPos();
        
        // Wskaźnik na początek pliku
        $this->setPos(0);
        
        // Pozycja nagłówka
        $hpos = 0;
        
        // Flaga zatrzymania pętli
        $stop = false;
        
        try {
            // Szukam wzorca nagłówka
            while ($stop == false) {
                
                // Odczyt linii
                $line = $this->convert($this->getLine());
                // Spr. czy nagłówek
                if ($line == $this->header_template) {
                    // Zatrzymanie pętli
                    break;
                }
                
                // Zwiększenie pozycji
                $hpos += 1;
                
            }
        } catch (EndBankFileException $e) {
            // Koniec pliku - zatrzymać pętle
            $stop = true;
        }
        
        // Przywrócenie poprzedniej pozycji wskaźnika
        $this->setPos($old_pos);
        
        return $hpos;
    }
    
}