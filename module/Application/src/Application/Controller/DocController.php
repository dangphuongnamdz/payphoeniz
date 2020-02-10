<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Mvc\MvcEvent;
use Application\Model\Post;

class DocController extends AbstractActionController
{
    protected $menu;
    protected $postTable;
    protected $storage;
    protected $authservices;

    public function getAuthServices()
    {
        if (! $this->authservices) {
            $this->authservices = $this->getServiceLocator()
                                      ->get('AuthServices');
        }
        return $this->authservices;
    }

    public function getSessionStorage()
    {
        if (! $this->storage) {
            $this->storage = $this->getServiceLocator()
                                  ->get('Application\Model\UserAuthStorage');
        }
        return $this->storage;
    }

    public function getMenuTable()
    {
        if (!$this->menu) {
            $sm = $this->getServiceLocator();
            $this->menu = $sm->get('Application\Model\MenuTable');
        }
        return $this->menu;
    }
    
    public function object_to_array($data)
    {
        if (is_array($data) || is_object($data))
        {
            $result = array();
            foreach ($data as $key => $value)
            {
                $result[$key] = $this->object_to_array($value);
                
            }
            return $result;
        }
        return $data;
    }
    
    public function onDispatch(MvcEvent $e)
    {
        try{
            if ($this->getAuthServices()->hasIdentity()){
                $user = $this->getAuthServices()->getIdentity();
                $login = array(
                    'is_login'  =>  true,
                    'username'  =>  $user
                );
            }
            else{
                $login = array(
                    'is_login'  =>  false
                );
            }
            $data = array();
            $data = $this->object_to_array($this->getMenuTable()->fetchAll());
            $arr_tree = array();
            $arr_tmp = array();
            foreach ($data as $item) {
                $parentid = $item['id_parent'];
                $id = $item['id'];
            
                if ($parentid  == 0)
                {
                    $arr_tree[$id] = $item;
                    $arr_tmp[$id] = &$arr_tree[$id];
                }
                else 
                {
                    if (!empty($arr_tmp[$parentid])) 
                    {
                        $arr_tmp[$parentid]['children'][$id] = $item;
                        $arr_tmp[$id] = &$arr_tmp[$parentid]['children'][$id];
                    }
                }
            }
            unset($arr_tmp);
            //send to layout
            $this->layout()->login = $login;   
            $this->layout()->menu = $arr_tree;   
            $this->layout()->config = $this->getServiceLocator()->get('config');
            return parent::onDispatch($e);
        }catch (Exception $e) {
            $this->getResponse()->setStatusCode(404);
            return;
        }
    }

    public function getPostTable()
    {
        if (!$this->postTable) {
            $sm = $this->getServiceLocator();
            $this->postTable = $sm->get('Application\Model\PostTable');
        }
        return $this->postTable;
    }

    //get chi tiet post
    public function indexAction()
    {  
        $id = $this->params()->fromRoute('id');
        $post = $this->getPostTable()->fetchDetailPost($id);
        return new ViewModel(array(
            'post' => $post
        ));
    }

    
    // public function unique_multidim_array($array, $key) { 
    //     $temp_array = array(); 
    //     $i = 0; 
    //     $key_array = array(); 
    //     foreach($array as $val) { 
    //         if (!in_array($val[$key], $key_array)) { 
    //             $key_array[$i] = $val[$key]; 
    //             $temp_array[$i] = $val; 
    //         } 
    //         $i++; 
    //     } 
    //     return $temp_array; 
    // } 

    // //get phan trang cho search
    // public function searchAction()
    // {   
    //     $key = $page = $this->getRequest()->getQuery('qq');
    //     $data = $this->getPostTable()->fetchPostSearch($key);
    //     $arr = $this->object_to_array($data);
    //     $details = $this->unique_multidim_array($arr,'id'); 
    //     return new ViewModel(array(
    //         'keysearch'  =>  $key,
    //         'postsearch' => $details,
    //     ));
    // }

}
