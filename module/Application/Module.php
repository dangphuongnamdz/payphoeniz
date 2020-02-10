<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Application\Model\Menu;
use Application\Model\MenuTable;
use Application\Model\Server;
use Application\Model\ServerTable;
use Application\Model\HistoryTrans;
use Application\Model\HistoryTransTable;
use Application\Model\Post;
use Application\Model\PostTable;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Application\Model\User;
use Application\Model\UserTable;
use Application\Model\Product;
use Application\Model\ProductTable;
use Application\Model\Role;
use Application\Model\RoleTable;
use Application\Model\HistoryTable;
use Application\Model\Payment;
use Application\Model\PaymentTable;
use Application\Model\Agent\M001;
use Application\Model\Agent\M002;
use Application\Model\Agent\M003;
use Application\Model\Agent\M004;
use Application\Model\Agent\M005;
use Application\Model\Agent\H001;
use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Authentication\Storage;
use Zend\Authentication\AuthenticationService;

use Zend\Authentication\Adapter\DbTable as DbTableAuthAdapter;

class Module
{
    
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
               //menu
               'Application\Model\MenuTable' =>  function($sm) {
                   $tableGateway = $sm->get('MenuTableGateway');
                   $table = new MenuTable($tableGateway);
                   return $table;
               },
               'MenuTableGateway' => function ($sm) {
                   $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                   $resultSetPrototype = new ResultSet();
                   $resultSetPrototype->setArrayObjectPrototype(new Menu());
                   return new TableGateway('category', $dbAdapter, null, $resultSetPrototype);
               },
               //server
               'Application\Model\ServerTable' =>  function($sm) {
                $tableGateway = $sm->get('ServerTableGateway');
                $table = new ServerTable($tableGateway);
                return $table;
                },
                'ServerTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Server());
                    return new TableGateway('servers', $dbAdapter, null, $resultSetPrototype);
                },
                //post
               'Application\Model\PostTable' =>  function($sm) {
                $tableGateway = $sm->get('PostTableGateway');
                $table = new PostTable($tableGateway);
                return $table;
                },
                'PostTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Post());
                    return new TableGateway('posts', $dbAdapter, null, $resultSetPrototype);
                },
                //check login user from session
                'Application\Model\UserAuthStorage' => function($sm){
                    return new \Application\Model\UserAuthStorage('zf_tutorials');
                },
                'AuthServices' => function($sm) {
                    $authServices = new AuthenticationService();
                    $authServices->setStorage($sm->get('Application\Model\UserAuthStorage'));
                    return $authServices;
                },
                //user
               'Application\Model\UserTable' =>  function($sm) {
                $tableGateway = $sm->get('UserTableGateway');
                $table = new UserTable($tableGateway);
                return $table;
                },
                'UserTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new User());
                    return new TableGateway('user', $dbAdapter, null, $resultSetPrototype);
                },
                //product
               'Application\Model\ProductTable' =>  function($sm) {
                    $tableGateway = $sm->get('ProductTableGateway');
                    $table = new ProductTable($tableGateway);
                    return $table;
                },
                'ProductTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Product());
                    return new TableGateway('product', $dbAdapter, null, $resultSetPrototype);
                },
                //history
                'Application\Model\HistoryTable' =>  function($sm) {
                    $tableGateway = $sm->get('HistoryTableGateway');
                    $table = new HistoryTable($tableGateway);
                    return $table;
                },
                'HistoryTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    return new TableGateway('', $dbAdapter, null, $resultSetPrototype);
                },
                //role
               'Application\Model\RoleTable' =>  function($sm) {
                $tableGateway = $sm->get('RoleTableGateway');
                $table = new RoleTable($tableGateway);
                return $table;
                },
                'RoleTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Role());
                    return new TableGateway('role_user', $dbAdapter, null, $resultSetPrototype);
                },
                //Payment
                'Application\Model\PaymentTable' =>  function($sm) {
                    $tableGateway = $sm->get('PaymentTableGateway');
                    $table = new PaymentTable($tableGateway);
                    return $table;
                },
                'PaymentTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    return new TableGateway('', $dbAdapter, null, $resultSetPrototype);
                },
                //M001
                'Application\Model\Agent\M001' =>  function($sm) {
                    $tableGateway = $sm->get('M001TableGateway');
                    $table = new M001($tableGateway);
                    return $table;
                },
                'M001TableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    return new TableGateway('', $dbAdapter, null, $resultSetPrototype);
                },
				//M002
                'Application\Model\Agent\M002' =>  function($sm) {
                    $tableGateway = $sm->get('M002TableGateway');
                    $table = new M002($tableGateway);
                    return $table;
                },
                'M002TableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    return new TableGateway('', $dbAdapter, null, $resultSetPrototype);
                },
				//M003
                'Application\Model\Agent\M003' =>  function($sm) {
                    $tableGateway = $sm->get('M003TableGateway');
                    $table = new M003($tableGateway);
                    return $table;
                },
                'M003TableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    return new TableGateway('', $dbAdapter, null, $resultSetPrototype);
                },
				//M004
                'Application\Model\Agent\M004' =>  function($sm) {
                    $tableGateway = $sm->get('M004TableGateway');
                    $table = new M004($tableGateway);
                    return $table;
                },
                'M004TableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    return new TableGateway('', $dbAdapter, null, $resultSetPrototype);
                },
                //M005
                'Application\Model\Agent\M005' =>  function($sm) {
                    $tableGateway = $sm->get('M005TableGateway');
                    $table = new M005($tableGateway);
                    return $table;
                },
                'M005TableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    return new TableGateway('', $dbAdapter, null, $resultSetPrototype);
                },
                //H001
                'Application\Model\Agent\H001' =>  function($sm) {
                    $tableGateway = $sm->get('H001TableGateway');
                    $table = new H001($tableGateway);
                    return $table;
                },
                'H001TableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    return new TableGateway('', $dbAdapter, null, $resultSetPrototype);
                },
                //HistoryTrans
                'Application\Model\HistoryTransTable' =>  function($sm) {
                    $tableGateway = $sm->get('HistoryTransTableGateway');
                    $table = new HistoryTransTable($tableGateway);
                    return $table;
                },
                'HistoryTransTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new HistoryTrans());
                    return new TableGateway('history_trans', $dbAdapter, null, $resultSetPrototype);
                },
            ), 
        );
    }
}
