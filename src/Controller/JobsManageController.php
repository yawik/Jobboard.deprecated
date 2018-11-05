<?php

/**
 * YawikDemoJobboard
 *
 * Overwrite for Jobs\Controller\ManageController
 *
 * @filesource
 * @copyright (c) 2013 - 2016 Cross Solution (http://cross-solution.de)
 * @license   MIT
 */

/** ActionController of Core */
namespace YawikDemoJobboard\Controller;

use Auth\Entity\AnonymousUser;
use Auth\Entity\User;
use Auth\Entity\UserInterface;
use Core\Entity\PermissionsInterface;
use Jobs\Controller\ManageController;
use Jobs\Entity\Status;
use YawikDemoJobboard\Entity\AnonymousOrganizationReference;
use Zend\Mvc\MvcEvent;
use Zend\Session\Container as SessionContainer;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Core\Form\SummaryForm;
use Jobs\Listener\Events\JobEvent;
use Core\Form\SummaryFormInterface;
use Zend\Stdlib\ArrayUtils;

/**
 * This Controller handles management actions for jobs.
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 */
class JobsManageController extends ManageController
{

    /**
     * Dispatch listener callback.
     *
     * Attaches the Delayed user mail sending listener to the job event manager.
     *
     * @param MvcEvent $e
     */
    public function preDispatch(MvcEvent $e)
    {
        $routeMatch = $e->getRouteMatch();
        $action = $routeMatch->getParam('action');

        if ('approval' == $action) {
            $services = $this->getServiceLocator();
            $jobEvents = $services->get('Jobs/Events');
            $mailSender = $services->get('YawikDemoJobboard/Listener/DelayedUserRegistrationMailSender');

            $mailSender->attach($jobEvents);
        }

        parent::preDispatch($e);
    }

    protected function injectUserOrganization($user)
    {
        if (!$user instanceof AnonymousUser) {
            return;
        }

        $org = new AnonymousOrganizationReference($user->getId());
        $user->setOrganization($org);
    }

