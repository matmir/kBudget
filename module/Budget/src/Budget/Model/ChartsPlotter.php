<?php
/**
    @author Mateusz Mirosławski
    
    Klasa generująca wykresy.
*/

namespace Budget\Model;

use PieGraph;
use PiePlot;
use Graph;
use UniversalTheme;
use BarPlot;
use LinePlot;

class ChartsPlotter
{
    private $Zend_img_dir;      // Ścieżka zapisu obrazków dla Zend-a
    private $Browser_img_dir;   // Ścieżka obrazków dla przeglądarki
    
    /**
        Konstruktor
        @param string $Zend_imgDir Ścieżka do zapisu obrazków dla Zend-a
        @param string $browser_imgDir Ścieżka do generowanych obrazków dla przeglądarki
    */
    public function __construct($Zend_imgDir, $browser_imgDir)
    {
        $this->Zend_img_dir = $Zend_imgDir;
        $this->Browser_img_dir = $browser_imgDir;
    }
    
    /**
        Generuje wykres kołowy podzielony na kategorie.
        @param array() $dt Tablica z danymi do wykresu (dt['data'] oraz dt['labels'])
        @param string $title Tytuł wykresu
        @param string $fName Nazwa pliku jaki ma mieć wygenerowany obrazek
        @return string Ścieżka do wygenerowanego obrazka (dla przeglądarki)
    */
    public function genPie($dt, $title, $fName)
    {
        // Spr. czy podano poprawne dane
        if (!is_array($dt))
            throw new \Exception('Podana zmienna nie jest tablicą!');
        // Spr. czy zawierają odpowiednie pola
        if (!isset($dt['labels']))
            throw new \Exception('Podana tablica nie zawiera pola z etykietami!');
        if (!isset($dt['data']))
            throw new \Exception('Podana tablica nie zawiera pola z danymi!');
        
        // Some data
        $data = $dt['data'];
        $label = $dt['labels'];
        
        // Spr. czy są zerowe dane
        if (in_array(0, $data)) {
            $data_n = array();
            $label_n = array();
            
            // Usunięcie pustych danych
            $in = 0;
            for ($i=0; $i<count($data); $i++) {
                // Są dane
                if ($data[$i] != 0) {
                    $data_n[$in] = $data[$i];
                    $label_n[$in] = $label[$i];
                    $in++;
                }
            }
            
            /*print_r($data);
            echo '<br />';
            print_r($label);
            echo '<br />';
            
            print_r($data_n);
            echo '<br />';
            print_r($label_n);
            echo '<br /><br />';*/
            
            $data = $data_n;
            $label = $label_n;
            
        }
        
        // Formowatowanie etykiet
        for ($i=0; $i<count($label); $i++) {
            $label[$i] = $label[$i]."\n(%.1f%%)";
        }
        
        // Create the Pie Graph. 
        $graph = new PieGraph(350,330);
        
        //$theme_class="DefaultTheme";
        //$graph->SetTheme(new $theme_class());
        
        // Set A title for the plot
        $graph->title->Set($title);
        $graph->SetBox(true);
        
        // Create
        $p1 = new PiePlot($data);
        
        //$p1->SetSize(0.32);
        //$p1->SetCenter(0.1, 0.5);
        $p1->ShowBorder();
        $p1->SetColor('black');
        //$p1->SetSliceColors(array('#1E90FF','#2E8B57','#ADFF2F','#DC143C','#BA55D3','#BA5555'));
        
        $p1->SetGuideLines(true, true, true);
        //$p1->SetGuideLinesAdjust(1.1);
        
        $p1->SetLabels($label);
        $p1->SetLabelPos(1);
        $p1->SetLabelType(PIE_VALUE_PER);
        $p1->value->Show();
        
        $graph->Add($p1);
        
        // Wygenerowanie obrazka
        $gdImgHandler = $graph->Stroke(_IMG_HANDLER);
        
        // Zapis obrazka
        $graph->img->Stream($this->Zend_img_dir.$fName);
        
        return $this->Browser_img_dir.$fName;
    }
    
