<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(  
    'router' => array(
        'routes' => array(
            //truoc khi login
            'login' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/admin/auth',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Admin\Controller',
                        'controller'    => 'Auth',
                        'action'        => 'login',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'process' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/admin/[:action]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),
            //set password moi
            'setpassword' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/admin/resetpassword/setpassword[/:token]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Auth',
                        'action'     => 'setpassword',
                    ),
                ),
            ),
            //reset password moi
            'resetpassword' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/admin/resetpassword',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Index',
                        'action'     => 'changepassword',
                    ),
                ),
            ),
            'admin' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/admin',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Admin\Controller',
                        'controller'    => 'index',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/admin/[:action]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                ),
            ),
            'adminuser' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/admin/user[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\User',
                        'action'     => 'index',
                    ),
                ),
            ),
            'changepassword' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/admin/changepassword',
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Index',
                        'action'     => 'changepassword',
                    ),
                ),
            ),
            //
            'admincategory' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/admin/category[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Category',
                        'action'     => 'index',
                    ),
                ),
            ),
            'adminpost' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/admin/posts[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Posts',
                        'action'     => 'index',
                    ),
                ),
            ),
            'adminserver' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/admin/server[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Server',
                        'action'     => 'index',
                    ),
                ),
            ),
            'adminthongke' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/admin/statistic[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Statistic',
                        'action'     => 'index',
                    ),
                ),
            ),
            'gamer' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/admin/gamer[/:action][/:id][/:username][/:agent]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[a-zA-Z0-9_-]*',
                        'username'     => '[a-zA-Z0-9_-]*',
                        'agent'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Gamer',
                        'action'     => 'index',
                    ),
                ),
            ),
            'adminproduct' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/admin/product[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Product',
                        'action'     => 'index',
                    ),
                ),
            ),
            'admingold' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/admin/gold[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\Gold',
                        'action'     => 'index',
                    ),
                ),
            ),
            'adminchargetype' => array(
                'type'    => 'segment',
                'options' => array(
                    'route'    => '/admin/charge-type[/:action][/:id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'id'     => '[0-9]+',
                    ),
                    'defaults' => array(
                        'controller' => 'Admin\Controller\ChargeType',
                        'action'     => 'index',
                    ),
                ),
            ),
        ),
    ),

    'controllers' => array(
        'invokables' => array(
            'Admin\Controller\Index'        => 'Admin\Controller\IndexController',
            'Admin\Controller\Auth'         => 'Admin\Controller\AuthController',
            'Admin\Controller\User'         => 'Admin\Controller\UserController',
            'Admin\Controller\Category'     => 'Admin\Controller\CategoryController',
            'Admin\Controller\Posts'        => 'Admin\Controller\PostsController',
            'Admin\Controller\Server'       => 'Admin\Controller\ServerController',
            'Admin\Controller\Image'        => 'Admin\Controller\ImageController',
            'Admin\Controller\Statistic'    => 'Admin\Controller\StatisticController',
            'Admin\Controller\Gamer'        => 'Admin\Controller\GamerController',
            'Admin\Controller\Product'      => 'Admin\Controller\ProductController',
            'Admin\Controller\Gold'         => 'Admin\Controller\GoldController',
            'Admin\Controller\ChargeType'   => 'Admin\Controller\ChargeTypeController',
        ),
    ),

    'view_manager' => array(
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'admin/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'admin/index/index' => __DIR__ . '/../view/admin/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
            'admin/auth' => __DIR__ . '/../view/layout/login.phtml'
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
        'strategies' => "ViewJsonStrategy",
    ),

    'module_layouts' => array(
        'Admin' => 'layout/layout.phtml'
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
    'navigation' => array(
        'default' => array(
            // array(
            //     'label' => 'Dashboard',
            //     'route' => 'admin',
            //     'resource'  => 'Admin\Controller\Index',
            // ),
            array(
                'label' => 'Chuyên mục',
                'route' => 'admincategory',
                'resource'  => 'Admin\Controller\Category',
                'pages' => array(
                    array(
                        'label' => 'Quản lý chuyên mục',
                        'action' => 'index',
                        'route' => 'admincategory',
                        'visible'    => true 
                    ),
                    array(
                        'label' => 'Thêm mới',
                        'action' => 'add',
                        'route' => 'admincategory',
                        'visible' => true,
                    ),
                    array(
                        'label' => 'Chỉnh sửa',
                        'action' => 'edit',
                        'route' => 'admincategory',
                        'visible' => false,
                    ),
                    array(
                        'label' => 'Xóa',
                        'action' => 'delete',
                        'route' => 'admincategory',
                        'visible' => false,
                    ),
                ),
            ),
            array(
                'label' => 'Bài viết',
                'route' => 'adminpost',
                'resource'  => 'Admin\Controller\Posts',
                'pages' => array(
                    array(
                        'label' => 'Quản lý bài viết',
                        'action' => 'index',
                        'route' => 'adminpost',
                        'visible'    => true 
                    ),
                    array(
                        'label' => 'Thêm mới',
                        'action' => 'add',
                        'route' => 'adminpost',
                        'visible' => true,
                    ),
                    array(
                        'label' => 'Chỉnh sửa',
                        'action' => 'edit',
                        'route' => 'adminpost',
                        'visible' => false,
                    ),
                    array(
                        'label' => 'Xóa',
                        'action' => 'delete',
                        'visible' => false,
                    ),
                ),
            ),
            array(
                'label' => 'Loại thẻ thanh toán',
                'route' => 'adminchargetype',
                'resource'  => 'Admin\Controller\ChargeType',
                'pages' => array(
                    array(
                        'label' => 'Quản lý loại thẻ',
                        'action' => 'index',
                        'route' => 'adminchargetype',
                        'visible'    => true 
                    ),
                    array(
                        'label' => 'Thêm mới',
                        'action' => 'add',
                        'route' => 'adminchargetype',
                        'visible' => true,
                    ),
                    array(
                        'label' => 'Chỉnh sửa',
                        'action' => 'edit',
                        'route' => 'adminchargetype',
                        'visible' => false,
                    ),
                    array(
                        'label' => 'Xóa',
                        'action' => 'adminchargetype',
                        'visible' => false,
                    ),
                ),
            ),
            array(
                'label' => 'Gold',
                'route' => 'admingold',
                'resource'  => 'Admin\Controller\Gold',
                'pages' => array(
                    array(
                        'label' => 'Quản lý gold',
                        'action' => 'index',
                        'route' => 'admingold',
                        'visible'    => true 
                    ),
                    array(
                        'label' => 'Thêm mới',
                        'action' => 'add',
                        'route' => 'admingold',
                        'visible' => true,
                    ),
                    array(
                        'label' => 'Chỉnh sửa',
                        'action' => 'edit',
                        'route' => 'admingold',
                        'visible' => false,
                    ),
                    array(
                        'label' => 'Xóa',
                        'action' => 'delete',
                        'visible' => false,
                    ),
                ),
            ),
            array(
                'label' => 'Game',
                'route' => 'adminproduct',
                'resource'  => 'Admin\Controller\Product',
                'pages' => array(
                    array(
                        'label' => 'Quản lý game',
                        'action' => 'index',
                        'route' => 'adminproduct',
                        'visible'    => true 
                    ),
                    array(
                        'label' => 'Thêm mới',
                        'action' => 'add',
                        'route' => 'adminproduct',
                        'visible' => true,
                    ),
                    array(
                        'label' => 'Chỉnh sửa',
                        'action' => 'edit',
                        'route' => 'adminproduct',
                        'visible' => false,
                    ),
                    array(
                        'label' => 'Xóa',
                        'action' => 'delete',
                        'route' => 'adminproduct',
                        'visible' => false,
                    ),
                ),
            ),
            array(
                'label' => 'Server',
                'route' => 'adminserver',
                'resource'  => 'Admin\Controller\Server',
                'pages' => array(
                    array(
                        'label' => 'Quản lý server',
                        'action' => 'index',
                        'route' => 'adminserver',
                        'visible'    => true 
                    ),
                    array(
                        'label' => 'Thêm mới',
                        'action' => 'add',
                        'route' => 'adminserver',
                        'visible' => true,
                    ),
                    array(
                        'label' => 'Chỉnh sửa',
                        'action' => 'edit',
                        'route' => 'adminserver',
                        'visible' => false,
                    ),
                    array(
                        'label' => 'Xóa',
                        'action' => 'delete',
                        'route' => 'adminserver',
                        'visible' => false,
                    ),
                ),
            ),
            //////
            array(
                'label' => 'Thống kê hàng ngày',
                'route' => 'adminthongke',
                'resource'  => 'Admin\Controller\Statistic',
                'pages' => array(
                    array(
                        'label' => 'Thống kê nạp tiền',
                        'action' => 'index',
                        'route' => 'adminthongke',
                        'visible'    => true 
                    ),
                ),
            ),
            array(
                'label' => 'Quản lý nạp tiền',
                'route' => 'adminthongke',
                'resource'  => 'Admin\Controller\Statistic',
                'pages' => array(
                    array(
                        'label' => 'Lịch sử nạp thẻ',
                        'action' => 'cardGetHistory',
                        'route' => 'adminthongke',
                        'visible'    => true 
                    ),
                    array(
                        'label' => 'Lịch sử nạp vào game',
                        'action' => 'payGetHistory',
                        'route' => 'adminthongke',
                        'visible'    => true 
                    ),
                    array(
                        'label' => 'Lịch sử nạp vào app',
                        'action' => 'iapGetHistory',
                        'route' => 'adminthongke',
                        'visible'    => true 
                    ),
                    array(
                        'label' => 'Xử lý đền bù',
                        'resource'  => 'Admin\Controller\Gamer',
                        'action' => 'index',
                        'route' => 'gamer',
                        'visible'    => true 
                    ),
                ),
            ),
            array(
                'label' => 'Quản lý tài khoản',
                'route' => 'gamer',
                'resource'  => 'Admin\Controller\Gamer',
                'pages' => array(
                    array(
                        'label' => 'Thông tin user passport',
                        'action' => 'info',
                        'route' => 'gamer',
                        'visible'    => true 
                    ),
                    // array(
                    //     'label' => 'Đặt lại mật khẩu',
                    //     'action' => 'forgetpassword',
                    //     'route' => 'gamer',
                    //     'visible'    => true 
                    // ),
                ),
            ),
            //////
            array(
                'label' => 'Người dùng Admin',
                'route' => 'adminuser',
                'resource'  => 'Admin\Controller\User',
                'pages' => array(
                    array(
                        'label' => 'Quản lý user admin',
                        'action' => 'index',
                        'route' => 'adminuser',
                        'visible'    => true 
                    ),
                    array(
                        'label' => 'Thêm mới',
                        'action' => 'add',
                        'route' => 'adminuser',
                        'visible' => true,
                    ),
                    array(
                        'label' => 'Chỉnh sửa',
                        'action' => 'edit',
                        'route' => 'adminuser',
                        'visible' => false,
                    ),
                    array(
                        'label' => 'Xóa',
                        'action' => 'delete',
                        'route' => 'adminuser',
                        'visible' => false,
                    ),
                ),
            ),
            
        ),
    ),
);