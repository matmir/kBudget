<?php
/**
 *  Bank CSV parser service
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

namespace Budget\Service;

use Base\Service\BaseService;
use Budget\Model\Banking\Bank;
use Budget\Model\Banking\mBank;

class BankService extends BaseService
{
    /**
     * List of supported banks configuration:
     * 'uniqueBankID' => array(
     *     'title' => 'Bank name',
     *     'class' => 'Bank class name'
     * )
     * 
     * @var array
     */
    private $supportedBanks;
    
    /**
     * Initialize supported bank list from config file
     */
    private function initSupportedBankList()
    {
        if ($this->supportedBanks === null) {
        
            $config = $this->getServiceLocator()->get('Configuration');
        
            $this->supportedBanks = $config['supportedBanks'];
        
        }
    }
    
    /**
     * Returns a list of supported banks:
     * array(
     *     'bankID' => 'bank title',
     * )
     * 
     * @return array
     */
    public function getBankList()
    {
        // Initialize bank list
        $this->initSupportedBankList();
        
        $returnArray = array();
        
        foreach ($this->supportedBanks as $bankID => $property) {
            
            $returnArray[$bankID] = $property['title'];
            
        }
        
        return $returnArray;
    }
    
    /**
     * Check bank identifier
     * 
     * @param string $bankID Bank identifier
     * @throws \Exception
     */
    private function checkBankID($bankID)
    {
        // Check if there is given bank identifier
        if (!array_key_exists($bankID, $this->supportedBanks)) {
            throw new \Exception('Bank ID does not exist!');
        }
    }
    
    /**
     * Get bank instance
     * 
     * @param string $bankID Bank identifier
     * @param string $file CSV file name
     * @param int $pos Line position in the CSV file
     * @param int $max_lines Number of lines processed once
     * @return Bank
     */
    public function getBankInstance($bankID, $file, $pos, $max_lines) {
        
        // Initialize bank list
        $this->initSupportedBankList();
        
        // Check if given bank id exist
        $this->checkBankID($bankID);
        
        // Check if given bank class name exist
        $bankClass = $this->supportedBanks[$bankID]['class'];
        if (!class_exists($bankClass)) {
            throw new \Exception('Class: '.$bankClass.' does not exist!');
        }
        
        return new $bankClass($file, $pos, $max_lines);
    }
}