    /**
        Generuje wykres słupkowy.
        @param array() $dt Tablica z danymi do wykresu (dt['data'] oraz dt['labels'])
        @param string $title Tytuł wykresu
        @param string $fName Nazwa pliku jaki ma mieć wygenerowany obrazek
        @return string Ścieżka do wygenerowanego obrazka (dla przeglądarki)
    */
    public function genBar($dt, $title, $fName)
    {
        // Spr. czy podano poprawne dane
        if (!is_array($dt))
            throw new \Exception('Podana zmienna nie jest tablicą!');
        // Spr. czy zawierają odpowiednie pola
        if (!isset($dt['labels']))
            throw new \Exception('Podana tablica nie zawiera pola z etykietami!');
        if (!isset($dt['data']))
            throw new \Exception('Podana tablica nie zawiera pola z danymi!');
        
        // Create the graph. These two calls are always required
        $graph = new Graph(500,150,'auto');
        $graph->SetScale("textlin");
        
        $theme_class=new UniversalTheme;
        $graph->SetTheme($theme_class);
        
        $graph->Set90AndMargin(70,20,40,40);
        $graph->img->SetAngle(90); 
        
        // set major and minor tick positions manually
        $graph->SetBox(false);
        
        //$graph->ygrid->SetColor('gray');
        $graph->ygrid->Show(false);
        $graph->ygrid->SetFill(false);
        $graph->xaxis->SetTickLabels($dt['labels']);
        $graph->yaxis->HideLine(false);
        $graph->yaxis->HideTicks(false,false);
        
        // For background to be gradient, setfill is needed first.
        $graph->SetBackgroundGradient('#00CED1', '#FFFFFF', GRAD_HOR, BGRAD_PLOT);
        
        // Create the bar plots
        $b1plot = new BarPlot($dt['data']);
        
        // ...and add it to the graPH
        $graph->Add($b1plot);
        
        $b1plot->SetWeight(0);
        $b1plot->SetFillGradient("#808000","#90EE90",GRAD_HOR);
        $b1plot->SetWidth(17);
        
        // Wygenerowanie obrazka
        $gdImgHandler = $graph->Stroke(_IMG_HANDLER);
        
        // Zapis obrazka
        $graph->img->Stream($this->Zend_img_dir.$fName);
        
        return $this->Browser_img_dir.$fName;
    }
    
    /**
        Generuje wykres czasowy.
        @param array() $dt Tablica z danymi do wykresu (dt['data'] oraz dt['labels'])
        @param string $title Tytuł wykresu
        @param string $fName Nazwa pliku jaki ma mieć wygenerowany obrazek
        @return string Ścieżka do wygenerowanego obrazka (dla przeglądarki)
    */
    public function genTime($dt, $title, $fName)
    {
        // Setup the graph
        $graph = new Graph(530,300);
        $graph->SetScale("datlin");
        
        $theme_class=new UniversalTheme;
        
        $graph->SetTheme($theme_class);
        $graph->img->SetAntiAliasing(false);
        $graph->title->Set($title);
        $graph->SetBox(false);
        
        $graph->img->SetAntiAliasing();
        
        $graph->yaxis->HideZeroLabel();
        $graph->yaxis->HideLine(false);
        $graph->yaxis->HideTicks(false,false);
        
        $graph->xgrid->Show();
        $graph->xgrid->SetLineStyle("solid");
        $graph->xaxis->SetTickLabels($dt['labels']);
        $graph->xgrid->SetColor('#E3E3E3');
        
        $graph->xaxis->SetLabelAngle(40);
        
        $graph->SetMargin(60,10,20,60);
        
        // Create the first line
        $p1 = new LinePlot($dt['data']);
        $graph->Add($p1);
        $p1->SetColor("#6495ED");
        //$p1->SetLegend('Wydatki');
        
        //$graph->legend->SetFrameWeight(1);
        
        // Wygenerowanie obrazka
        $gdImgHandler = $graph->Stroke(_IMG_HANDLER);
        
        // Zapis obrazka
        $graph->img->Stream($this->Zend_img_dir.$fName);
        
        return $this->Browser_img_dir.$fName;
    }
    
}