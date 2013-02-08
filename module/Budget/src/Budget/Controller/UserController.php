<?php
/**
    @author Mateusz Mirosławski
    
    Kontroler usera (logowanie/wylogowanie, rejestracja, edycja)
*/

namespace Budget\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Budget\Model\User;
use Budget\Model\UserMapper;

use Budget\Form\LoginForm;
use Budget\Form\LoginFormFilter;

use Budget\Form\RegisterForm;
use Budget\Form\RegisterFormFilter;

use Budget\Form\PasswordResetForm;
use Budget\Form\PasswordResetFormFilter;

use Zend\Authentication\Adapter\DbTable as AuthAdapter;
use Zend\Authentication\AuthenticationService;

use Zend\Mail;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;

class UserController extends AbstractActionController
{
    protected $userMapper;
    
    // Pobiera mapper do bazy z user-ami
    private function getUserMapper()
    {
        if (!$this->userMapper) {
            $sm = $this->getServiceLocator();
            $this->userMapper = new UserMapper($sm->get('adapter'));
        }
        
        return $this->userMapper;
    }
    
    // Główna strona
    public function indexAction()
    {
    }
    
    // Wylogowanie
    public function logoutAction()
    {
        $auth = new AuthenticationService();
        $auth->clearIdentity();
        
        // Przekierowanie do głownej strony
        return $this->redirect()->toRoute('main');
    }
    
    // Logowanie
    public function loginAction()
    {
        // Ustawienia długości loginu/hasła
        $cfg = $this->getServiceLocator()->get('user_login_cfg');
        
        // Formularz
        $form = new LoginForm($cfg);
        // Filtry
        $formFilters = new LoginFormFilter($cfg);
        
        // Flaga błędu logowania
        $ERR = 0;

        $request = $this->getRequest();
        if ($request->isPost()) {
            
            // Filtry
            $form->setInputFilter($formFilters->getInputFilter());
            // Uzupełnienie formularza
            $form->setData($request->getPost());
            
            // Spr. poprawności
            if ($form->isValid()) {
                
                $sm = $this->getServiceLocator();
                // Dostęp do bazy danych
                $dbAdapter = $sm->get('adapter');
                
                $authAdapter = new AuthAdapter($dbAdapter,
                               'users',
                               'login',
                               'pass',
                               //'MD5(CONCAT(?, passs)) AND active = 1' // DOPISAĆ SOLENIE!!!
                               'MD5(?) AND active = 1'
                               );
                
                $authAdapter->setIdentity($form->get('login')->getValue())
                            ->setCredential($form->get('pass')->getValue());
                
                $auth = new AuthenticationService();
                $result = $auth->authenticate($authAdapter);
                
                // Autoryzacja pozytywna
                if ($result->isValid()) {
                    
                    $storage = $auth->getStorage();
                    $storage->write($authAdapter->getResultRowObject(array(
                            'uid',
                            'login',
                            'u_type',
                        )));
                    
                    // Data logowania
                    $this->getUserMapper()->setUserLoginDate($storage->read()->uid);
                    
                    // Przekierowanie do listy transakcji
                    return $this->redirect()->toRoute('transaction', array(
                                                                           'month' => (int)date('m'),
                                                                           'year' => (int)date('Y'),
                                                                           'page' => 1,
                                                                           ));
                } else { // Błąd w logowaniu
                    $ERR = 1;
                }
            }
        }
        
        return array(
            'form' => $form,
            'ERR' => $ERR,
        );
    }
    
