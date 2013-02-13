<?php
/**
    @author Mateusz Mirosławski
    
    Formularz edycji importowanych transakcji.
*/

namespace Budget\Form;

use Zend\Form\Form;
use Zend\Form\Element;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class TransactionImportForm extends Form
{
    private $fCount;
    
    /**
        Konstruktor
        @param int $forms_count Liczba generowanych formatek
    */
    public function __construct($forms_count, $name = null)
    {
        // Spr. liczby generowanych formatek
        if ($forms_count<1) {
            throw new \Exception('Błędna liczba generowanych formatek!');
        }
        
        $this->fCount = (int)$forms_count;
        
        // we want to ignore the name passed
        parent::__construct('transactionImportForm');
        $this->setAttribute('method', 'post');
        
        // Generacja odpowiedniej liczby formatek
        for ($i=0; $i<$this->fCount; $i++) {
            
            // Typ transakcji
            $this->add(array(
                'type'  => 'Zend\Form\Element\Hidden',
                'name' => 't_type'.$i,
            ));
            
            // Lista kategorii
            $this->add(array(
                'type'  => 'Zend\Form\Element\Select',
                'name' => 'cid'.$i,
                'options' => array(
                    //'label' => 'Kategoria: ',
                    'value_options' => array(
                      '0' => 'Jedzenie',
                    ),
                ),
            ));
            
            // Nowa kategoria
            $this->add(array(
                'type'  => 'Zend\Form\Element\Text',
                'name' => 'c_name'.$i,
                /*'options' => array(
                    'label' => 'Nowa kategoria: ',
                ),*/
                'attributes' => array(
                    'maxlength' => 100,
                    'size' => 9,
                ),
            ));
            
            // Data
            $this->add(array(
                'type'  => 'Zend\Form\Element\Date',
                'name' => 't_date'.$i,
                /*'options' => array(
                    'label' => 'Data: ',
                ),*/
                'attributes' => array(
                    'step' => '1',
                ),
            ));
            
            // Opis
            $this->add(array(
                'type'  => 'Zend\Form\Element\Text',
                'name' => 't_content'.$i,
                /*'options' => array(
                    'label' => 'Opis: ',
                ),*/
                'attributes' => array(
                    'maxlength' => 400,
                ),
            ));
            
            // Wartość
            $this->add(array(
                'type'  => 'Zend\Form\Element\Text',
                'name' => 't_value'.$i,
                /*'options' => array(
                    'label' => 'Wartość: ',
                ),*/
                'attributes' => array(
                    'maxlength' => 12,
                    'size' => 8,
                ),
            ));
            
            // Ignorowanie wpisu
            $this->add(array(
                'type'  => 'Zend\Form\Element\Checkbox',
                'name' => 'ignore'.$i,
            ));
            
        }
        
        // Przycisk zatwierdzenia
        $this->add(array(
            'type'  => 'Zend\Form\Element\Submit',
            'name' => 'submit',
            'attributes' => array(
                'value' => 'Importuj',
                'id' => 'submitbutton',
            ),
        ));
    }
}

/*
    Filtry dla formularza
*/
class TransactionImportFormFilter implements InputFilterAwareInterface
{
    protected $inputFilter;
    private $fCount;
    private $n_c_required; // Czy wymagane pole od nowej kategorii
    
    /**
        Konstruktor
        @param int $forms_count Liczba generowanych formatek (validatorów)
        @param array() $new_category_required Tablica z flagami wymagania pola z nową kategorią
    */
    public function __construct($forms_count, $new_category_required)
    {
        // Spr. liczby generowanych formatek
        if ($forms_count<1) {
            throw new \Exception('Błędna liczba generowanych formatek!');
        }
        
        // Spr. tablicy z flagami
        if (!is_array($new_category_required)) {
            throw new \Exception('Parametr z flagą wymagania nowej kategorii musi być tablicą!');
        }
        
        $this->fCount = (int)$forms_count;
        $this->n_c_required = $new_category_required;
    }
    
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();
            
            // Generacja odpowiedniej liczby validatorów dla formatek
            for ($i=0; $i<$this->fCount; $i++) {
                
                // Typ transakcji
                $inputFilter->add($factory->createInput(array(
                    'name'     => 't_type'.$i,
                    'required' => true,
                    'filters'  => array(
                        array('name' => 'Int'),
                    ),
                )));
    
                // Lista kategorii
                $inputFilter->add($factory->createInput(array(
                    'name'     => 'cid'.$i,
                    'required' => true,
                    'filters'  => array(
                        array('name' => 'Int'),
                    ),
                )));
                
                // Nowa kategoria
                $inputFilter->add($factory->createInput(array(
                    'name'     => 'c_name'.$i,
                    'required' => $this->n_c_required[$i],
                    'filters'  => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        array(
                            'name'    => 'StringLength',
                            'options' => array(
                                'encoding' => 'UTF-8',
                                'min'      => 1,
                                'max'      => 100,
                            ),
                        ),
                    ),
                )));
                
                // Data
                $inputFilter->add($factory->createInput(array(
                    'name'     => 't_date'.$i,
                    'required' => true,
                    'validators'  => array(
                        array(
                              'name' => 'Between',
                              'options' => array(
                                'min' => '1970-01-01',
                                'max' => date('Y-m-d'),
                              ),
                        ),
                    ),
                )));
                
                // Opis
                $inputFilter->add($factory->createInput(array(
                    'name'     => 't_content'.$i,
                    'required' => true,
                    'filters'  => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        array(
                            'name'    => 'StringLength',
                            'options' => array(
                                'encoding' => 'UTF-8',
                                'min'      => 1,
                                'max'      => 200,
                            ),
                        ),
                    ),
                )));
                
                // Wartość
                $inputFilter->add($factory->createInput(array(
                    'name'     => 't_value'.$i,
                    'required' => true,
                    'filters'  => array(
                        array(
                              'name' => 'PregReplace',
                              'options' => array(
                                'pattern'     => '/,/',
                                'replacement' => '.',
                            ),
                        ),
                    ),
                )));
                
            }
            
            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
