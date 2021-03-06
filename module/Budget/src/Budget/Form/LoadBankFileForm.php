<?php
/**
 *  Form to load CSV file
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
        
        // Bank account identifier into which imports transactions
        $this->add(array(
            'type'  => 'Zend\Form\Element\Select',
            'name' => 'aid',
            'options' => array(
                'label' => 'Konto do którego importować: ',
                'value_options' => array(
                    '0' => '...',
                ),
            ),
        ));
        
        // List of supported banks
        $this->add(array(
            'type'  => 'Zend\Form\Element\Select',
            'name' => 'bank',
            'options' => array(
                'label' => 'Nazwa banku z którego pochodzi plik CSV: ',
                'value_options' => array(
                  '0' => 'Bank',
                ),
            ),
        ));
        
        // File
        $this->add(array(
            'type'  => 'Zend\Form\Element\File',
            'name' => 'upload_file',
            'options' => array(
                'label' => 'Plik z wyciągiem: ',
            ),
            'attributes' => array(
                'multiple' => false,
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

/**
 * Upload filters
 * 
 * @author Mateusz Mirosławski
 *
 */
class LoadBankFileFormFilter implements InputFilterAwareInterface
{
    protected $inputFilter;
    private $file_cfg;
    
    /**
     * Constructor
     * 
     * @param array $cfg Upload configuration array
     * @throws \Exception
     */
    public function __construct(array $cfg)
    {
        // Check fields
        if (!(isset($cfg['maxFileSize']))) {
            throw new \Exception('Missing file weight configuration!');
        }
        if (!(isset($cfg['fileExtension']))) {
            throw new \Exception('Missing file extension configuration!');
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

            // Bank account identifier into which imports transactions
            $inputFilter->add($factory->createInput(array(
                'name' => 'aid',
                'required' => true,
                'filters' => array(
                    array('name' => 'Int'),
                ),
            )));
            
            // Supported bank list
            $inputFilter->add($factory->createInput(array(
                'name'     => 'bank',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
            )));
            
            // File
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