    // Rejestracja
    public function registerAction()
    {
        // Ustawienia długości loginu/hasła
        $cfg = $this->getServiceLocator()->get('user_login_cfg');
        
        // Wylogowanie zalogowanego usera
        $auth = new AuthenticationService();
        $auth->clearIdentity();
        
        // Formularz
        $form = new RegisterForm($cfg);
        // Filtry
        $formFilters = new RegisterFormFilter($cfg);
        
        // Flaga błędu (1 - login istnieje, 2 - e-mail istnieje, 3 - hasła się nie zgadzają)
        $ERR = 0;
        // Flaga potwierdzenia rejestracji (0 - niezarejestrowany, 1 - zarejestrowany)
        $CONFIRM = 0;

        $request = $this->getRequest();
        if ($request->isPost()) {
            
            // Filtry
            $form->setInputFilter($formFilters->getInputFilter());
            // Uzupełnienie formularza
            $form->setData($request->getPost());
            
            // Spr. poprawności formularza
            if ($form->isValid()) {
                
                // Spr. czy podano nieistniejący login
                $u_login = (string)$form->get('login')->getValue();
                if ($this->getUserMapper()->isUserLoginExists($u_login)==0) {
                    
                    // Spr. czy podano e-mail, którego nie ma w bazie
                    $u_email = (string)$form->get('email')->getValue();
                    if ($this->getUserMapper()->isEmailExists($u_email)==0) {
                        
                        // Spr. poprawności wpisanych haseł
                        $p1 = (string)$form->get('pass1')->getValue();
                        $p2 = (string)$form->get('pass2')->getValue();
                        if ($p1 == $p2) {
                            
                            // Model usera
                            $user = new User();
                            // Uzupełnienie modelu danymi
                            $user->login = $u_login;
                            $user->email = $u_email;
                            $user->pass = $p1;
                            
                            // Dodanie usera do bazy
                            $this->getUserMapper()->addUser($user);
                            
                            // Potwierdzenie rejestracji
                            $CONFIRM = 1;
                            
                        } else { // Niepoprawne hasła
                            $ERR = 3;
                        }
                        
                    } else { // Podany email jest w bazie
                        $ERR = 2;
                    }
                    
                } else { // Podany login istnieje
                    $ERR = 1;
                }
                
            }
        }
        
        return array(
            'form' => $form,
            'ERR' => $ERR,
            'CONFIRM' => $CONFIRM,
        );
    }
    
    // Reset hasła
    public function passrstAction()
    {
        // Ustawienia e-maila
        $cfg = $this->getServiceLocator()->get('email_cfg');
        
        // Wylogowanie zalogowanego usera
        $auth = new AuthenticationService();
        $auth->clearIdentity();
        
        // Formularz
        $form = new PasswordResetForm();
        // Filtry
        $formFilters = new PasswordResetFormFilter();
        
        // Flaga błędu (1 - login istnieje, 2 - e-mail istnieje, 3 - hasła się nie zgadzają)
        $ERR = 0;
        // Flaga potwierdzenia rejestracji (0 - niezarejestrowany, 1 - zarejestrowany)
        $CONFIRM = 0;

        $request = $this->getRequest();
        if ($request->isPost()) {
            
            // Filtry
            $form->setInputFilter($formFilters->getInputFilter());
            // Uzupełnienie formularza
            $form->setData($request->getPost());
            
            // Spr. poprawności formularza
            if ($form->isValid()) {
                
                // Spr. czy podano istniejący e-mail
                $u_email = (string)$form->get('email')->getValue();
                $uid = $this->getUserMapper()->isEmailExists($u_email);
                if ($uid) {
                    
                    // Pobranie danych usera
                    $user = $this->getUserMapper()->getUser($uid);
                    // Generacja nowego hasła
                    $new_pass = $user->genNewPass();
                    // Zmiana hasła w bazie
                    $this->getUserMapper()->changeUserPass($uid, $new_pass);
                    
                    // Wysłanie maila
                    $mail = new Mail\Message();
                    $mail->setBody('Twój login: '.$user->login."\nTwoje hasło: ".$new_pass);
                    $mail->setFrom($cfg['FromAddr'], $cfg['FromName']);
                    $mail->addTo($user->email, $user->login);
                    $mail->setSubject('Reset hasła');
                    
                    $transport = new SmtpTransport();
                    $options   = new SmtpOptions(array(
                        'name'              => 'localhost',
                        'host'              => $cfg['host'],
                        'connection_class'  => 'login',
                        'connection_config' => array(
                            'username' => $cfg['login'],
                            'password' => $cfg['pass'],
                        ),
                    ));
                    $transport->setOptions($options);
                    $transport->send($mail);
                    
                    $CONFIRM = 1;
                    
                } else { // E-mail nie istnieje
                    $ERR = 1;
                }
                
            }
        }
        
        return array(
            'form' => $form,
            'ERR' => $ERR,
            'CONFIRM' => $CONFIRM,
        );
    }

}