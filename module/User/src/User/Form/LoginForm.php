<?php
/**
 *  Login form
 *  Copyright (C) 2013 Mateusz Mirosławski
 * 
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 * 
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 * 
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
    /**
     * Constructor
     * 
     * @param array $cfg Array with user login/password configuration
     */
    public function __construct(array $cfg)
    {
        // we want to ignore the name passed
        parent::__construct('login');
        $this->setAttribute('method', 'post');

        // Check fields with login
        if (!((isset($cfg['minLoginLength']))&&(isset($cfg['maxLoginLength'])))) {
            throw new \Exception('Missing configuration with user login!');
        }
        // Check fileds with the password
        if (!((isset($cfg['minPassLength']))&&(isset($cfg['maxPassLength'])))) {
            throw new \Exception('Missing configuration with user password!');
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

/**
 * Login form filters
 * 
 * @author Mateusz Mirosławski
 *
 */
class LoginFormFilter implements InputFilterAwareInterface
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
        // Check fields with login
        if (!((isset($cfg['minLoginLength']))&&(isset($cfg['maxLoginLength'])))) {
            throw new \Exception('Missing configuration with user login!');
        }
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
            
            // Password
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
