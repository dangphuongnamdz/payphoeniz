<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'controllers' => array(
        'invokables' => array(
            'Api\Controller\IapRestful' => 'Api\Controller\IapRestfulController',
            'Api\Controller\AgentRestful' => 'Api\Controller\AgentRestfulController',
        ),
    ),
    'router' => array(
        'routes' => array(
            'iap' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/api/iap/exchange',
                    'defaults' => array(
                        'controller' => 'Api\Controller\IapRestful',
                        'action'     => 'exchange',
                    ),
                ),
            ),
            'server' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/api/server/info',
                    'defaults' => array(
                        'controller' => 'Api\Controller\AgentRestful',
                        'action'     => 'serverInfo',
                    ),
                ),
            ),
            'role' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/api/role/info',
                    'defaults' => array(
                        'controller' => 'Api\Controller\AgentRestful',
                        'action'     => 'roleInfo',
                    ),
                ),
            ),
            'exchange' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/api/role/exchange',
                    'defaults' => array(
                        'controller' => 'Api\Controller\AgentRestful',
                        'action'     => 'exchange',
                    ),
                ),
            ),
        ),
    ),
    'view_manager' => array(
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
);