<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013-2014 Cross Solution (http://cross-solution.de)
 * @author cbleek
 * @license   AGPLv3
 */

namespace YawikDemoJobboard\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ContentController extends AbstractActionController {
    /**
     * Processes formular data of the application form
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $viewModel = new ViewModel();
        $viewModel->setTemplate("jobboard/about");
        return $viewModel;
    }
}