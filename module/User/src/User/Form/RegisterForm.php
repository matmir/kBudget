<?php

namespace User\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Zend\Captcha;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * Register form
 * 
 * @author Mateusz Mirosławski
 *
 */
class RegisterForm extends Form
{
    public function __construct($cfg, $name = null)
    {
        // we want to ignore the name passed
        parent::__construct('register');
        $this->setAttribute('method', 'post');
        
        // Check config parameter
        if (!is_array($cfg)) {
            throw new \Exception('Config parameter must be an array!');
        }
        if (!((isset($cfg['minLoginLength']))&&(isset($cfg['maxLoginLength'])))) {
            throw new \Exception('Missing fields with login!');
        }
        if (!((isset($cfg['minPassLength']))&&(isset($cfg['maxPassLength'])))) {
            throw new \Exception('Missing fields with password!');
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
        
        // Default bank account name
        $this->add(array(
            'type'  => 'Zend\Form\Element\Text',
            'name' => 'bankAccount',
            'options' => array(
                'label' => 'Nazwa konta bankowego: ',
            ),
            'attributes' => array(
                'maxlength' => 30,
            ),
        ));
        
        // e-mail
        $this->add(array(
            'type'  => 'Zend\Form\Element\Email',
            'name' => 'email',
            'options' => array(
                'label' => 'Adres e-mail: ',
            ),
            'attributes' => array(
                'maxlength' => 50,
            ),
        ));
        
        // Pass1
        $this->add(array(
            'type'  => 'Zend\Form\Element\Password',
            'name' => 'pass1',
            'options' => array(
                'label' => 'Hasło: ',
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
                'label' => 'Powtórz hasło: ',
            ),
            'attributes' => array(
                'maxlength' => $cfg['maxPassLength'],
            ),
        ));
        
        // Captcha
        $this->add(array(
            'type'  => 'Zend\Form\Element\Captcha',
            'name' => 'captcha',
            'options' => array(
                'label' => 'Przepisz tekst z obrazka: ',
                'captcha' => new Captcha\Image(array(
                                                'font' => 'public/fonts/FreeSans.ttf',
                                                'fontSize' => 28,
                                                'width' => 200,
                                                'height' => 80,
                                               )),
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
        
        // Submit
        $this->add(array(
            'type'  => 'Zend\Form\Element\Submit',
            'name' => 'submit',
            'attributes' => array(
                'value' => 'Rejestruj',
                'id' => 'submitbutton',
            ),
        ));
    }
}

/**
 * Register form filters
 * 
 * @author Mateusz Mirosławski
 *
 */
class RegisterFormFilter implements InputFilterAwareInterface
{
    protected $inputFilter;
    protected $login_cfg;
    
    /**
     * Constructor
     * 
     * @param array $cfg Array with login/pass length configuration
     * @throws \Exception
     */
    public function __construct($cfg)
    {
        // Check config parameter
        if (!is_array($cfg)) {
            throw new \Exception('Config parameter must be an array!');
        }
        if (!((isset($cfg['minLoginLength']))&&(isset($cfg['maxLoginLength'])))) {
            throw new \Exception('Missing fields with login!');
        }
        if (!((isset($cfg['minPassLength']))&&(isset($cfg['maxPassLength'])))) {
            throw new \Exception('Missing fields with password!');
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
            
            // Account name
            $inputFilter->add($factory->createInput(array(
                'name'     => 'bankAccount',
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
                            'min'      => 2,
                            'max'      => 30,
                        ),
                    ),
                ),
            )));
            
            // e-mail
            $inputFilter->add($factory->createInput(array(
                'name'     => 'email',
                'required' => true,
            )));
            
            // Password 1
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
            
            // Password 2
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
            
            // captcha
            $inputFilter->add($factory->createInput(array(
                'name'     => 'captcha',
                'required' => true,
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
