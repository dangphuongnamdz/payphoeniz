<?php

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Admin\Form\LoginForm;
use Admin\Model\User;         
use Admin\Form\UserForm;  
use Zend\View\Model\JsonModel;

//send mail
use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mail\Transport\Sendmail as SendmailTransport;

class AuthController extends AbstractActionController
{
    protected $form;
    protected $storage;
    protected $authservice;
    public $userTable;

    public function getAuthService()
    {
        if (! $this->authservice) {
            $this->authservice = $this->getServiceLocator()
                                      ->get('AuthService');
        }
        return $this->authservice;
    }

    public function getSessionStorage()
    {
        if (! $this->storage) {
            $this->storage = $this->getServiceLocator()
                                  ->get('Admin\Model\MyAuthStorage');
        }
        return $this->storage;
    }

    public function getForm()
    {
        if (! $this->form) {
            $loginform       = new LoginForm();
            $builder    = new AnnotationBuilder();
            $this->form = $builder->createForm($loginform);
        }
        return $this->form;
    }

    public function loginAction()
    {
        if ($this->getAuthService()->hasIdentity()){
            return $this->redirect()->toRoute('admin');
        }
        $form       = $this->getForm(); 
        $this->layout('admin/auth');
        return array(
            'form'      => $form,
            'messages'  => $this->flashmessenger()->getMessages()
        );
    }

    public function authenticateAction()
    {
        $form       = $this->getForm();
        $redirect = 'login';
        $request = $this->getRequest();
        if ($request->isPost()){
            $form->setData($request->getPost());
            if ($form->isValid()){
                $this->getAuthService()->getAdapter()
                                       ->setIdentity($request->getPost('username'))
                                       ->setCredential($request->getPost('password'));
                $result = $this->getAuthService()->authenticate();
                foreach($result->getMessages() as $message)
                {
                    $this->flashmessenger()->addMessage("Sai thông tin đăng nhập");
                }
                if ($result->isValid()) {
                    $redirect = 'admin';
                    if ($request->getPost('rememberme') == 1 ) {
                        $this->getSessionStorage()
                             ->setRememberMe(1);
                        $this->getAuthService()->setStorage($this->getSessionStorage());
                    }
                    $this->getAuthService()->getStorage()->write($request->getPost('username'));
                    $resultRow = $this->getAuthService()->getAdapter()->getResultRowObject();
                    //save session
                    $session = new Container('User');
                    $session->offsetSet('user_name', $request->getPost('username'));
                    $session->offsetSet('id_user', $resultRow->id);
                    $session->offsetSet('role_agent', $resultRow->role_agent);
                    $session->offsetSet('level', $resultRow->level);
                }
            }
        }
        return $this->redirect()->toRoute($redirect);
    }

    public function logoutAction()
    {
        $this->getSessionStorage()->forgetMe();
        $this->getAuthService()->clearIdentity();
        //clear session
        $sessionUser = new Container('User');
        $sessionUser->offsetUnset('user_name');
        $sessionUser->offsetUnset('role_agent');
        $sessionUser->offsetUnset('level');
        $sessionUser->offsetUnset('id_user');
        $this->flashmessenger()->addMessage("Bạn đã logout");
        return $this->redirect()->toRoute('login');
    }

    public function getUserTable()
    {
        if (!$this->userTable) {
            $sm = $this->getServiceLocator();
            $this->userTable = $sm->get('Admin\Model\UserTable');
        }
        return $this->userTable;
    }

    //send mail
    public function sendmail($email, $tokenKey){
        $html = new MimePart('Xin chào '.$email.'.'
        .'<br> Vui lòng tạo link để khôi phục mật khẩu '
        .'<br> Sử dụng tên miền + link sau: /admin/setpassword/'.$tokenKey.' để set password mới'
        .'<br> Xin cám ơn');
        $html->type = "text/html";
        $body = new MimeMessage();
        $body->addPart($html);

        $message = new Message();
        $message->addTo($email)
                ->addFrom('resetpassword@admin.com')
                ->setSubject('Email reset password admin')
                ->setBody($body);
                
        //SendmailTransport
        $transport = new SendmailTransport();
        $transport->send($message);
    }

    public function resetpassswordAction()
    {
        $email = $this->params()->fromRoute('email', null);
        die(var_dump($email));
        if($this->getUserTable()->getUserByEmail($email)){
            //tao token va gui mail
            $tokenKey = $this->getUserTable()->createTokenPasswordReset($email);
            $this->sendmail($email, $tokenKey);
            $result = 'Token reset password vừa mới được gửi vào mail (kiểm tra spam nếu không tìm thấy)';
        }else{
            $result = 'Email not exist';
        }
        return new JsonModel (array(
            'result' => $result
        ));

    }
    public function setpasswordAction(){
        $token = $this->params()->fromRoute('token',null);
        $this->layout('admin/auth');
        if($token == null || strlen($token)!=32){
            throw new \Exception("Token không hợp lệ");
        }
        if($this->getUserTable()->getUserByPasswordToken($token)==false){
            throw new \Exception("Token không hợp lệ hoặc đã hết hạn sử dụng. Vui lòng thử lại");
        }
        
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new UserForm($dbAdapter);
        $form->get('submit')->setValue('Change');
        if($this->getRequest()->isPost()){
            $data = $this->params()->fromPost();
            $form->setData($data);
            if($form->isValid()){
                if($this->getUserTable()->changePassword($data['newpassword'])){
                    $this->flashMessenger()->addSuccessMessage('Đổi mật khẩu thành công');
                    return $this->redirect()->toRoute('login');
                }
                else{
                    $this->flashMessenger()->addErrorMessage('Lỗi! Không thể đổi password');
                }
            }
        }
        return new ViewModel(array(
            'messages'  => $this->flashmessenger()->getMessages(),
            'form'=>$form
        ));
    }
    
}