<?php

/**
 * create a config/autoload/YawikDemoJobboard.local.php and put modifications there.
 */

\YawikDemoJobboard\Module::$isLoaded = true;

return array('view_manager' => array(
    'template_map' => array(
        'layout/layout'        => __DIR__ . '/../view/layout.phtml',
        'core/index/index'     => __DIR__ . '/../view/index.phtml',
        'auth/manage/password' => __DIR__ . '/../view/password.phtml',
        'piwik'                => __DIR__ . '/../view/piwik.phtml',
        'jobboard/about'       => __DIR__ . '/../view/about.phtml',
        'main-navigation'      => __DIR__ . '/../view/main-navigation.phtml',
        'jobs/form/list-filter' => __DIR__ . '/../view/search-for-jobs.phtml',
        'templates/default/index' => __DIR__ . '/../view/templates/default/index.phtml'
    ),
),
             'translator'   => array(
                 'translation_file_patterns' => array(
                     array(
                         'type'     => 'gettext',
                         'base_dir' => __DIR__ . '/../language',
                         'pattern'  => '%s.mo',
                     ),
                 ),
             ),
             'router'       => array(
                 'routes' => array(
                     'lang' => array(
                         'options' => array(
                             'defaults' => array(
                                 'controller' => 'Jobs/Jobboard', //Overwrites the route of the start Page
                                 'action'     => 'index',
                             ),
                         ),
                         'child_routes' => array(

                             'about'    => array(
                                 'type' => 'Literal',          // route is interpreted as string
                                 'options' => array(
                                     'route' => '/about',
                                     'defaults' => array(
                                         'controller' => 'Jobboard/Content',
                                         'action'     => 'index',
                                     ),
                                 ),
                             ),
                         ),
                     ),
                 ),
             ),
             'acl' => array(
                 'rules' => array(
                     // guests are allowed to see a list of companies.
                     'guest' => array(
                         'allow' => array(
                             'route/lang/organizations',
                         ),
                     ),
                 ),
             ),
             'navigation' => array(
                 'default' => array(
                     'jobboard' => array(
                         'label' => 'About',
                         'route' => 'lang/about',
                         'order' => 2000,                             // allows to order the menu items
                         //    'resource' => 'route/lang/organizations',  // if a resource is defined, the acl will be applied.


                     ),

                 ),
             ),
             'controllers' => array(
                'invokables' => array(
                  'Jobboard/Content' => '\YawikDemoJobboard\Controller\ContentController',
                ),
             ),
);