    /**
     * save a Job-Post either by a regular request or by an async post (AJAX)
     * a mandatory parameter is the ID of the Job
     * in case of a regular Request you can
     *
     * parameter are arbitrary elements for defaults or programming flow
     *
     * @param array $parameter
     * @return null|ViewModel
     * @throws \RuntimeException
     */
    protected function save($parameter = array())
    {
        $params             = $this->params();
        $formIdentifier     = $params->fromQuery('form');
        $request            = $this->getRequest();

        if ($request->isPost() && 'regForm' == $formIdentifier) {
            return $this->handleRegistration();
        }
        $serviceLocator     = $this->getServiceLocator();
        $user               = $this->auth()->getUser();
        $session = new SessionContainer('YawikDemoJobboard_Jobs_Manage');
        $repService = $serviceLocator->get('repositories');
        if ($session->isRegistered && $session->user) {
            $users = $repService->get('Auth/User');
            $user = $users->find($session->user);
        }
        $isAnonUser         = $user instanceof AnonymousUser;
        $this->injectUserOrganization($user);

        $userOrg            = $user->getOrganization();
        $translator         = $serviceLocator->get('translator');
        /** @var \Zend\Http\Request $request */

        $isAjax             = $request->isXmlHttpRequest();
        $pageToForm         = $isAnonUser
                            ? array(0 => array('locationForm', 'regForm', 'portalForm'),
                                      1 => array('descriptionForm'),
                                      2 => array('previewForm')
                              )
                            : array(0 => array('locationForm', 'nameForm', 'portalForm'),
                                    1 => array('descriptionForm'),
                                    2 => array('previewForm')
                              );
        $request            = $this->getRequest();
        $params             = $this->params();

        $pageIdentifier     = (int) $params->fromQuery('page', array_key_exists('page', $parameter)?$parameter['page']:0);
        $jobEntity          = $this->getJob();
        $viewModel          = null;
        !$isAnonUser && !$jobEntity->isDraft() && $this->acl($jobEntity, 'edit');
        $form               = $this->getFormular($jobEntity);
        $mvcEvent           = $this->getEvent();

        $valid              = true;
        $instanceForm       = null;
        $viewHelperManager  = $serviceLocator->get('ViewHelperManager');
        $formErrorMessages = array();
        if (isset($formIdentifier) &&  $request->isPost()) {
            // at this point the form get instantiated and immediately accumulated
            $instanceForm = $form->getForm($formIdentifier);
            if (!isset($instanceForm)) {
                throw new \RuntimeException('No form found for "' . $formIdentifier . '"');
            }
            // the id may be part of the postData, but it never should be altered
            $postData = $request->getPost();
            if (isset($postData['id'])) {
                unset($postData['id']);
            }
            unset($postData['applyId']);
            $instanceForm->setData($postData);
            $valid = $instanceForm->isValid();
            $formErrorMessages = ArrayUtils::merge($formErrorMessages, $instanceForm->getMessages());
            if ($valid) {
                $title = $jobEntity->title;
                $templateTitle = $jobEntity->templateValues->title;
                if (empty($templateTitle)) {
                    $jobEntity->templateValues->title = $title;
                }
                $serviceLocator->get('repositories')->persist($jobEntity);
            } else {
            }
        }

        // validation
        $jobValid = $this->validateJob($jobEntity);
        if (is_array($jobValid)) {
            $errorMessage = $jobValid;
            $jobValid = false;
        } else {
            $errorMessage = array();
        }

        $errorMessage = '<br />' . implode('<br />', $errorMessage);
        if ($isAjax) {
            if ($instanceForm instanceof SummaryForm) {
                $instanceForm->setRenderMode(SummaryForm::RENDER_SUMMARY);
                $viewHelper = 'summaryform';
            } else {
                $viewHelper = 'form';
            }
            $content = $viewHelperManager->get($viewHelper)->__invoke($instanceForm);
            $viewModel = new JsonModel(
                array(
                'content' => $content,
                'valid' => $valid,
                'jobvalid' => $jobValid,
                'errors' => $formErrorMessages,
                'errorMessage' => $errorMessage)
            );
        } else {
            if (isset($pageIdentifier)) {
                $form->disableForm();
                if (array_key_exists($pageIdentifier, $pageToForm)) {
                    foreach ($pageToForm[$pageIdentifier] as $actualFormIdentifier) {
                        $form->enableForm($actualFormIdentifier);
                        if ($jobEntity->isDraft()) {
                            $actualForm = $form->get($actualFormIdentifier);
                            if ('nameForm' != $actualFormIdentifier
                                && ('regForm' != $actualFormIdentifier || !$session->isRegistered) && $actualForm instanceof SummaryFormInterface) {
                                $actualForm->setDisplayMode(SummaryFormInterface::DISPLAY_FORM);
                            }
                            if ('regForm' == $actualFormIdentifier && $session->isRegistered) {
                                $org = $jobEntity->getOrganization();
                                $jobUserInfo = $jobEntity->getUser()->getInfo();
                                $actualForm->populateValues(
                                    array(
                                                          'register' => array(
                                                              'organizationName' => $org->getOrganizationName()->getName(),
                                                              'postalCode' => $jobUserInfo->getPostalCode(),
                                                              'city' => $jobUserInfo->getCity(),
                                                              'street' => $jobUserInfo->getStreet(),
                                                              'name' => $jobUserInfo->getFirstName(),
                                                              'email' => $jobUserInfo->getEmail(),
                                                              'phone' => $jobUserInfo->getPhone(),
                                                          )
                                                      )
                                );
                            }
                        }
                    }
                    if (!$jobEntity->isDraft()) {
                        // Job is deployed, some changes are now disabled
                        $form->enableAll();
                    }
                } else {
                    throw new \RuntimeException('No form found for page ' . $pageIdentifier);
                }
            }
            $pageLinkNext = null;
            $pageLinkPrevious = null;
            if (0 < $pageIdentifier) {
                $pageLinkPrevious = $this->url()->fromRoute('lang/jobs/manage', array(), array('query' => array('id' => $jobEntity->id, 'page' => $pageIdentifier - 1)));
            }
            if ($pageIdentifier < count($pageToForm) - 1) {
                $pageLinkNext     = $this->url()->fromRoute('lang/jobs/manage', array(), array('query' => array('id' => $jobEntity->id, 'page' => $pageIdentifier + 1)));
            }
            $completionLink = $this->url()->fromRoute('lang/jobs/completion', array('id' => $jobEntity->id));

            $viewModel = $this->getViewModel($form);
            //$viewModel->setVariable('page_next', 1);
            $viewModel->setVariables(
                array(
                'pageLinkPrevious' => $pageLinkPrevious,
                'pageLinkNext' => $pageLinkNext,
                'completionLink' => $completionLink,
                'page' => $pageIdentifier,
                'title' => $jobEntity->title,
                'job' => $jobEntity,
                'summary' => 'this is what we charge you for your offer...',
                'valid' => $valid,
                'jobvalid' => $jobValid,
                'errorMessage' => $errorMessage,
                'isDraft' => $jobEntity->isDraft()
                )
            );
        }

        if ($user instanceof AnonymousUser && !$session->isRegistered) {
            $cacheUser = $jobEntity->getUser();
            $jobEntity->unsetUser(/* removePermissions */ false);
            $repService->store($jobEntity);
            $repService->detach($jobEntity);
            $jobEntity->setUser($cacheUser);
        }
        return $viewModel;
    }

