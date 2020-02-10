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
 use Admin\Model\Gold;         
 use Admin\Form\GoldForm;
 use Admin\Model\Savelog; 

 class GoldController extends AbstractActionController
 {
    protected $goldTable;
    protected $productTable;
    protected $savelogTable;
	protected $username;
	public function getAuthService()
	{
		$this->authservice = $this->getServiceLocator()->get('AuthService'); 
		return $this->authservice;  
	}
	public function getSavelogTable()
    {
        if (!$this->savelogTable) {
            $sm = $this->getServiceLocator();
            $this->savelogTable = $sm->get('Admin\Model\SavelogTable');
        }
        return $this->savelogTable;
    } 
    public function onDispatch(MvcEvent $e)
    {
        if (! $this->getServiceLocator()->get('AuthService')->hasIdentity()){
            return $this->redirect()->toRoute('login');
        }
		$this->username = $this->getAuthService()->getStorage()->read();
        return parent::onDispatch($e);
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
    public function getProductTable()
    {
        if (!$this->productTable) {
            $sm = $this->getServiceLocator();
            $this->productTable = $sm->get('Admin\Model\ProductTable');
        }
        return $this->productTable;
    }

    public function getGoldTable()
    {
        if (!$this->goldTable) {
            $sm = $this->getServiceLocator();
            $this->goldTable = $sm->get('Admin\Model\GoldTable');
        }
        return $this->goldTable;
    }
    
    public function indexAction()
    {
        return new ViewModel(array(
            'gold' => $this->getGoldTable()->fetchAll(),
            "products" => $this->object_to_array($this->getProductTable()->fetchAll()),
        ));
    }

    public function addAction()
    {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new GoldForm($dbAdapter); 
        $form->get('submit')->setValue('Thêm');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $gold = new Gold();
            $form->setInputFilter($gold->getInputFilter());
            
            $nonFile = $request->getPost()->toArray();
            $File    = $this->params()->fromFiles('avatar');
            $data = array_merge(
                 $nonFile, //POST 
                 array('avatar'=> $File['name']) //FILE...
             );
             $newName = "";
             if($File['name']!=''){
                $adapter = new \Zend\File\Transfer\Adapter\Http(); 
                $adapter->setDestination(dirname(__DIR__).'/../../../../public/img/iconcoin');
                $destination = dirname(__DIR__).'/../../../../public/img/iconcoin';

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
                 $gold->exchangeArray($form->getData());
                 $this->getGoldTable()->saveGold($gold, $newName);
				 $ip = $request->getServer('REMOTE_ADDR'); 
				$this->getSavelogTable()->saveLogAction($this->username, 'Add '.$gold->product_id.'-'.$gold->product_gold_id,'AddGold',$ip);
                 return $this->redirect()->toRoute('admingold');
             }
        }
        return array('form' => $form);
    }


    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('admingold', array(
                'action' => 'add'
            ));
        }
        try {
            $gold = $this->getGoldTable()->getGold($id);
        }
        catch (\Exception $ex) {
            return $this->redirect()->toRoute('admingold', array(
                'action' => 'index'
            ));
        }

        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new GoldForm($dbAdapter);
        $form->bind($gold);
        $form->get('submit')->setAttribute('value', 'Chỉnh sửa');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($gold->getInputFilter());
           
            $nonFile = $request->getPost()->toArray();
            $File    = $this->params()->fromFiles('avatar');
            $data = array_merge(
                 $nonFile, //POST 
                 array('avatar'=> $File['name']) //FILE...
             );
             $newName = "";
            if($File['name']!=''){
                $adapter = new \Zend\File\Transfer\Adapter\Http(); 
                unlink(dirname(__DIR__).'/../../../../public/img/iconcoin/'.$gold->image);
                $adapter->setDestination(dirname(__DIR__).'/../../../../public/img/iconcoin');
                $destination = dirname(__DIR__).'/../../../../public/img/iconcoin';

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
                $this->getGoldTable()->saveGold($gold, $newName);
				$ip = $request->getServer('REMOTE_ADDR'); 
				$this->getSavelogTable()->saveLogAction($this->username, 'Edit '.$gold->product_id.'-'.$gold->product_gold_id,'EditGold',$ip);
                return $this->redirect()->toRoute('admingold');
            }
        }

        return array(
            'id' => $id,
            'avatar' => $gold->image,
            'form' => $form,
        );
    }

     public function deleteAction()
     {
         $id = (int) $this->params()->fromRoute('id', 0);
         if (!$id) {
             return $this->redirect()->toRoute('admingold');
         }

         $request = $this->getRequest();
         if ($request->isPost()) {
             $del = $request->getPost('del', 'No');

             if ($del == 'Yes') {
                 $id = (int) $request->getPost('id');
				 $product_id = $request->getPost('product_id');
				 $product_gold_id = $request->getPost('gold_id');
                 $this->getGoldTable()->deleteGold($id);
				 $ip = $request->getServer('REMOTE_ADDR'); 
				$this->getSavelogTable()->saveLogAction($this->username, 'Add '.$product_id.'-'.$product_gold_id,'AddGold',$ip);
             }

             return $this->redirect()->toRoute('admingold');
         }

         return array(
             'id'    => $id,
             'gold' => $this->getGoldTable()->getGold($id)
         );
     }
 }