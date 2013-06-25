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
 * Password change form
 * 
 * @author Mateusz Mirosławski
 *
 */
class PasswordChangeForm extends Form
{
    /**
     * Constructor
     * 
     * @param array $cfg Array with user login/password configuration
     */
    public function __construct(array $cfg)
    {
        // we want to ignore the name passed
        parent::__construct('password');
        $this->setAttribute('method', 'post');
        
        // Check fileds with the password
        if (!((isset($cfg['minPassLength']))&&(isset($cfg['maxPassLength'])))) {
            throw new \Exception('Missing configuration with user password!');
        }

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

/**
 * Password change form filters
 * 
 * @author Mateusz Mirosławski
 *
 */
class PasswordChangeFormFilter implements InputFilterAwareInterface
{
    protected $inputFilter;
    protected $login_cfg;
    
    /**
     * Constructor
     * 
     * @param array $cfg Array with user login/password configuration
     */
    public function __construct(array $cfg)
    {
        // Check fileds with the password
        if (!((isset($cfg['minPassLength']))&&(isset($cfg['maxPassLength'])))) {
            throw new \Exception('Missing configuration with user password!');
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

            // Old password
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
            
            // New pass 1
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
            
            // New pass 2
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
