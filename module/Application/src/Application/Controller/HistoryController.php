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
use Application\Form\LogCards\CardHistoryForm;   
use Application\Form\LogCards\PayHistoryForm;   
use Zend\Session\Container;

 class HistoryController extends AbstractActionController
 {
    protected $historyTable;
    protected $productTable;
    protected $serverTable;
    protected $menu;
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

    public function getHistoryTable()
    {
        if (!$this->historyTable) {
            $sm = $this->getServiceLocator();
            $this->historyTable = $sm->get('Application\Model\HistoryTable');
        }
        return $this->historyTable;
    }

    public function getProductTable()
    {
        if (!$this->productTable) {
            $sm = $this->getServiceLocator();
            $this->productTable = $sm->get('Application\Model\ProductTable');
        }
        return $this->productTable;
    }

    public function getServerTable()
    {
        if (!$this->serverTable) {
            $sm = $this->getServiceLocator();
            $this->serverTable = $sm->get('Application\Model\ServerTable');
        }
        return $this->serverTable;
    }


    public function cardGetHistoryAction()
    {
        $this->layout('layout/passport');
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new CardHistoryForm($dbAdapter);
        $form->get('submit')->setValue('Search');
        $request = $this->getRequest();
        $parameters['in_username'] = "'".$this->getAuthServices()->getIdentity()."'";
        //set null for param
        $parameters['in_transaction'] = 'null';
        $parameters['in_status'] = 'null';
        $parameters['in_type'] = 'null';
        $parameters['in_serial'] = 'null';
        $parameters['in_code'] = 'null';
        $parameters['in_product_id'] = 'null';    
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $date = date('Y-m-d');        
        $parameters['fromDate'] = str_replace('-','',$date);
        $parameters['toDate'] = str_replace('-','',$date);
        $result = $this->getHistoryTable()->getCardGetHistory($parameters);
        if ($request->isPost()) {
            $time = $request->getPost('in_time');
            $time = explode('-',$time);
            $fromDate = str_replace('-','',date('Y-m-d',strtotime($time[0])));
            $toDate = 	str_replace('-','',date('Y-m-d',strtotime($time[1])));
            ///////////////////////////////////////////////////
            $parameters['in_transaction'] =   ($request->getPost('in_transaction') == '') ? 'null'     : "'".$request->getPost('in_transaction')."'";
            $parameters['in_status']      =   ($request->getPost('in_status') == '')      ? 'null'     : "'".$request->getPost('in_status')."'";
            $parameters['in_type']        =   ($request->getPost('in_type') == '')        ? 'null'     : "'".$request->getPost('in_type')."'" ;
            $parameters['in_serial']      =   ($request->getPost('in_serial') == '')      ? 'null'     : "'".$request->getPost('in_serial')."'";
            $parameters['in_code']        =   ($request->getPost('in_code') == '')      ? 'null'     : "'".$request->getPost('in_code')."'";
            $parameters['in_product_id']  =   ($request->getPost('in_product_id') == '')  ? 'null'     : "'".$request->getPost('in_product_id')."'";
            $parameters['fromDate'] = $fromDate;
            $parameters['toDate'] = $toDate;
            ///////////////////////////////////////////////////
            $result = $this->getHistoryTable()->getCardGetHistory($parameters);
            $form->setData($request->getPost());
        }
        return (array(
            'result' => $result,
            "products" => $this->object_to_array($this->getProductTable()->fetchAll()),
            'form' => $form
        ));
    }

    public function payGetHistoryAction()
    {
        $this->layout('layout/passport');
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new PayHistoryForm ($dbAdapter);
        $form->get('submit')->setValue('Search');
        $request = $this->getRequest();
        $parameters['in_username'] = "'".$this->getAuthServices()->getIdentity()."'";
        //set null for param
        $parameters['in_role'] = 'null';
        $parameters['in_transaction'] = 'null';
        $parameters['in_status'] = 'null';
        $parameters['in_type'] = 'null';
        $parameters['in_serial'] = 'null';
        $parameters['in_code'] = 'null';
        $parameters['in_server'] = 'null';
        $parameters['in_product_id'] = 'null';
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $current_date = date('Y-m-d');        
        $parameters['fromDate'] = str_replace('-','',$current_date);
        $parameters['toDate'] = str_replace('-','',$current_date);
        ///////////////////////////////////////////////////
        $result = $this->getHistoryTable()->getPayGetHistory($parameters); 
        if ($request->isPost()) {
            $time = $request->getPost('in_time');
            $time = explode('-',$time);
            $fromDate = str_replace('-','',date('Y-m-d',strtotime($time[0])));
            $toDate = 	str_replace('-','',date('Y-m-d',strtotime($time[1])));
            ///////////////////////////////////////////////////GET DATA PARAM 
            $parameters['in_role']        =   ($request->getPost('in_role') == '') ? 'null'     : "'".$request->getPost('in_role')."'";
            $parameters['in_transaction'] =   ($request->getPost('in_transaction') == '') ? 'null'     : "'".$request->getPost('in_transaction')."'";
            $parameters['in_status']      =   ($request->getPost('in_status') == '')      ? 'null'     : "'".$request->getPost('in_status')."'";
            $parameters['in_type']        =   ($request->getPost('in_type') == '')        ? 'null'     : "'".$request->getPost('in_type')."'" ;
            $parameters['in_serial']      =   ($request->getPost('in_serial') == '')      ? 'null'     : "'".$request->getPost('in_serial')."'";
            $parameters['in_code']        =   ($request->getPost('in_code') == '')      ? 'null'     : "'".$request->getPost('in_code')."'";
            $parameters['in_server']      =   ($request->getPost('in_server') == '')      ? 'null'     : "'".$request->getPost('in_server')."'";
            $parameters['in_product_id']  =   ($request->getPost('in_product_id') == '')  ? 'null'     : "'".$request->getPost('in_product_id')."'";
            $parameters['fromDate'] = $fromDate;
            $parameters['toDate'] = $toDate;
            ///////////////////////////////////////////////////
            $result = $this->getHistoryTable()->getPayGetHistory($parameters); 
            $form->setData($request->getPost());
        }
        return (array(
            'result'    => $result,
            "products" => $this->object_to_array($this->getProductTable()->fetchAll()),
            "servers" => $this->object_to_array($this->getServerTable()->fetchAll()),
            'form'      => $form
        ));
    }

 }