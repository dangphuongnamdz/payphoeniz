<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
            ),
            'detail' => array(
                'type' => 'Zend\Mvc\Router\Http\Regex',
                'options' => array(
                    'regex' => '/(?<post>[a-zA-Z0-9_-]+)-(?<id>[0-9_-]+).html',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Doc',
                        'action' => 'index',
                    ),
                    'spec' => '/%post%/%id%',
                ),
            ),
            
            'passports' => array(
                'type' => 'Zend\Mvc\Router\Http\Regex',
                'options' => array(
                    'regex' => '/passport.html',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Passport',
                        'action' => 'login',
                    ),
                    'spec' => '/%%',
                ),
            ),

            'passport' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/passport[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\Passport',
                        // 'action'     => 'login',
                    ),
                ),
            ),

            'payment' => array(
                'type' => 'Zend\Mvc\Router\Http\Regex',
                'options' => array(
                    'regex' => '/payment/(?<slug>[a-zA-Z0-9_-]+).html',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Payment',
                        'action' => 'index',
                    ),
                    'spec' => '/%slug%',
                ),
            ),

            'payments' => array(
                'type' => 'Zend\Mvc\Router\Http\Regex',
                'options' => array(
                    'regex' => '/payments/(?<slug>[a-zA-Z0-9_-]+).html',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Payments',
                        'action' => 'index',
                    ),
                    'spec' => '/%slug%',
                ),
            ),

            'paymentnone' => array(
                'type' => 'Zend\Mvc\Router\Http\Regex',
                'options' => array(
                    'regex' => '/paymentnone/(?<slug>[a-zA-Z0-9_-]+).html',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Payments',
                        'action' => 'none',
                    ),
                    'spec' => '/%slug%',
                ),
            ),

            'history' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/history[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Application\Controller\History',
                        'action'     => 'index',
                    ),
                ),
            ),

            'successchargeatm' => array(
                'type' => 'Zend\Mvc\Router\Http\Regex',
                'options' => array(
                    'regex' => '/payment/success-charge-atm.html',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Callback',
                        'action' => 'successchargeatm',
                    ),
                    'spec' => '/payment/success-charge-atm.html',
                ),
            ),

            'errorchargeatm' => array(
                'type' => 'Zend\Mvc\Router\Http\Regex',
                'options' => array(
                    'regex' => '/payment/error-charge-atm.html',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Callback',
                        'action' => 'errorchargeatm',
                    ),
                    'spec' => '/payment/error-charge-atm.html',
                ),
            ),
            
           

            // 'balance' => array(
            //     'type' => 'Zend\Mvc\Router\Http\Regex',
            //     'options' => array(
            //         'regex' => '/payment/balance.html',
            //         'defaults' => array(
            //             'controller' => 'Application\Controller\Payment',
            //             'action' => 'balance',
            //         ),
            //         'spec' => '/payment/balance.html',
            //     ),
            // ),

            'getrole' => array(
                'type' => 'Zend\Mvc\Router\Http\Regex',
                'options' => array(
                    'regex' => '/payment/getrole.html',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Payment',
                        'action' => 'getrole',
                    ),
                    'spec' => '/payment/getrole.html',
                ),
            ),
			'getinfogold' => array(
                'type' => 'Zend\Mvc\Router\Http\Regex',
                'options' => array(
                    'regex' => '/payment/getinfogold.html',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Payment',
                        'action' => 'getgold',
                    ),
                    'spec' => '/payment/getinfogold.html',
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory',
        ),
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Application\Controller\Index'   => 'Application\Controller\IndexController',
            'Application\Controller\Doc'     => 'Application\Controller\DocController',
            'Application\Controller\Passport'     => 'Application\Controller\PassportController',
            'Application\Controller\Payment'    => 'Application\Controller\PaymentController',
            'Application\Controller\Payments'    => 'Application\Controller\PaymentsController',
            'Application\Controller\History'    => 'Application\Controller\HistoryController',
            'Application\Controller\Callback'    => 'Application\Controller\CallbackController',
        ),
    ),
    'view_manager' => array(
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
            'layout/passport' => __DIR__ . '/../view/layout/passport.phtml'
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),

    'module_layouts' => array(
        'login' => 'layout/admin.phtml'
    ),

    'console' => array(
        'router' => array(
            'routes' => array(
            ),
        ),
    ),

    'navigation' => array(
        'default' => array(
        ),
    ),
);
