<?php

namespace User\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\View\Model\ViewModel;

class ConfigMenu extends AbstractHelper
{

    public function __invoke()
    {
        $view = new ViewModel();

        $view->setTemplate('user/configMenu');

        return $this->getView()->render($view);
    }
}