<?php 
/**
 *  Mailer service
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

namespace Base\Service;

use Base\Service\BaseService;
use Zend\Mail;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;

class Mailer extends BaseService
{
    /**
     * E-mail configuration
     * 
     * @var array
     */
    protected $emailConfig;
    
    /**
     * SMTP transport object
     * 
     * @var SmtpTransport
     */
    protected $smtpTransport;
    
    /**
     * Initialize the SMTP object
     */
    private function initSMTP()
    {
        // Get e-mail server configuration
        $this->emailConfig = $this->getServiceLocator()->get('emailConfig');
        
        // Init SMTP object
        $this->smtpTransport = new SmtpTransport();
        $options   = new SmtpOptions(array(
            'name' => 'localhost',
            'host' => $this->emailConfig['host'],
            'connection_class' => 'login',
            'connection_config' => array(
                'username' => $this->emailConfig['login'],
                'password' => $this->emailConfig['pass'],
            ),
        ));
        
        $this->smtpTransport->setOptions($options);
    }
    
    /**
     * Check given message structure
     * 
     * @param array $msg
     * @throws \Exception
     */
    private function checkMessageStructure(array $msg)
    {
        if (!array_key_exists('toAddress', $msg)) {
            throw new \Exception('Message must contain recipient address (key - "toAddress")!');
        }
        if (!array_key_exists('toName', $msg)) {
            throw new \Exception('Message must contain recipient name (key - "toName")!');
        }
        if (!array_key_exists('subject', $msg)) {
            throw new \Exception('Message must contain subject (key - "subject")!');
        }
        if (!array_key_exists('body', $msg)) {
            throw new \Exception('Message must contain content (key - "body")!');
        }
    }
    
    /**
     * Send e-mail message
     * 
     * @param array $msg Message
     */
    public function send($msg)
    {
        // Check the given message
        $this->checkMessageStructure($msg);
        
        // Check if there is SMTP object
        if (!($this->smtpTransport instanceof SmtpTransport)) {
            $this->initSMTP();
        }
        
        // Send e-mail
        $mail = new Mail\Message();
        $mail->setBody($msg['body']);
        $mail->setFrom($this->emailConfig['FromAddr'], $this->emailConfig['FromName']);
        $mail->addTo($msg['toAddress'], $msg['toName']);
        $mail->setSubject($msg['subject']);
        
        $this->smtpTransport->send($mail);
    }
}
