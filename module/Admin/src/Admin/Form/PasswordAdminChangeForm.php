<?php
/**
 *  Password change form
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

namespace Admin\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class PasswordAdminChangeForm extends Form
{
    public function __construct($cfg, $name = null)
    {
        // we want to ignore the name passed
        parent::__construct('password-admin');
        $this->setAttribute('method', 'post');
        
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
        
        // Submit
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
 * Pasword change form filters
 * 
 * @author Mateusz Mirosławski
 * 
 */
class PasswordAdminChangeFormFilter implements InputFilterAwareInterface
{
    protected $inputFilter;
    protected $login_cfg;
    
    /**
     * Constructor
     * 
     * @param array $cfg Array with login/password configuration
     */
    public function __construct(array $cfg)
    {
        // Check array fields
        if (!((isset($cfg['minPassLength']))&&(isset($cfg['maxPassLength'])))) {
            throw new \Exception('Missing fields with password configuration!');
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

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}
