<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

 use Zend\Mvc\Controller\AbstractActionController;
 use Zend\View\Model\ViewModel;
 use Zend\Mvc\MvcEvent;
 use Admin\Model\Server;         
 use Admin\Form\ServerForm;       
 use Zend\Validator\File\Size;
 use Zend\Db\Sql\Sql;
  use Admin\Model\Savelog;

 class ServerController extends AbstractActionController
 {
    protected $serverTable;
    protected $productTable;
    protected $savelogTable;
	protected $username;
	public function getAuthService()
	{
		$this->authservice = $this->getServiceLocator()->get('AuthService'); 
		return $this->authservice;  
	}
	 
    public function onDispatch(MvcEvent $e)
    {
        if (! $this->getServiceLocator()->get('AuthService')->hasIdentity()){
            return $this->redirect()->toRoute('login');
        }
		$this->username = $this->getAuthService()->getStorage()->read();
        return parent::onDispatch($e);
    }
	public function getSavelogTable()
    {
        if (!$this->savelogTable) {
            $sm = $this->getServiceLocator();
            $this->savelogTable = $sm->get('Admin\Model\SavelogTable');
        }
        return $this->savelogTable;
    }
    public function getServerTable()
    {
        if (!$this->serverTable) {
            $sm = $this->getServiceLocator();
            $this->serverTable = $sm->get('Admin\Model\ServerTable');
        }
        return $this->serverTable;
    }

    public function getProductTable()
    {
        if (!$this->productTable) {
            $sm = $this->getServiceLocator();
            $this->productTable = $sm->get('Admin\Model\ProductTable');
        }
        return $this->productTable;
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

    public function indexAction()
    {
        //return list form
        $list_server = $this->getServerTable()->fetchAll();
        $list_form = array();
        foreach($list_server as $server){
            $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
            $form = new ServerForm ($dbAdapter);
            $form->bind($server);
            $form->get('submit')->setAttribute('value', 'Cập nhập');
            array_push($list_form, $form);
        }
        
        return new ViewModel(array(
            'list_form' => $list_form,
            'servers' => $this->getServerTable()->fetchAll(),
            'products' => $this->getProductTable()->fetchAll()
        ));
    }

    public function updateStatusAction() {
        $request = $this->getRequest();
        $response = $this->getResponse();
        if ($request->isPost()) {
            $post = $request->getPost();
            $arrXutno = $post['arrXutno'];
            $type = (Int)$post['type'];
            if (!$this->getServerTable()->updateStatusAll($arrXutno, $type))
                $response->setContent(\Zend\Json\Json::encode(array('response' => false)));
            else {
                $response->setContent(\Zend\Json\Json::encode(array('response' => true)));
            }
        }
        return $response;
    }
    
    public function addAction()
    {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new ServerForm ($dbAdapter);
        $form->get('submit')->setValue('Thêm');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $server = new Server();
            $form->setInputFilter($server->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $server->exchangeArray($form->getData());
                //get agent name
                $sql = new Sql($dbAdapter);
                $select = $sql->select();
                $select->from('product');
                $select->where(array('id' => $server->product_id))->limit(1);
                $statement = $sql->prepareStatementForSqlObject($select);
                $result = $statement->execute();
                $result = $this->object_to_array($result);
                $agent = 'null';
                foreach($result as $row){
                    $agent = $row['agent'];
                }
                $this->getServerTable()->saveServer($server, $agent);
				$ip = $request->getServer('REMOTE_ADDR'); 
				$this->getSavelogTable()->saveLogAction($this->username, 'Add '.$agent.'_'.$server->server_name,'AddServer',$ip);
                return $this->redirect()->toRoute('adminserver');
            }
        }
        return array('form' => $form);
    }

     public function editAction()
     {
         $id = (int) $this->getRequest()->getPost('id', null);
         if (!$id) {
             return $this->redirect()->toRoute('adminserver', array(
                 'action' => 'add'
             ));
         }
         try {
             $server = $this->getServerTable()->getServer($id);
         }
         catch (\Exception $ex) {
             return $this->redirect()->toRoute('adminserver', array(
                 'action' => 'index'
             ));
         }
         $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
         $form = new ServerForm ($dbAdapter);
         $form->bind($server);
         $form->get('submit')->setAttribute('value', 'Chỉnh sửa');
         $request = $this->getRequest();
         if ($request->isPost()) {
             $form->setInputFilter($server->getInputFilter());
             $form->setData($request->getPost());
             if ($form->isValid()) {
                 //get agent name
                $sql = new Sql($dbAdapter);
                $select = $sql->select();
                $select->from('product');
                $select->where(array('id' => $server->product_id))->limit(1);
                $statement = $sql->prepareStatementForSqlObject($select);
                $result = $statement->execute();
                $result = $this->object_to_array($result);
                $agent = 'null';
                foreach($result as $row){
                    $agent = $row['agent'];
                }
                 $this->getServerTable()->editServer($server, $agent);
				 $ip = $request->getServer('REMOTE_ADDR'); 
				$this->getSavelogTable()->saveLogAction($this->username, 'Edit '.$agent.'_'.$server->server_name,'EditServer',$ip);
                 return $this->redirect()->toRoute('adminserver');
             }
         }
         return array(
             'id' => $id,
             'form' => $form,
         );
     }

     public function deleteAction()
     {
         $id = (int) $this->params()->fromRoute('id', 0);
         if (!$id) {
             return $this->redirect()->toRoute('adminserver');
         }

         $request = $this->getRequest();
         if ($request->isPost()) {
             $del = $request->getPost('del', 'No');

             if ($del == 'Yes') {
                 $id = (int) $request->getPost('id');
                 $this->getServerTable()->deleteServer($id);
				 $ip = $request->getServer('REMOTE_ADDR'); 
				$this->getSavelogTable()->saveLogAction($this->username, 'Delete server','DeleteServer',$ip);
             }
             return $this->redirect()->toRoute('adminserver');
         }

         return array(
             'id'    => $id,
             'server' => $this->getServerTable()->getServer($id)
         );
     }
 }