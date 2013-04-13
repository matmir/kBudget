<?php
/**
    @author Mateusz Mirosławski
    
    Formularz zmiany hasła usera.
*/

namespace User\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Zend\Captcha;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class PasswordChangeForm extends Form
{
    public function __construct($cfg, $name = null)
    {
        // we want to ignore the name passed
        parent::__construct('password');
        $this->setAttribute('method', 'post');
        
        // Pass
        $this->add(array(
            'type'  => 'Zend\Form\Element\Password',
            'name' => 'pass',
            'options' => array(
                'label' => 'Aktualne hasło: ',
            ),
            'attributes' => array(
                'maxlength' => $cfg['maxPassLength'],
            ),
        ));
        
        // Pass1
        $this->add(array(
            'type'  => 'Zend\Form\Element\Password',
            'name' => 'pass1',
            'options' => array(
                'label' => 'Nowe hasło: ',
            ),
            'attributes' => array(
                'maxlength' => $cfg['maxPassLength'],
            ),
        ));
        
        // Pass2
        $this->add(array(
            'type'  => 'Zend\Form\Element\Password',
            'name' => 'pass2',
            'options' => array(
                'label' => 'Powtórz nowe hasło: ',
            ),
            'attributes' => array(
                'maxlength' => $cfg['maxPassLength'],
            ),
        ));
        
        // Knefel
        $this->add(array(
            'type'  => 'Zend\Form\Element\Submit',
            'name' => 'submit',
            'attributes' => array(
                'value' => 'Zmień',
                'id' => 'submitbutton',
            ),
        ));
    }
}

/*
    Filtry dla formularza
*/
class PasswordChangeFormFilter implements InputFilterAwareInterface
{
    protected $inputFilter;
    protected $login_cfg; // konfiguracja długości loginu/hasła
    
    /**
        Konstruktor
        @param array() Tablica z konfiguracją długości loginu/hasła
    */
    public function __construct($cfg)
    {
        // spr.czy parametr jest tablicą
        if (!is_array($cfg)) {
            throw new \Exception("Parametr z ustawieniami musi być tablicą!");
        }
        // Spr. pól z hasłem
        if (!((isset($cfg['minPassLength']))&&(isset($cfg['maxPassLength'])))) {
            throw new \Exception("Brak ustawień dla pól z hasłem!");
        }
        
        $this->login_cfg = $cfg;
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

            // Hasło
            $inputFilter->add($factory->createInput(array(
                'name'     => 'pass',
                'required' => true,
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => $this->login_cfg['minPassLength'],
                            'max'      => $this->login_cfg['maxPassLength'],
                        ),
                    ),
                ),
            )));
            
            // Hasło 1
            $inputFilter->add($factory->createInput(array(
                'name'     => 'pass1',
                'required' => true,
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => $this->login_cfg['minPassLength'],
                            'max'      => $this->login_cfg['maxPassLength'],
                        ),
                    ),
                ),
            )));
            
            // Hasło 2
            $inputFilter->add($factory->createInput(array(
                'name'     => 'pass2',
                'required' => true,
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => $this->login_cfg['minPassLength'],
                            'max'      => $this->login_cfg['maxPassLength'],
                        ),
                    ),
                ),
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
