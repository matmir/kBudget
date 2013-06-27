<?php

namespace Budget\Model\Banking;

use Budget\Model\Banking\Bank;
use Budget\Model\Banking\Exception\EndBankFile;
use Budget\Model\Banking\Exception\ParseBankFileError;
use Budget\Model\Transaction;

/**
 * Class importing transactions from mBank CSV file
 * 
 * @author Mateusz Mirosławski
 *
 */
class mBank extends Bank
{
    /**
     * Pattern of the header with the data in the CSV file
     * 
     * @var string
     */
    const HEADER = "#Data operacji;#Data księgowania;#Opis operacji;#Tytuł;#Nadawca/Odbiorca;#Numer konta;#Kwota;#Saldo po operacji;";
    
    /**
     *  Constructor
     *
     * @param string $file CSV file name
     * @param int $pos Line position in the CSV file
     * @param int $max_lines Number of lines processed once
     * @throws \Exception
     */
    public function __construct($file, $pos, $max_lines)
    {
        parent::__construct($file, $pos, $max_lines);
    }
    
    /**
     * Parsing data from the given CSV file.
     * Return array of the Transaction objects.
     *
     * @return array
     */
    public function parseData()
    {
        // Return array
        $returnArray = array();
        
        // Check file position
        if ($this->getPos() == 0) {
            // Position of the header with the data +1
            $hpos = $this->findDataHeaderPos()+1;
            // Set position on the first data to import
            $this->setPos($hpos);
        }
        
        // Parsed line count
        $pl = 0;
        
        // Move the line by line starting from the position of the first data to import
        while (true) {
            
            // Get line from the CSV file
            $line = $this->convert(trim($this->getLine()));
            
            // Check if there is empty line
            if ($line == '') {
                // End of the data
                break;
            }
            
            // Get data from the line
            $data = explode(';', $line);
            
            // Check correction of the data
            if (count($data) != 9) {
                throw new ParseBankFileError();
            }
            
            // Insert data into the Transaction object
            $tr = new Transaction();
            $tr->setDate(new \DateTime($data[0]));
            $value = str_replace(array(',',' '),array('.',''),$data[6]);
            $tr->setTransactionType(($value<0)?(Transaction::EXPENSE):(Transaction::PROFIT));
            $tr->setValue($value);
            $tr->setContent(str_replace('"', '', $data[3]));
            
            // Insert into the return array
            array_push($returnArray, $tr);
            
            // Increment parsed line count
            $pl++;
            
            // Check parsed line count
            if ($pl == ($this->max_parse_lines)) {
                // Stop the loop
                break;
            }
        }
        
        return $returnArray;
    }
    
    /**
     * Returns the number of all transactions in the file
     * 
     * @return int
     */
    public function count()
    {
        // Get actual line position
        $old_pos = $this->getPos();
        
        // Set line position at the top of the file
        $this->setPos(0);
        
        // Find header with the transactions data
        $hpos = $this->findDataHeaderPos();
        
        // Set position on the first transaction data
        $this->setPos($hpos+1);
        
        // Number of the transaction in the CSV file
        $tr_count = 0;
        
        // Counting loop
        while (true) {
        
            try {
                // Get the line
                $line = $this->convert($this->getLine());
            } catch (EndBankFile $e) {
                // Stop the loop
                break;
            }
            
            // Check if there is empty line
            if ($line == '') {
                // Stop the loop
                break;
            }
        
            // Increment number of the transactions
            $tr_count += 1;
        
        }
        
        // Set the file position on the old value
        $this->setPos($old_pos);
        
        return $tr_count;
    }
    
    /**
     * Convert line on utf-8 chars
     * 
     * @param string $line Line with the chars
     * @return string
     */
    private function convert($line)
    {
        $fc = iconv('windows-1250', 'utf-8', $line);
        
        return trim($fc);
    }
    
    /**
     * Find the line number with the header
     * 
     * @return number
     */
    private function findDataHeaderPos()
    {
        // Get actual line position
        $old_pos = $this->getPos();
        
        // Set line position at the top of the file
        $this->setPos(0);
        
        // Position of the header
        $hpos = 0;
        
        // Finding the pattern of the header
        while (true) {
        
            try {
                // Get the line
                $line = $this->convert($this->getLine());
            } catch (EndBankFile $e) {
                // Stop the loop
                break;
            }
            
            // Check if there is header
            if ($line == self::HEADER) {
                // Stop the loop
                break;
            }
        
            // Increment header position
            $hpos += 1;
        
        }
        
        // Set the file position on the old value
        $this->setPos($old_pos);
        
        return $hpos;
    }
    
}