    protected function validateJob($job)
    {
        $jobValid = true;
        $errorMessage = array();
        $translator = $this->getServiceLocator()->get('translator');
        if (empty($job->title)) {
            $jobValid = false;
            $errorMessage[] = $translator->translate('No Title');
        }
        if (empty($job->location)) {
            $jobValid = false;
            $errorMessage[] = $translator->translate('No Location');
        }
        if (empty($job->termsAccepted)) {
            $jobValid = false;
            $errorMessage[] = $translator->translate('Accept the Terms');
        }

        if ($this->auth()->getUser() instanceof AnonymousUser) {
            $session = new SessionContainer('YawikDemoJobboard_Jobs_Manage');
            if (!$session->isRegistered) {
                $jobValid = false;
                $errorMessage[] = $translator->translate('Please register');
            }
        }
        return $jobValid ?: $errorMessage;
    }
    public function checkApplyIdAction()
    {
        $services = $this->getServiceLocator();
        $validator = $services->get('validatormanager')->get('Jobs/Form/UniqueApplyId');
        if (!$validator->isValid($this->params()->fromQuery('applyId'))) {
            return array(
                'ok' => false,
                'messages' => $validator->getMessages(),
            );
        }
        return array('ok' => true);
    }

    protected function handleRegistration()
    {
        $session = new SessionContainer('YawikDemoJobboard_Jobs_Manage');
        $jobEntity          = $this->getJob();
        $container          = $this->getFormular($jobEntity);
        $form = $container->get('regForm');
        $services = $this->getServiceLocator();
        $repositories = $services->get('repositories');

        $form->setRenderMode(SummaryForm::RENDER_SUMMARY);
        if ($session->isRegistered) {
            $valid = true;
        } else {
            $form->setData($_POST);
            $valid = $form->isValid();
            $errors = $form->getMessages();

            if ($valid) {
                $users = $repositories->get('Auth/User');

                $fs = $form->get('register');
                try {
                    $email = $fs->get('email')->getValue();
                    $name = $fs->get('name')->getValue();
                    if ($users->findByLoginOrEmail($email)) {
                        throw new \Auth\Service\Exception\UserAlreadyExistsException('User already exists');
                    }

                    $user = $users->create(
                        array(
                                                        'login' => $email,
                                                        'role' => User::ROLE_RECRUITER
                                                    )
                    );

                    $info = $user->getInfo();
                    $info->setEmail($email);
                    $user->setEmail($email);
                    $info->setFirstName($name);
                    $info->setEmailVerified(false);

                    if (strstr($name, ' ') !== false) {
                        $nameParts = explode(' ', $name);
                        $firstName = array_shift($nameParts);
                        $lastName = implode(' ', $nameParts);

                        $info->setFirstName($firstName);
                        $info->setLastName($lastName);
                    }

                    $user->setPassword(uniqid('credentials', true));




                    $user->info->houseNumber = $fs->get('houseNumber')->getValue();
                    $user->info->phone = $fs->get('phone')->getValue();
                    $user->info->postalCode = $fs->get('postalCode')->getValue();
                    $user->info->city = $fs->get('city')->getValue();
                    $user->info->street = $fs->get('street')->getValue();
                    $repositories->store($user);

                    $organizationName = $fs->get('organizationName')->getValue();
                    $organization = $repositories->get('Organizations')->createWithName($organizationName);
                    $organization->contact->postalcode = $fs->get('postalCode')->getValue();
                    $organization->contact->city = $fs->get('city')->getValue();
                    $organization->contact->street = $fs->get('street')->getValue();
                    $organization->contact->houseNumber = $fs->get('houseNumber')->getValue();
                    $organization->user = $user;

                    $repositories->store($organization);

                    $jobEntity->setUser($user);
                    $jobEntity->setOrganization($organization);

                    $repositories->store($jobEntity);

                    $session = new SessionContainer('YawikDemoJobboard_Jobs_Manage');
                    $session->isRegistered = true;
                    $session->user = $user->id;

                } catch (\Auth\Service\Exception\UserAlreadyExistsException $e) {
                    $valid = false;
                    $errors['register']['email']['exists'] = /*@translate*/ 'An user with this email address is already registered.';
                }
            }
        }

        if (!$valid) {
            $repositories->detach($jobEntity);
        }

        $jobValid= $this->validateJob($jobEntity);
        if (is_array($jobValid)) {
            $errorMessage = $jobValid;
            $jobValid = false;
        } else {
            $errorMessage = array();
        }

        $content = $this->getServiceLocator()->get('viewHelperManager')->get('summaryform')->__invoke($form);

        $viewModel = new JsonModel(
            array(
            'content' => $content,
            'valid' => $valid,
            'jobvalid' => $jobValid,
            'errors' => $errors,
            'errorMessage' => $errorMessage
                                   )
        );

        return $viewModel;
    }

