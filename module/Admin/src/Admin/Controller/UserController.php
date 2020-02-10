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
 use Admin\Model\User;         
 use Admin\Form\UserForm;       
 use Zend\Validator\File\Size;
 use Zend\Session\Container;
 use Admin\Model\Savelog;


 class UserController extends AbstractActionController
 {
    protected $userTable;
    

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
    public function getUserTable()
    {
        if (!$this->userTable) {
            $sm = $this->getServiceLocator();
            $this->userTable = $sm->get('Admin\Model\UserTable');
        }
        return $this->userTable;
    }
    
    public function indexAction()
    {
        $paginator = $this->getUserTable()->fetchAllUser(true); 
        $paginator->setCurrentPageNumber((int) $this->params()->fromQuery('page', 1));
        $paginator->setItemCountPerPage(10);
        return new ViewModel(array(
            'paginator' => $paginator
        ));
    }

    public function addAction()
    {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new UserForm($dbAdapter);
        $form->get('submit')->setValue('Thêm');
        $message = '';
        $request = $this->getRequest();
        if ($request->isPost()) {
            $user = new User();
            $form->setInputFilter($user->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $user->exchangeArray($form->getData());
                $pattern = '/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$/';
                if(!preg_match($pattern, $user->password)){
                    $message = 'Mật khẩu phải phải từ 6 ký tự, có chữ hoa, chữ thường, số, chấp nhận một số ký tự đặc biệt';
                    return array('form' => $form, 'message' => $message);
                }
                $this->getUserTable()->saveUser($user);
				$ip = $request->getServer('REMOTE_ADDR'); 
				$this->getSavelogTable()->saveLogAction($this->username, 'Add '.$user->username,'AddUser',$ip);
                $this->flashMessenger()->addSuccessMessage('Thêm thành công');
                return $this->redirect()->toRoute('adminuser');
            }
        }
        return array('form' => $form, 'message' => $message);
    }


    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('adminuser', array(
                'action' => 'add'
            ));
        }
        try {
            $user = $this->getUserTable()->getUser($id);
        }
        catch (\Exception $ex) {
            return $this->redirect()->toRoute('adminuser', array(
                'action' => 'index'
            ));
        }

        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new UserForm($dbAdapter, $id);
        $form->bind($user);
        $form->get('submit')->setAttribute('value', 'Chỉnh sửa');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($user->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $this->getUserTable()->saveUser($user);
				$ip = $request->getServer('REMOTE_ADDR'); 
				$this->getSavelogTable()->saveLogAction($this->username, 'Edit '.$user->username,'EditUser',$ip);
                return $this->redirect()->toRoute('adminuser');
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
             return $this->redirect()->toRoute('adminuser');
         }

         $request = $this->getRequest();
         if ($request->isPost()) {
             $del = $request->getPost('del', 'No');

             if ($del == 'Yes') {
                 $id = (int) $request->getPost('id');
				 $name = $request->getPost('name');
                 $this->getUserTable()->deleteUser($id);
				 $ip = $request->getServer('REMOTE_ADDR'); 
				$this->getSavelogTable()->saveLogAction($this->username, 'Delete '.$name,'DeleteUser',$ip);
             }
             return $this->redirect()->toRoute('adminuser');
         }

         return array(
             'id'    => $id,
             'user' => $this->getUserTable()->getUser($id)
         );
     }

     public function resetpasswordAction()
     {
        $id = (int) $this->params()->fromRoute('id', 0);
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new UserForm($dbAdapter);
        $form->get('submit')->setValue('Reset mật khẩu');
        $request = $this->getRequest();
        $messager="";
        if ($request->isPost()) {
            $user = new User();
            $form->setInputFilter($user->getInputFilter());
            $form->setData($request->getPost());
            if (!$form->isValid()) {
                $pattern = '/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$/';
                $newpassword = $this->getRequest()->getPost('newpassword', null);
                $renewpassword = $this->getRequest()->getPost('renewpassword', null);
                if($newpassword != $renewpassword){
                    $messager =  "Xác nhận password không giống nhau";
                }
                else if(!preg_match($pattern, $newpassword)){
                    $messager = 'Mật khẩu phải phải từ 6 ký tự, có chữ hoa, chữ thường, số, chấp nhận một số ký tự đặc biệt';
                }
                else{
                    $this->getUserTable()->changePassword($newpassword, $id);
					$ip = $request->getServer('REMOTE_ADDR'); 
					$this->getSavelogTable()->saveLogAction($this->username, 'Reset pass userid '.$id,'Resetpass',$ip);
                    $messager = "Reset password thành công";
                }
            }
        }
        return new ViewModel(array(
            'id'    => $id,
            'messager'  => $messager,
            'form'=>$form
        ));
     }
 }