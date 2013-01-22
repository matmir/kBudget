<?php
/**
    @author Mateusz Mirosławski
    
    Klasa analizująca transakcje.
*/

namespace Budget\Model;

class TransactionAnalyzer
{
    /**
        Tworzy dane potrzebne do wykresu kołowego (podział na kategorie).
        @param array() $transactions Tablica z transakcjami danego typu (przychód lub wydatek).
        @param array() $categories Tablica z kategoriami danego typu (przychód lub wydatek).
        @return array() Tablica z danymi i etykietami dla wykresu (ret['data']=dane, ret['labels']=etykiety)
    */
    public function categoryPie($transactions, $categories)
    {
        // Etykiety
        $labels = array();
        // cidy
        $cids = array();
        // Dane
        $data = array();
        
        foreach ($categories as $category) {
            // Umieszczenie etykiety
            array_push($labels, $category->c_name);
            // Umieszczenie cida
            array_push($cids, $category->cid);
            // inicjalizacja danych
            array_push($data,0);
        }
        
        foreach ($transactions as $transaction) {
            // Wyszukanie klucza (pozycja w tablicy z danymi do której dodawać)
            $key = array_search($transaction->cid,$cids);
            
            // Sumowanie wartości
            $data[$key] = $data[$key] + $transaction->t_value;
        }
        
        return array(
            'data' => $data,
            'cids' => $cids,
            'labels' => $labels,
        );
    }
    
    /**
        Umieszcza dane z transakcji w tablicy dla wykresu czasowego
        @param array() $transactions Tablica z obiektami transakcji
        @return array() Tablica z danymi do wykresu ret=array(
                                                        'data' => array(z wartościami)
                                                        'labels' => array(etykiety - daty)
                                                    )
    */
    public function makeTimeArray($transactions)
    {
        $data = array(
            'data' => array(),
            'labels' => array(),
        );
        
        $i=-1;
        $prev_label = '0';
        foreach ($transactions as $transaction) {
            
            // Spr czy poprzednia data jest inna niż aktualna
            if ($prev_label == $transaction->t_date) {
                
                // Ta sama data! Sumować transakcje!
                $data['data'][$i] += $transaction->t_value;
                
            } else { // Data jest inna
                
                // Umieścić w tablicy
                array_push($data['data'], $transaction->t_value);
                array_push($data['labels'], $transaction->t_date);
                $prev_label = $transaction->t_date;
                
                $i++;
            }
        }
        
        return $data;
    }
}