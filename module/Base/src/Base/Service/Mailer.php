<?php 

namespace Base\Service;

use Base\Service\BaseService;
use Zend\Mail;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;

/**
 * Mailer service
 * 
 * @author Mateusz Mirosławski
 *
 */
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
        $this->emailConfig = $this->getServiceLocator()->get('email_cfg');
        
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
    private function checkMessageStructure($msg)
    {
        if (!is_array($msg)) {
            throw new \Exception('Message must be an aray!');
        }
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