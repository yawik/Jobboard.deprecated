<?php
/**
 * YAWIK
 *
 * @filesource
 * @copyright (c) 2013-2015 Cross Solution (http://cross-solution.de)
 * @author cbleek
 * @license   MIT
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

        $config = $this->getServiceLocator()->get('config');

        $viewModel = new ViewModel();
        $viewModel->setVariables(array(
            "company_name"=>$config['imprint']['company_name'],
            "company_fullname"=>$config['imprint']['company_fullname'],
            "company_zip"=>$config['imprint']['company_zip'],
            "company_city"=>$config['imprint']['company_city'],
            "person_name"=>$config['imprint']['person_name'],
            "person_email"=>$config['imprint']['person_email'],
            "person_phone"=>$config['imprint']['person_phone'],
            "person_fax"=>$config['imprint']['person_fax'],
            "piwik_opt_out"=>$config['piwik_opt_out'],
        ));
        $viewModel->setTemplate("jobboard/about");
        return $viewModel;
    }
}