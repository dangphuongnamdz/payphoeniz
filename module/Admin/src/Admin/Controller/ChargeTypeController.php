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
 use Admin\Model\ChargeType; 
 use Admin\Model\Savelog;
 use Admin\Form\ChargeTypeForm;       

 class ChargeTypeController extends AbstractActionController
 {
    protected $chargeTypeTable;
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
    
    public function getChargeTypeTable()
    {
        if (!$this->chargeTypeTable) {
            $sm = $this->getServiceLocator();
            $this->chargeTypeTable = $sm->get('Admin\Model\ChargeTypeTable');
        }
        return $this->chargeTypeTable;
    }
    
    public function indexAction()
    {
        return new ViewModel(array(
            "chargeTypes" => $this->getChargeTypeTable()->fetchAll(),
        ));
    }

    public function addAction()
    {
        $form = new ChargeTypeForm();
        $form->get('submit')->setValue('Thêm');
        $message = '';
        $request = $this->getRequest();
        if ($request->isPost()) {
            $chargeType = new ChargeType();
            $form->setInputFilter($chargeType->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $chargeType->exchangeArray($form->getData());
                $this->getChargeTypeTable()->saveChargeType($chargeType);
                $this->flashMessenger()->addSuccessMessage('Thêm thành công');
				$ip = $request->getServer('REMOTE_ADDR'); 
				$this->getSavelogTable()->saveLogAction($this->username, 'Add '.$chargeType->name,'AddChargeType',$ip);
                return $this->redirect()->toRoute('adminchargetype');
            }
        }
        return array('form' => $form, 'message' => $message);
    }


    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('adminchargetype', array(
                'action' => 'add'
            ));
        }
        try {
            $chargeType = $this->getChargeTypeTable()->getChargeType($id);
        }
        catch (\Exception $ex) {
            return $this->redirect()->toRoute('adminchargetype', array(
                'action' => 'index'
            ));
        }

        $form  = new ChargeTypeForm();
        $form->bind($chargeType);
        $form->get('submit')->setAttribute('value', 'Chỉnh sửa');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($chargeType->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getChargeTypeTable()->saveChargeType($chargeType);
				$ip = $request->getServer('REMOTE_ADDR'); 
				$this->getSavelogTable()->saveLogAction($this->username, 'Edit '.$chargeType->name,'EditChargeType',$ip);
                return $this->redirect()->toRoute('adminchargetype');
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
             return $this->redirect()->toRoute('adminchargetype');
         }

         $request = $this->getRequest();
         if ($request->isPost()) {
             $del = $request->getPost('del', 'No');

             if ($del == 'Yes') {
                 $id = (int) $request->getPost('id');
				 $name = $request->getPost('name');
                 $this->getChargeTypeTable()->deleteChargeType($id);
				 $ip = $request->getServer('REMOTE_ADDR'); 
				$this->getSavelogTable()->saveLogAction($this->username, 'Delete '.$name,'DeleteChargeType',$ip);
             }
             return $this->redirect()->toRoute('adminchargetype');
         }

         return array(
             'id'    => $id,
             'chargetype' => $this->getChargeTypeTable()->getChargeType($id)
         );
     }
 }