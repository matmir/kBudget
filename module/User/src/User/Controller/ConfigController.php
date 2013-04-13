<?php
/**
    @author Mateusz Mirosławski
    
    Kontroler konfiguracji ustawień usera (kategorie/zmiana hasła/maila)
*/

namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Authentication\Adapter\DbTable as AuthAdapter;
use Zend\Authentication\AuthenticationService;

class ConfigController extends AbstractActionController
{
    // Główna strona
    public function indexAction()
    {
    }

}