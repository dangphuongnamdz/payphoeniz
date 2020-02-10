<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin;

 use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
 use Zend\ModuleManager\Feature\ConfigProviderInterface;
 use Admin\Model\User;
 use Admin\Model\UserTable;
 use Admin\Model\Category;
 use Admin\Model\CategoryTable;
 use Admin\Model\Savelog;
 use Admin\Model\SavelogTable;
 use Admin\Model\Posts;
 use Admin\Model\PostsTable;
 use Admin\Model\Server;
 use Admin\Model\ServerTable;
 use Admin\Model\StatisticTable;
 use Admin\Model\GamerTable;
 use Admin\Model\Product;
 use Admin\Model\ProductTable;
 use Admin\Model\Gold;
 use Admin\Model\GoldTable;
 use Admin\Model\ChargeType;
 use Admin\Model\ChargeTypeTable;

 use Zend\Db\ResultSet\ResultSet;
 use Zend\Db\TableGateway\TableGateway;
 use Zend\Authentication\Adapter\DbTable as DbTableAuthAdapter;
 use Zend\Authentication\AuthenticationService;
 use Zend\Session\Container;
 
 use Zend\View\HelperPluginManager;

 use Zend\Mvc\MvcEvent;
 use Zend\Permissions\Acl\Acl;
 use Zend\Permissions\Acl\Role\GenericRole;
 use Zend\Permissions\Acl\Resource\GenericResource;

 class Module implements AutoloaderProviderInterface, ConfigProviderInterface
 {
     
    public function onBootstrap($e) {
        $session = new Container('User');
        $user_name= $session->offsetGet('user_name');
        $e->getViewModel()->setVariable('username', $user_name);
        $e->getApplication()
                ->getEventManager()
                ->getSharedManager()
                ->attach('Zend\Mvc\Controller\AbstractController', 'dispatch', 
                        function($e) {
                            $controller = $e->getTarget();
                            $controllerClass = get_class($controller);
                            $moduleNamespace = substr($controllerClass, 0, strpos($controllerClass, '\\'));
                            $config = $e->getApplication()->getServiceManager()->get('config');
                            if (isset($config['module_layouts'][$moduleNamespace])) {
                                $controller->layout($config['module_layouts'][$moduleNamespace]);
                            }
                        }, 100);
        $this->initAcl($e);
        $e->getApplication()->getEventManager()->attach('route', array($this, 'checkAcl'));
    }
    
    public function getViewHelperConfig()
    {
        return [
            'factories' => [
                // This will overwrite the native navigation helper
                'navigation' => function(HelperPluginManager $pm) {
                    // Setup ACL:
                    $acl = new Acl();
                    $roles = include __DIR__ . '/config/module.roles.php';
                    $allResources = array();
                    foreach ($roles as $role => $resources) {
                        // Add groups to the Role registry using Zend\Permissions\Acl\Role\GenericRole
                        $role = new GenericRole($role);
                        $acl->addRole($role);
                        $allResources = array_merge($resources, $allResources);
                        foreach ($resources as $resource) {
                            if(!$acl->hasResource($resource)){
                                $acl->addResource(new GenericResource($resource));
                                
                            }
                        }
                        //adding restrictions
                        foreach ($allResources as $resource) {
                            if(!$acl->isAllowed($role, $resource))
                            {
                                $acl->allow($role, $resource);
                            }
                        }
                    }
                    $navigation = $pm->get('Zend\View\Helper\Navigation');

                    $userRole = '';
                    $session = new Container('User');
                    $level= $session->offsetGet('level');
                    switch($level){
                        case 1: $userRole = "admin"; break;
                        case 2: $userRole = "editor"; break;
                        case 3: $userRole = "operation"; break;
                        default: $userRole = "guest"; break;
                    }
                    $navigation->setAcl($acl);
                    $navigation->setRole($userRole);

                    return $navigation;
                }
            ]
        ];
    }

    public function initAcl(MvcEvent $e) {
 
        $acl = new Acl();
        $roles = include __DIR__ . '/config/module.roles.php';
        $allResources = array();
        foreach ($roles as $role => $resources) {
            // Add groups to the Role registry using Zend\Permissions\Acl\Role\GenericRole
            $role = new GenericRole($role);
            $acl->addRole($role);
            $allResources = array_merge($resources, $allResources);
            foreach ($resources as $resource) {
                if(!$acl->hasResource($resource)){
                    $acl->addResource(new GenericResource($resource));
                    
                }
            }
            //adding restrictions
            foreach ($allResources as $resource) {
                if(!$acl->isAllowed($role, $resource))
                {
                    $acl->allow($role, $resource);
                }
            }
        }
        $e->getViewModel()->acl = $acl;
    }

    public function checkAcl(MvcEvent $e) {
        //$route = $e->getRouteMatch()->getMatchedRouteName();

        $routeMatch = $e->getRouteMatch();
        $controller = $routeMatch->getParam('controller');
        $action = $routeMatch->getParam('action');
        // set user role
        $userRole = 'guest';
        $session = new Container('User');
        $level= $session->offsetGet('level');
        switch($level){
            case 1: $userRole = "admin"; break;
            case 2: $userRole = "editor"; break;
            case 3: $userRole = "operation"; break;
            default: $userRole = "guest"; break;
        }
        if ($userRole != "guest") {
            if ($e->getViewModel()->acl->hasResource($controller) && !$e->getViewModel()->acl->isAllowed($userRole, $controller)) 
            {
                $response = $e->getResponse();
                $response->getHeaders()->addHeaderLine('Location', $e->getRequest()->getBaseUrl() . '/404');
                $response->setStatusCode(404);
            }
        }
    }
    
     public function getAutoloaderConfig()
     {
         return array(
             'Zend\Loader\StandardAutoloader' => array(
                 'namespaces' => array(
                     __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                 ),
             ),
             'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
         );
     }

     public function getConfig()
     {
         return include __DIR__ . '/config/module.config.php';
     }

     public function getServiceConfig()
     {
         return array(
             'factories' => array(
                 //check login
                'Admin\Model\MyAuthStorage' => function($sm){
                    return new \Admin\Model\MyAuthStorage('zf_tutorial');
                },
                'AuthService' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $dbTableAuthAdapter  = new DbTableAuthAdapter($dbAdapter,
                        #'users','username','password', 'MD5(?)');
                        'users','username','password', 'MD5(?)');
                    $authService = new AuthenticationService();
                    $authService->setAdapter($dbTableAuthAdapter);
                    $authService->setStorage($sm->get('Admin\Model\MyAuthStorage'));
                    return $authService;
                },
                //user
                'Admin\Model\UserTable' =>  function($sm) {
                    $tableGateway = $sm->get('UserTableGateway');
                    $table = new UserTable($tableGateway);
                    return $table;
                },
                'UserTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new User());
                    return new TableGateway('users', $dbAdapter, null, $resultSetPrototype);
                },
                //Category
                'Admin\Model\CategoryTable' =>  function($sm) {
                    $tableGateway = $sm->get('CategoryTableGateway');
                    $table = new CategoryTable($tableGateway);
                    return $table;
                },
                'CategoryTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Category());
                    return new TableGateway('category', $dbAdapter, null, $resultSetPrototype);
                },
				//savelog
                'Admin\Model\SavelogTable' =>  function($sm) {
                    $tableGateway = $sm->get('SavelogTableGateway');
                    $table = new SavelogTable($tableGateway);
                    return $table;
                },
                'SavelogTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
					$resultSetPrototype->setArrayObjectPrototype(new Savelog());
                    return new TableGateway('logactions', $dbAdapter, null, $resultSetPrototype);
                },
                //Posts
                'Admin\Model\PostsTable' =>  function($sm) {
                    $tableGateway = $sm->get('PostsTableGateway');
                    $table = new PostsTable($tableGateway);
                    return $table;
                },
                'PostsTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Posts());
                    return new TableGateway('posts', $dbAdapter, null, $resultSetPrototype);
                },
                //Server
                'Admin\Model\ServerTable' =>  function($sm) {
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
                //statistic
                'Admin\Model\StatisticTable' =>  function($sm) {
                    $tableGateway = $sm->get('StatisticTableGateway');
                    $table = new StatisticTable($tableGateway);
                    return $table;
                },
                'StatisticTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    return new TableGateway('', $dbAdapter, null, $resultSetPrototype);
                },
                //gamer
                'Admin\Model\GamerTable' =>  function($sm) {
                    $tableGateway = $sm->get('GamerTableGateway');
                    $table = new GamerTable($tableGateway);
                    return $table;
                },
                'GamerTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    return new TableGateway('', $dbAdapter, null, $resultSetPrototype);
                },
                //Product
                'Admin\Model\ProductTable' =>  function($sm) {
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
                //Gold
                'Admin\Model\GoldTable' =>  function($sm) {
                    $tableGateway = $sm->get('GoldTableGateway');
                    $table = new GoldTable($tableGateway);
                    return $table;
                },
                'GoldTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Gold());
                    return new TableGateway('gold', $dbAdapter, null, $resultSetPrototype);
                },
                //ChargeType
                'Admin\Model\ChargeTypeTable' =>  function($sm) {
                    $tableGateway = $sm->get('ChargeTypeTableGateway');
                    $table = new ChargeTypeTable($tableGateway);
                    return $table;
                },
                'ChargeTypeTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new ChargeType());
                    return new TableGateway('charge_type', $dbAdapter, null, $resultSetPrototype);
                },
             ),
         );
     }
     
 }