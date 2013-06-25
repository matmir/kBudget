<?php

namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Authentication\Adapter\DbTable as AuthAdapter;
use Zend\Authentication\AuthenticationService;

/**
 * Configuration controller
 * 
 * @author Mateusz Mirosławski
 *
 */
class ConfigController extends AbstractActionController
{
    /**
     * Main configuration site
     */
    public function indexAction()
    {
    }

}
