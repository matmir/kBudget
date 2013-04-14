<?php
/**
    @author Mateusz Mirosławski
    
    Kontroler usera (logowanie/wylogowanie, rejestracja, edycja)
*/

namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use User\Model\User;
use User\Model\UserMapper;

use User\Form\LoginForm;
use User\Form\LoginFormFilter;

use User\Form\RegisterForm;
use User\Form\RegisterFormFilter;

use User\Form\PasswordResetForm;
use User\Form\PasswordResetFormFilter;

use User\Form\PasswordChangeForm;
use User\Form\PasswordChangeFormFilter;

use User\Form\EmailForm;
use User\Form\EmailFormFilter;

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
        $this->getServiceLocator()->get('Auth\UserAuthentication')->clearIdentity();
        
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
                
                // Authentication service
                $userAuth = $this->getServiceLocator()->get('Auth\UserAuthentication');
                
                // Autoryzacja pozytywna
                if ($userAuth->authenticate($form->get('login')->getValue(), $form->get('pass')->getValue())) {
                    
                    // User id
                    $uid = $this->getServiceLocator()->get('userId');
                    
                    // Data logowania
                    $this->getUserMapper()->setUserLoginDate($uid);
                    
                    // Przekierowanie do listy transakcji
                    return $this->redirect()->toRoute('transactions', array(
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
        $this->getServiceLocator()->get('Auth\UserAuthentication')->clearIdentity();
        
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
        $this->getServiceLocator()->get('Auth\UserAuthentication')->clearIdentity();
        
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
    
    // Zmiana emaila
    public function emailAction()
    {
        // Identyfikator zalogowanego usera
        $uid = $this->getServiceLocator()->get('userId');
    
        // Pobranie danych usera
        $user = $this->getUserMapper()->getUser($uid);
    
        // Aktualny e-mail
        $actual_email = $user->email;
    
        // Formularz
        $form = new EmailForm();
        // Filtry
        $formFilters = new EmailFormFilter();
    
        // Flaga błędu (1 - podany e-mail istnieje, 2 - wszystko ok)
        $ERR = 0;
    
        $request = $this->getRequest();
        if ($request->isPost()) {
    
            $form->setInputFilter($formFilters->getInputFilter());
            $form->setData($request->getPost());
    
            if ($form->isValid()) {
    
                // Nowy e-mail
                $new_email = (string)$form->get('email')->getValue();
    
                // Spr. Czy podanego e-maila nie ma w bazie
                if ($this->getUserMapper()->isEmailExists($new_email)==0) {
    
                    // Zmiana e-maila w bazie
                    $this->getUserMapper()->changeUserEmail($uid, $new_email);
    
                    // Wszystko ok
                    $ERR = 2;
    
                    $actual_email = $new_email;
    
                } else { // Podany adres istnieje
                    $ERR = 1;
                }
    
            }
    
        }
    
        return array(
                'ERR' => $ERR,
                'actual_email' => $actual_email,
                'form' => $form,
        );
    }
    
    // Zmiana hasła
    public function passwordAction()
    {
        // Ustawienia długości loginu/hasła
        $cfg = $this->getServiceLocator()->get('user_login_cfg');
    
        // Identyfikator zalogowanego usera
        $uid = $this->getServiceLocator()->get('userId');
    
        // Pobranie danych usera
        $user = $this->getUserMapper()->getUser($uid);
    
        // Formularz
        $form = new PasswordChangeForm($cfg);
        // Filtry
        $formFilters = new PasswordChangeFormFilter($cfg);
    
        // Flaga błędu (1 - Błędne aktualne hasło, 2 - nowe hasła się nie zgadzają, 3 - hasło zmieniono)
        $ERR = 0;
    
        $request = $this->getRequest();
        if ($request->isPost()) {
    
            $form->setInputFilter($formFilters->getInputFilter());
            $form->setData($request->getPost());
    
            if ($form->isValid()) {
    
                // Sprawdzenie poprawności aktualnego hasła
                $p = (string)$form->get('pass')->getValue();
                if ($user->pass == md5($p)) {
    
                    // Spr. poprawności wprowadzonych nowych haseł
                    $p1 = (string)$form->get('pass1')->getValue();
                    $p2 = (string)$form->get('pass2')->getValue();
                    if ($p1 == $p2) {
    
                        // Zmiana hasła w bazie
                        $this->getUserMapper()->changeUserPass($uid, $p1);
    
                        // Flaga
                        $ERR = 3;
    
                    } else { // Wprowadzono błędne nowe hasła
                        $ERR = 2;
                    }
    
                } else { // Podane hasło jest błędne
                    $ERR = 1;
                }
    
            }
    
        }
    
        return array(
                'ERR' => $ERR,
                'form' => $form,
        );
    }

}