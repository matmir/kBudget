<?php

namespace Budget\Model\Banking;

use Budget\Model\Banking\Exception\EndBankFile;

/**
 * Base abstract bank class for parsing CSV files
 * 
 * @author Mateusz Mirosławski
 *
 */
abstract class Bank
{
    /**
     * CSV file name
     * 
     * @var string
     */
    private $fname;
    
    /**
     * Actual line position in file
     * 
     * @var int
     */
    private $fpos;
    
    /**
     * Handle for the CSV file
     * 
     * @var resource
     */
    private $file_handle;
    
    /**
     * Number of lines processed once
     * 
     * @var int
     */
    protected $max_parse_lines;
    
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
        // Check number of parsing lines
        if ($max_lines < 2) {
            throw new \Exception('Number of processed lines must be greater than 2!');
        }
        
        // Check if the given file exists
        if (!(file_exists($file))) {
            throw new \Exception('File does not exist!');
        }
        
        // Check given line position
        if ($pos < 0) {
            throw new \Exception('Line number must be a positive number!');
        }
        
        $this->fname = (string)$file;
        
        // Open the file
        $this->file_handle = fopen($this->fname, 'r');
        
        if (!$this->file_handle) {
            throw new \Exception('Can not open the file!');
        }
        
        // Set line position
        $this->setPos((int)$pos);
        
        $this->max_parse_lines = (int)$max_lines;
    }
    
    /**
     * Destructor
     */
    public function __destruct() {
        
        if (is_resource($this->file_handle)) {
            
            // Close the file
            fclose($this->file_handle);
            
        }
        
    }
    
    /**
     * Parsing data for the given Bank.
     * Return array of the Transaction objects.
     * (must be overridden in a derived class)
     * 
     * @return array
     */
    public abstract function parseData();
    
    /**
     * Returns the number of all transactions in the file
     * (must be overridden in a derived class)
     * 
     * @return int
     */
    public abstract function count();
    
    /**
     * Set line position in the CSV file
     * 
     * @param int $pos Line number
     * @throws \Exception
     */
    protected function setPos($pos)
    {
        if ($pos < 0) {
            throw new \Exception('Line number must be a positive number!');
        }
        
        // Copy line number
        $this->fpos = (int)$pos;
        
        // We point to the beginning of the file
        fseek($this->file_handle, 0);
        
        // Move the line by line, until we reach the setpoint
        $line = 0;
        while (($buff = fgets($this->file_handle)) !== false) {
            
            // Check if the actual line is the setpoint
            if ($line == $this->fpos) {
                // Stop the loop
                break;
            }
            
            $line++;
            
        }
    }
    
    /**
     * Get line from CSV file
     * 
     * @return string
     */
    protected function getLine()
    {
        if (($line = fgets($this->file_handle)) !== false) {
            
            // Increase line number pointer
            $this->fpos += 1;
            
            return $line;
            
        } else { // End of the file
            
            throw new EndBankFile();
        }
    }
    
    /**
     * Get atual line position
     * 
     * @return int
     */
    public function getPos()
    {
        return $this->fpos;
    }
}