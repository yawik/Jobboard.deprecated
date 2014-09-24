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
                                 'controller' => 'Jobs/Index', //Overwrites the route of the start Page
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
             'controllers' => array(
                'invokables' => array(
                  'Jobboard/Content' => '\YawikDemoJobboard\Controller\ContentController',
                ),
             ),
);