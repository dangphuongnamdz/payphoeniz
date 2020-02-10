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
 use Admin\Model\Product;         
 use Admin\Form\ProductForm;
 use Admin\Model\Savelog; 

 class ProductController extends AbstractActionController
 {
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
    public function getProductTable()
    {
        if (!$this->productTable) {
            $sm = $this->getServiceLocator();
            $this->productTable = $sm->get('Admin\Model\ProductTable');
        }
        return $this->productTable;
    }
    
    public function indexAction()
    {
        return new ViewModel(array(
            'product' => $this->getProductTable()->fetchAll()
        ));
    }

    public function addAction()
    {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new ProductForm($dbAdapter); 
        $form->get('submit')->setValue('Thêm');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $product = new Product();
            $form->setInputFilter($product->getInputFilter());
            
            $nonFile = $request->getPost()->toArray();
            $File    = $this->params()->fromFiles('avatar');
            $data = array_merge(
                 $nonFile, //POST 
                 array('avatar'=> $File['name']) //FILE...
             );
             $newName = "";
             if($File['name']!=''){
                $adapter = new \Zend\File\Transfer\Adapter\Http(); 
                $adapter->setDestination(dirname(__DIR__).'/../../../../public/img/icon');
                $destination = dirname(__DIR__).'/../../../../public/img/icon';

                $ext = pathinfo($File['name'], PATHINFO_EXTENSION);
                $newName = md5(rand(). $File['name']) . '.' . $ext;
                $adapter->addFilter('File\Rename', array(
                    'target' => $destination . '/' . $newName,
                ));

                if ($adapter->receive($File['name'])) {
                //success
                }
             }
             $form->setData($request->getPost());
             
             if ($form->isValid()) {
                 $product->exchangeArray($form->getData());
                 $this->getProductTable()->saveProduct($product, $newName);
				 $ip = $request->getServer('REMOTE_ADDR'); 
				$this->getSavelogTable()->saveLogAction($this->username, 'Add '.$product->name,'AddProduct',$ip);
                 return $this->redirect()->toRoute('adminproduct');
             }
        }
        return array('form' => $form);
    }


    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('adminproduct', array(
                'action' => 'add'
            ));
        }
        try {
            $product = $this->getProductTable()->getProduct($id);
        }
        catch (\Exception $ex) {
            return $this->redirect()->toRoute('adminproduct', array(
                'action' => 'index'
            ));
        }

        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new ProductForm($dbAdapter);
        $form->bind($product);
        $form->get('submit')->setAttribute('value', 'Chỉnh sửa');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($product->getInputFilter());
           
            $nonFile = $request->getPost()->toArray();
            $File    = $this->params()->fromFiles('avatar');
            $data = array_merge(
                 $nonFile, //POST 
                 array('avatar'=> $File['name']) //FILE...
             );
             $newName = "";
            if($File['name']!=''){
                $adapter = new \Zend\File\Transfer\Adapter\Http(); 
                unlink(dirname(__DIR__).'/../../../../public/img/icon/'.$product->avatar);
                $adapter->setDestination(dirname(__DIR__).'/../../../../public/img/icon');
                $destination = dirname(__DIR__).'/../../../../public/img/icon';

                $ext = pathinfo($File['name'], PATHINFO_EXTENSION);
                $newName = md5(rand(). $File['name']) . '.' . $ext;
                $adapter->addFilter('File\Rename', array(
                    'target' => $destination . '/' . $newName,
                ));

                if ($adapter->receive($File['name'])) {
                //success
                }
            }
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                $this->getProductTable()->saveProduct($product, $newName);
				$ip = $request->getServer('REMOTE_ADDR'); 
				$this->getSavelogTable()->saveLogAction($this->username, 'Edit '.$product->name,'EditProduct',$ip);
                return $this->redirect()->toRoute('adminproduct');
            }
        }

        return array(
            'id' => $id,
            'avatar' => $product->avatar,
            'form' => $form,
        );
    }

    public function sortAction() {
        $request = $this->getRequest();
        $response = $this->getResponse();
        if ($request->isPost()) {
            $post_data = $request->getPost();
            $desired_position = $post_data['desired_position'];
            $current_position = $post_data['current_position'];
            $id_current_position = $post_data['id_current_position'];
            // $id_desired_position = $post_data['id_desired_position'];
            $result = $this->getProductTable()->saveSort($desired_position, $current_position, $id_current_position);
            if ($result==true)
                $response->setContent(\Zend\Json\Json::encode(array('response' => true)));
            else {
                $response->setContent(\Zend\Json\Json::encode(array('response' => false)));
            }
        }
        return $response;
    }


     public function deleteAction()
     {
         $id = (int) $this->params()->fromRoute('id', 0);
         if (!$id) {
             return $this->redirect()->toRoute('adminproduct');
         }

         $request = $this->getRequest();
         if ($request->isPost()) {
             $del = $request->getPost('del', 'No');

             if ($del == 'Yes') {
                 $id = (int) $request->getPost('id');
				 $name = (int) $request->getPost('name');
                 $this->getProductTable()->deleteProduct($id);
				 $ip = $request->getServer('REMOTE_ADDR'); 
				$this->getSavelogTable()->saveLogAction($this->username, 'Delete '.$name,'DeleteProduct',$ip);
             }

             return $this->redirect()->toRoute('adminproduct');
         }

         return array(
             'id'    => $id,
             'product' => $this->getProductTable()->getProduct($id)
         );
     }
 }