    protected function getFormular($job)
    {
        $services = $this->getServiceLocator();
        $forms    = $services->get('FormElementManager');
        $container = $forms->get(
            'Jobs/Job',
            array(
            'mode' => $job->id ? 'edit' : 'new'
            )
        );
        if ($this->auth()->getUser() instanceof AnonymousUser) {
            $container->setForm(
                'regForm',
                array(
                'type' => 'YawikDemoJobboard/IncludedRegisterForm',
                'options' => array(
                    'label' => 'Unternehmen',
                    'enable_descriptions' => true,
                    'description' => 'Geben Sie Ihre Kontaktdaten und die Rechnungsanschrift ein.',
                    'display_mode' => SummaryForm::DISPLAY_SUMMARY,
                ))
            );
        }
        $container->setEntity($job);
        $container->setParam('job', $job->id);
        $container->setParam('applyId', $job->applyId);
        return $container;
    }
    
    protected function getJob($allowDraft = true)
    {
        $services       = $this->getServiceLocator();
        $repositories   = $services->get('repositories');
        /** @var \Jobs\Repository\Job $repository */
        $repository     = $repositories->get('Jobs/Job');
        // @TODO three different method to obtain the job-id ?, simplify this
        $id_fromRoute   = $this->params('id', 0);
        $id_fromQuery   = $this->params()->fromQuery('id', 0);
        $id_fromSubForm = $this->params()->fromPost('job', 0);
        $user           = $this->auth()->getUser();
        $id             = empty($id_fromRoute)? (empty($id_fromQuery)?$id_fromSubForm:$id_fromQuery) : $id_fromRoute;
        $session        = new SessionContainer('YawikDemoJobboard_Jobs_Manage');

        if (empty($id) && $allowDraft) {
            if ($user instanceof AnonymousUser) {
                $session = new SessionContainer('YawikDemoJobboard_Jobs_Manage');
                if ($session->job) {
                    $jobId = $session->job;
                    $job = $repository->find($jobId);
                } else {
                    $job = $repository->create();
                    $job->setIsDraft(true);
                    $job->getPermissions()->grantAll($user);
                    $repository->store($job);
                    $session->job = $job->getId();
                }
                if (!$session->isRegistered) {
                    $job->setUser($user);
                }
                return $job;

            }

            $this->acl('Jobs/Manage', 'new');
            /** @var \Jobs\Entity\Job $job */
            $job = $repository->findDraft($user);
            if (empty($job)) {
                $job = $repository->create();
                $job->setIsDraft(true);
                $job->setUser($user);
                $repositories->store($job);
            }

            return $job;
        }

        $jobEntity      = $repository->find($id);
        if (!$jobEntity) {
            throw new \RuntimeException('No job found with id "' . $id . '"');
        }
        if (!$session->isRegistered) {
            $jobEntity->setUser($user);
        }
        return $jobEntity;
    }
    

    /**
     * Job opening is completed.
     *
     * @return array
     */
    public function completionAction()
    {

        $serviceLocator = $this->getServiceLocator();
        $jobEntity      = $this->getJob();

        $jobEntity->isDraft = false;
        $reference = $jobEntity->getReference();
        if (empty($reference)) {
            // create an unique job-reference
            $repository = $this->getServiceLocator()
                               ->get('repositories')
                               ->get('Jobs/Job');
            $jobEntity->setReference($repository->getUniqueReference());
        }
        $jobEntity->changeStatus(Status::CREATED, "job was created");
        $jobEntity->atsEnabled = true;

        /*
         * make the job opening persist and fire the EVENT_JOB_CREATED
         */
        $serviceLocator->get('repositories')->store($jobEntity);

        $jobEvents = $serviceLocator->get('Jobs/Events');
        $jobEvents->trigger(JobEvent::EVENT_JOB_CREATED, $this, array('job' => $jobEntity));

        $model = new ViewModel(array('job' => $jobEntity));
        $model->setTemplate('jobs/manage/completion');

        return $model;
    }
}
