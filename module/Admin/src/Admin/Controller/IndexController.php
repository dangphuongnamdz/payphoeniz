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
use Admin\Model\User;         
use Admin\Form\UserForm;  
use Zend\Session\Container;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Adapter;
use Zend\Console\Request as ConsoleRequest;

class IndexController extends AbstractActionController
{

    public $userTable;
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

    public function thongkebaiviet(){
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $sql = new Sql($dbAdapter);
        $select = $sql->select();
        $select->columns(array(
            'label' => new \Zend\Db\Sql\Expression('DATE(created_at)'), 'y' => new \Zend\Db\Sql\Expression('COUNT(*)')
        ));
        $select->group (array(new \Zend\Db\Sql\Expression('DATE(created_at)')));
        $select->order('label desc')->limit(10);
        $select->from ("posts");
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $result;
    }

    public function thongkebaivietcategory(){
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $sql = new Sql($dbAdapter);
        $select = $sql->select();
        $select->join('category', 'category.id = posts.id_category', array(
            'label' => 'tendanhmuc', 'y' => new \Zend\Db\Sql\Expression('COUNT(*)')
        ), 'left');
        $select->group (array(new \Zend\Db\Sql\Expression('id_category')));
        $select->from ("posts");
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $result;
    }


    public function indexAction()
     {
        if (! $this->getServiceLocator()->get('AuthService')->hasIdentity()){
            return $this->redirect()->toRoute('login');
        }
        $thongkebaiviet = $this->thongkebaiviet();
        $thongkebaivietcategory = $this->thongkebaivietcategory();
        return new ViewModel(array(
            'thongkebaiviet'    => $this->object_to_array($thongkebaiviet),
            'thongkebaivietcategory'    => $this->object_to_array($thongkebaivietcategory),
        ));
     }     
     public function getUserTable()
     {
         if (!$this->userTable) {
             $sm = $this->getServiceLocator();
             $this->userTable = $sm->get('Admin\Model\UserTable');
         }
         return $this->userTable;
     }

     public function changepasswordAction()
     {
        if (! $this->getServiceLocator()->get('AuthService')->hasIdentity()){
            return $this->redirect()->toRoute('login');
        }
        $session = new Container('User');
        $idUser= $session->offsetGet('id_user');
        $result_user = $this->getUserTable()->getUser($idUser);
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new UserForm($dbAdapter);
        $form->get('submit')->setValue('Change');
        $request = $this->getRequest();
        $messager="";
        if ($request->isPost()) {
            $user = new User();
            $form->setInputFilter($user->getInputFilter());
            $form->setData($request->getPost());
            if (!$form->isValid()) {
                $pattern = '/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$/';
                $getuser = $this->object_to_array($form->getData());
                $oldpassword = Md5($getuser['password']);
                if($getuser['newpassword'] != $getuser['renewpassword']){
                    $messager =  "Xác nhận password không giống nhau";
                }
                else if($result_user->password != $oldpassword){
                    $messager =  "Nhập password cũ không chính xác";
                }
                else if(!preg_match($pattern, $getuser['newpassword'])){
                    $messager = 'Mật khẩu phải phải từ 6 ký tự, có chữ hoa, chữ thường, số, chấp nhận một số ký tự đặc biệt';
                }
                else{
                    $this->getUserTable()->changePassword($getuser['newpassword']);
                    $messager = "Đổi password thành công";
                }
            }
        }
        return new ViewModel(array(
            'messager'  => $messager,
            'form'=>$form
        ));
     }
}

