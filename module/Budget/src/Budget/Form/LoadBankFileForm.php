<?php
/**
    @author Mateusz Mirosławski
    
    Formularz ładowania pliku z wyciągiem na serwer
*/

namespace Budget\Form;

use Zend\Form\Form;
use Zend\Form\Element;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class LoadBankFileForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('bankFile');
        $this->setAttribute('method', 'post');
        
        // Lista banków
        $this->add(array(
            'type'  => 'Zend\Form\Element\Select',
            'name' => 'bank',
            'options' => array(
                'label' => 'Wybierz bank: ',
                'value_options' => array(
                  '0' => 'Bank',
                ),
            ),
        ));
        
        // Formatka na wybór pliku
        $this->add(array(
            'type'  => 'Zend\Form\Element\File',
            'name' => 'upload_file',
            'options' => array(
                'label' => 'Wybierz plik: ',
            ),
            'attributes' => array(
                'multiple' => false,
                //'accept' => 'text/csv',
            ),
        ));
        
        $this->add(array(
            'type'  => 'Zend\Form\Element\Submit',
            'name' => 'submit',
            'attributes' => array(
                'value' => 'Wyślij',
                'id' => 'submitbutton',
            ),
        ));
    }
}

/*
    Filtry dla formularza
*/
class LoadBankFileFormFilter implements InputFilterAwareInterface
{
    protected $inputFilter;
    private $file_cfg;
    
    /**
        Konstruktor
        @param array() Tablica z ustawieniami dla ładowania plików.
    */
    public function __construct($cfg)
    {
        // spr.czy parametr jest tablicą
        if (!is_array($cfg)) {
            throw new \Exception("Parametr z ustawieniami musi być tablicą!");
        }
        // Spr. pól
        if (!(isset($cfg['maxFileSize']))) {
            throw new \Exception("Brak ustawień dla wielkości ładowanego pliku!");
        }
        if (!(isset($cfg['fileExtension']))) {
            throw new \Exception("Brak ustawień dla typu ładowanego pliku!");
        }
        
        $this->file_cfg = $cfg;
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

            // Lista banków
            $inputFilter->add($factory->createInput(array(
                'name'     => 'bank',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
            )));
            
            // Formatka z wyborem pliku
            $inputFilter->add($factory->createInput(array(
                'name'     => 'upload_file',
                'required' => true,
                'validators'  => array(
                    array(
                          'name' => 'FileSize',
                          'options' => array(
                            'max' => $this->file_cfg['maxFileSize'],
                          ),
                    ),
                    array(
                        'name' => 'FileExtension',
                          'options' => array(
                            $this->file_cfg['fileExtension']
                        ),
                    ),
                ),
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
