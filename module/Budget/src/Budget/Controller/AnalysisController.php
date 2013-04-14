<?php
/**
    @author Mateusz Mirosławski
    
    Kontroler zajmujący się analizą transakcji.
*/

namespace Budget\Controller;

use Base\Controller\BaseController;

use Budget\Model\Transaction;
use Budget\Model\TransactionMapper;

use User\Model\Category;
use User\Model\CategoryMapper;

use Budget\Form\TransactionRangeSelectForm;
use Budget\Form\TransactionRangeSelectFilter;

use Budget\Form\TransactionBetweenSelectForm;
use Budget\Form\TransactionBetweenSelectFormFilter;

use Budget\Form\TransactionTimeSelectForm;
use Budget\Form\TransactionTimeSelectFormFilter;

use Budget\Model\TransactionAnalyzer;
use Budget\Model\ChartsPlotter;

class AnalysisController extends BaseController
{
    // Główna strona
    public function indexAction()
    {
    }
    
    // Podział na kategorie
    public function categoryAction()
    {
        // Identyfikator zalogowanego usera
        $uid = $this->get('userId');
        
        // Ustawienia ścieżek
        $cfg = $this->get('img_dirs');
        // Ustawienia nazw obrazków
        $img = $this->get('img_nm');
        
        // Formularz filtrujący
        $form = new TransactionTimeSelectForm();
        // Minimalny rok w transakcjach usera
        $minYear = $this->get('Budget\TransactionMapper')->getMinYearOfTransaction($uid);
        // Filtracja formularza
        $formFilters = new TransactionTimeSelectFormFilter($minYear);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($formFilters->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                
                // Spr. typu filtracji
                $ft = $Y = $form->get('filter_type')->getValue();
                
                // Filtracja po zakresie
                if ($ft == 'between') {
                    
                    $date_from = $form->get('date_from')->getValue();
                    $date_to = $form->get('date_to')->getValue();
                    // Parametry daty
                    $date_param = array(
                        'type' => 'between',
                        'dt_up' => $date_to,
                        'dt_down' => $date_from,
                    );
                    
                } elseif ($ft == 'all') { // Cały zakres
                    
                    // Parametry daty
                    $date_param = array(
                        'type' => 'all',
                    );
                    
                } else { // Filtracja po miesiącu
                    
                    $Y = $form->get('year')->getValue();
                    $m = $form->get('month')->getValue();
                    $ms = ($m < 10) ? ((string)'0'.$m) : ((string)$m);
                    $m = $ms;
                    // Parametry daty
                    $date_param = array(
                        'type' => 'month',
                        'dt_month' => $Y.'-'.$m,
                    );
                    
                }
                
            } else { // Błąd formularze (ktoś coś kombinuje)
                // Aktualna data
                $Y = date('Y');
                $m = date('m');
                // Parametry daty
                $date_param = array(
                    'type' => 'month',
                    'dt_month' => $Y.'-'.$m,
                );
            }
        } else { // Brak parametrów
            // Aktualna data
            $Y = date('Y');
            $m = date('m');
            // Parametry daty
            $date_param = array(
                'type' => 'month',
                'dt_month' => $Y.'-'.$m,
            );
            // Ustawienie formularza
            $form->get('filter_type')->setValue('month');
            $form->get('month')->setValue($m);
            $form->get('year')->setValue($Y);
            $form->get('date_from')->setValue($Y.'-'.$m.'-01');
            $form->get('date_to')->setValue(date('Y-m-d'));
        }
        
        // Pobranie przychodów
        $tr_profit = $this->get('Budget\TransactionMapper')->getTransactions($uid, $date_param, 0);
        // Pobranie wydatków
        $tr_expense = $this->get('Budget\TransactionMapper')->getTransactions($uid, $date_param, 1);
        
        // Pobranie kategorii z zyskami
        $cat_profit = $this->get('User\CategoryMapper')->getCategories($uid, 0);
        $cat_expense = $this->get('User\CategoryMapper')->getCategories($uid, 1);
        
