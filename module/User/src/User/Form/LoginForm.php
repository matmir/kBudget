<?php
/**
    @author Mateusz Mirosławski
    
    Formularz logowania.
*/

namespace User\Form;

use Zend\Form\Form;
use Zend\Form\Element;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class LoginForm extends Form
{
    public function __construct($cfg, $name = null)
    {
        // we want to ignore the name passed
        parent::__construct('login');
        $this->setAttribute('method', 'post');
        
        // spr.czy parametr jest tablicą
        if (!is_array($cfg)) {
            throw new \Exception("Parametr z ustawieniami musi być tablicą!");
        }
        // Spr. pól z loginem
        if (!((isset($cfg['minLoginLength']))&&(isset($cfg['maxLoginLength'])))) {
            throw new \Exception("Brak ustawień dla pól z logowaniem!");
        }
        // Spr. pól z hasłem
        if (!((isset($cfg['minPassLength']))&&(isset($cfg['maxPassLength'])))) {
            throw new \Exception("Brak ustawień dla pól z hasłem!");
        }
        
        // Login
        $this->add(array(
            'type'  => 'Zend\Form\Element\Text',
            'name' => 'login',
            'options' => array(
                'label' => 'Login: ',
            ),
            'attributes' => array(
                'maxlength' => $cfg['maxLoginLength'],
            ),
        ));
        
        // Pass
        $this->add(array(
            'type'  => 'Zend\Form\Element\Password',
            'name' => 'pass',
            'options' => array(
                'label' => 'Hasło: ',
            ),
            'attributes' => array(
                'maxlength' => $cfg['maxPassLength'],
            ),
        ));
        
        // CSRF
        $this->add(array(
            'type'  => 'Zend\Form\Element\Csrf',
            'name' => 'csrf',
            'options' => array(
                'csrf_options' => array('timeout' => 120),
            ),
        ));
        
        // Knefel
        $this->add(array(
            'type'  => 'Zend\Form\Element\Submit',
            'name' => 'submit',
            'attributes' => array(
                'value' => 'Zatwierdź',
                'id' => 'submitbutton',
            ),
        ));
    }
}

/*
    Filtry dla formularza
*/
class LoginFormFilter implements InputFilterAwareInterface
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
        // Spr. pól z loginem
        if (!((isset($cfg['minLoginLength']))&&(isset($cfg['maxLoginLength'])))) {
            throw new \Exception("Brak ustawień dla pól z logowaniem!");
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

            // Login
            $inputFilter->add($factory->createInput(array(
                'name'     => 'login',
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
                            'min'      => $this->login_cfg['minLoginLength'],
                            'max'      => $this->login_cfg['maxLoginLength'],
                        ),
                    ),
                ),
            )));
            
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
            
            // CSRF
            $inputFilter->add($factory->createInput(array(
                'name'     => 'csrf',
                'required' => true,
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