        // Analizer
        $analyzer = new TransactionAnalyzer();
        // Generator wykresów
        $plotter = new ChartsPlotter($cfg['Zend_dir'], $cfg['browser_dir']);
        
        if (count($tr_expense) > 0) {
            // Przygotowanie analizy dla wydatków
            $dt_epxense = $analyzer->categoryPie($tr_expense, $cat_expense);
            // Nazwa obrazu
            $fn_ex = md5($uid.$img['pie_expense']).$img['img_ex'];
            // Generacja obrazu
            $img_epxense = $plotter->genPie($dt_epxense,'Wydatki',$fn_ex);
        }
        
        if (count($tr_profit)) {
            // Przygotowanie analizy dla przychodów
            $dt_profit = $analyzer->categoryPie($tr_profit, $cat_profit);
            // Nazwa obrazu
            $fn_pr = md5($uid.$img['pie_profit']).$img['img_ex'];
            // Generacja obrazu
            $img_profit = $plotter->genPie($dt_profit,'Przychody',$fn_pr);
        }
        
        return array(
            'img_expense' => (isset($img_epxense))?($img_epxense):(0),
            'img_profit' => (isset($img_profit))?($img_profit):(0),
            'dt_expense' => (isset($dt_epxense))?($dt_epxense):(0),
            'dt_profit' => (isset($dt_profit))?($dt_profit):(0),
            'form' => $form,
            
        );
    }
    
    // Podział czasowy
    public function timeAction()
    {
        // Identyfikator zalogowanego usera
        $uid = $this->get('userId');
        
        // Ustawienia ścieżek
        $cfg = $this->get('img_dirs');
        // Ustawienia nazw obrazków
        $img = $this->get('img_nm');
        
        // Formularz filtrujący
        $form = new TransactionTimeSelectForm();
        // Minimalny rok w transakcjach usera
        $minYear = $this->get('Budget\TransactionMapper')->getMinYearOfTransaction($uid);
        // Filtracja formularza
        $formFilters = new TransactionTimeSelectFormFilter($minYear);
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($formFilters->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                
                // Spr. typu filtracji
                $ft = $Y = $form->get('filter_type')->getValue();
                
                // Filtracja po zakresie
                if ($ft == 'between') {
                    
                    $date_from = $form->get('date_from')->getValue();
                    $date_to = $form->get('date_to')->getValue();
                    // Parametry daty
                    $date_param = array(
                        'type' => 'between',
                        'dt_up' => $date_to,
                        'dt_down' => $date_from,
                    );
                    
                } elseif ($ft == 'all') { // Cały zakres
                    
                    // Parametry daty
                    $date_param = array(
                        'type' => 'all',
                    );
                    
                } else { // Filtracja po miesiącu
                    
                    $Y = $form->get('year')->getValue();
                    $m = $form->get('month')->getValue();
                    $ms = ($m < 10) ? ((string)'0'.$m) : ((string)$m);
                    $m = $ms;
                    // Parametry daty
                    $date_param = array(
                        'type' => 'month',
                        'dt_month' => $Y.'-'.$m,
                    );
                    
                }
                
            } else { // Błąd formularze (ktoś coś kombinuje)
                // Aktualna data
                $Y = date('Y');
                $m = date('m');
                // Parametry daty
                $date_param = array(
                    'type' => 'month',
                    'dt_month' => $Y.'-'.$m,
                );
            }
        } else { // Brak parametrów
            // Aktualna data
            $Y = date('Y');
            $m = date('m');
            // Parametry daty
            $date_param = array(
                'type' => 'month',
                'dt_month' => $Y.'-'.$m,
            );
            // Ustawienie formularza
            $form->get('filter_type')->setValue('month');
            $form->get('month')->setValue($m);
            $form->get('year')->setValue($Y);
            $form->get('date_from')->setValue($Y.'-'.$m.'-01');
            $form->get('date_to')->setValue(date('Y-m-d'));
        }
        
        /* ----------------------------- WYKRES SŁUPKOWY ----------------------------------- */
        
        // Pobranie sumy wydatków
        $sum_expense = $this->get('Budget\TransactionMapper')->getSumOfTransactions($uid, $date_param, 1);
        // Pobranie sumy przychodów
        $sum_profit = $this->get('Budget\TransactionMapper')->getSumOfTransactions($uid, $date_param, 0);
        
        // Bilans
        $balance = $sum_profit - $sum_expense;
        
        // Generator wykresów
        $plotter = new ChartsPlotter($cfg['Zend_dir'], $cfg['browser_dir']);
        
        // Parametry dla wykresu
        $chart_param = array(
            'data' => array($sum_profit, $sum_expense),
            'labels' => array('Przychód','Wydatki'),
        );
        
        // Nazwa generowanego pliku
        $fn_balance = md5($uid.$img['balacne']).$img['img_ex'];
        // Generacja wykresu
        $balance_chart = $plotter->genBar($chart_param, 'Bilans', $fn_balance);
        
        /* -------------------------- WYKRES CZASOWY ----------------------------------------- */
        // Analizer
        $analyzer = new TransactionAnalyzer();
        
        // Pobranie transakcji
        $tr_profit = $this->get('Budget\TransactionMapper')->getTransactions($uid, $date_param, 0);
        $tr_expense = $this->get('Budget\TransactionMapper')->getTransactions($uid, $date_param, 1);
        
        // Spr. czy są zyski
        if (count($tr_profit)) {
            
            // Przygotowanie tablicy z danymi
            $dt_profit = $analyzer->makeTimeArray($tr_profit);
            
            // Spr. czy jest więcej niż 2 dane
            if (count($dt_profit['data'])<3) {
                
                // Flaga niewyświetlania wykresu (tylko jedna lub dwie porcje danych - bez sensu)
                $ONE_PROFIT = count($dt_profit['data']);
                
            } else { // generacja wykresu
                
                // Nazwa generowanego pliku
                $fn_time_pr = md5($uid.$img['time_profit']).$img['img_ex'];
                // Generacja wykresu
                $time_profit_chart = $plotter->genTime($dt_profit, 'Wykres czasowy przychodów', $fn_time_pr);
                
            }
            
        }
        
        // Spr. czy są wydatki
        if (count($tr_expense)) {
            
            $dt_expense = $analyzer->makeTimeArray($tr_expense);
            
            // Spr. czy jest więcej niż 2 dane
            if (count($dt_expense['data'])<3) {
                
                // Flaga niewyświetlania wykresu (tylko jedna lub dwie porcje danych - bez sensu)
                $ONE_EXPENSE = count($dt_expense['data']);
                
            } else { // generacja wykresu
                
                // Nazwa generowanego pliku
                $fn_time_ex = md5($uid.$img['time_expense']).$img['img_ex'];
                // Generacja wykresu
                $time_expense_chart = $plotter->genTime($dt_expense, 'Wykres czasowy wydatków', $fn_time_ex);
                
            }
            
        }
        
        return array(
            'sum_profit' => $sum_profit,
            'sum_expense' => $sum_expense,
            'balance' => $balance,
            'balance_chart' => $balance_chart,
            'ONE_EXPENSE' => (isset($ONE_EXPENSE))?($ONE_EXPENSE):(0),
            'dt_expense' => (isset($dt_expense))?($dt_expense):(0),
            'time_expense_chart' => (isset($time_expense_chart))?($time_expense_chart):(0),
            'ONE_PROFIT' => (isset($ONE_PROFIT))?($ONE_PROFIT):(0),
            'dt_profit' => (isset($dt_profit))?($dt_profit):(0),
            'time_profit_chart' => (isset($time_profit_chart))?($time_profit_chart):(0),
            'form' => $form,
            
        );
    }

}