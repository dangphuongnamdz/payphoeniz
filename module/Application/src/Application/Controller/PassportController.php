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
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Application\Model\User;    
use Application\Form\UserForm;     
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Adapter;
use Zend\Session\Container;

use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;

use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;
use Zend\Mail\Transport\Sendmail as SendmailTransport;


class PassportController extends AbstractActionController
{
    protected $storage;
    protected $authservices;
    protected $userTable;
    protected $arrConfig;
    protected $menu;

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
            //get config passport
            $config = $this->getServiceLocator()->get('config');
            $this->arrConfig['agent'] = $config ['passport'] ['agent'];
            $this->arrConfig['key'] = $config ['passport'] ['key'];
            $this->arrConfig['encryptKey'] = $config ['passport'] ['encryptKey'];
            $this->arrConfig['secret'] = $config ['passport'] ['secret'];
            $this->arrConfig['domain'] = $config ['passport'] ['domain'];
            $this->arrConfig['email_sender'] = $config ['email'] ['sender'];
            $this->arrConfig['main_domain'] = $config ['domain'];
            $this->arrConfig['time_session'] = $config ['time_session'];
            $this->arrConfig['facebook_app_id'] = $config['social']['facebook']['appId'];
            $this->arrConfig['facebook_secret'] = $config['social']['facebook']['secret'];
            $this->arrConfig['facebook_graph_version'] = $config['social']['facebook']['graph_version'];
            $this->arrConfig['google_client_id'] = $config['social']['google']['google_client_id'];
            $this->arrConfig['google_client_secret'] = $config['social']['google']['google_client_secret'];
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

    public function getUserTable()
    {
        if (!$this->userTable) {
            $sm = $this->getServiceLocator();
            $this->userTable = $sm->get('Application\Model\UserTable');
        }
        return $this->userTable;
    }

    public function loginAction()
    {
        //check login
        if ($this->getAuthServices()->hasIdentity()){
            return $this->redirect()->toRoute('passport', array(
                'action' => 'edit'
            ));
        }
        if($this->params()->fromRoute('id')!=null)
            $id = (String) $this->params()->fromRoute('id');
        else
            $id = 'null';
        //login by 100d id
        $request = $this->getRequest(); 
        $messages = '';
        $form = new UserForm();
        $form->get('submit')->setValue('Đăng nhập');
        if ($request->isPost()) {
            $id = (String) $this->params()->fromRoute('id', null);
            $user = new User();
            $form->setInputFilter($user->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $user->exchangeArray($form->getData());
                $result = $this->getUserTable()->checkLoginPassport($user, $this->arrConfig);
                switch ($result['status']) {
                    case 1:
                        $this->getSessionStorage()->setRememberMe(1, $this->arrConfig['time_session']);
                        $this->getAuthServices()->setStorage($this->getSessionStorage());
                        $this->getAuthServices()->getStorage()->write($result['result']->username);
                        if($id == null)
                            return $this->redirect()->toRoute('home');
                        else
                            return $this->redirect()->toUrl($this->arrConfig['main_domain'].'/payment'.'/'.$id.".html");
                    case -17:
                        $messages = "Tên đăng nhập không tồn tại";
                        break;
                    case -18:
                        $messages = "Mật khẩu không đúng";
                        break;
                    default:
                        $messages = "Sai tên đăng nhập hoặc mật khẩu";
                        break;
                }
            }
        }
        $fb = new \Facebook\Facebook([
            'app_id' => $this->arrConfig['facebook_app_id'],
            'app_secret' => $this->arrConfig['facebook_secret'],
            'default_graph_version' => $this->arrConfig['facebook_graph_version'],
        ]);
        $helper = $fb->getRedirectLoginHelper();
        $permissions = [];
        $loginUrlFB = $helper->getLoginUrl($this->arrConfig['main_domain'].'/passport/fbCallback', $permissions);
        $loginUrlGG = 'https://accounts.google.com/o/oauth2/v2/auth?scope=' 
        . urlencode('https://www.googleapis.com/auth/userinfo.email') 
        . '&redirect_uri=' . urlencode($this->arrConfig['main_domain'].'/passport/ggCallback') . '&response_type=code&client_id=' . $this->arrConfig['google_client_id'] . '&access_type=offline';
        
        return array(
            'form'      => $form,
            'messages'  => $messages,
            'id'    => $id,
            'loginUrlFB'  => $loginUrlFB,
            'loginUrlGG'    => $loginUrlGG,
        );
    }

    public function fbCallbackAction(){
        $fb = new \Facebook\Facebook([
            'app_id' => $this->arrConfig['facebook_app_id'],
            'app_secret' => $this->arrConfig['facebook_secret'],
            'default_graph_version' => $this->arrConfig['facebook_graph_version'],
        ]);
        $helper = $fb->getRedirectLoginHelper();
        try {
            $accessToken = $helper->getAccessToken();
        } catch(\Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
        if (! isset($accessToken)) {
            if ($helper->getError()) {
              header('HTTP/1.0 401 Unauthorized');
              echo "Error: " . $helper->getError() . "\n";
              echo "Error Code: " . $helper->getErrorCode() . "\n";
              echo "Error Reason: " . $helper->getErrorReason() . "\n";
              echo "Error Description: " . $helper->getErrorDescription() . "\n";
            } else {
              header('HTTP/1.0 400 Bad Request');
              echo 'Bad request';
            }
            exit;
        }
        //  var_dump($accessToken->getValue());
        $oAuth2Client = $fb->getOAuth2Client();
        $tokenMetadata = $oAuth2Client->debugToken($accessToken);
        // echo '<h3>Metadata</h3>';
        //  var_dump($tokenMetadata);
        $tokenMetadata->validateAppId($this->arrConfig['facebook_app_id']);
        //$tokenMetadata->validateUserId('123');
        $tokenMetadata->validateExpiration();
        if (! $accessToken->isLongLived()) {
            try {
              $accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
            } catch (\Facebook\Exceptions\FacebookSDKException $e) {
              echo "<p>Error getting long-lived access token: " . $e->getMessage() . "</p>\n\n";
              exit;
            }
            // var_dump($accessToken->getValue());
        }
        try {
            $response = $fb->get(
                '/me?fields=token_for_business',
                $accessToken->getValue()
            );
        } catch(\Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
        $graphNode = $response->getGraphNode();
        $token_for_business = $graphNode->getField('token_for_business');
        // die(var_dump($token_for_business));
        $remote = new \Zend\Http\PhpEnvironment\RemoteAddress;
        $result = $this->getUserTable()->checkLoginPassportNoPass($this->arrConfig, $token_for_business, 'FB', $remote->getIpAddress());
        switch ($result->status) {
            case 1:
                $this->getSessionStorage()->setRememberMe(1, $this->arrConfig['time_session']);
                $this->getAuthServices()->setStorage($this->getSessionStorage());
                $this->getAuthServices()->getStorage()->write($result->result->username);
                return $this->redirect()->toRoute('home');
            default:
                $this->getResponse()->setStatusCode(404);
                return; 
        }
    }


    public function ggCallbackAction(){
        $client = new \Google_Client();
        $client->setClientId($this->arrConfig['google_client_id']);
        $client->setClientSecret($this->arrConfig['google_client_secret']);
        $client->setRedirectUri($this->arrConfig['main_domain'].'/passport/ggCallback');
        $client->setScopes(array('https://www.googleapis.com/auth/userinfo.email'));
        $code = $this->params()->fromQuery('code', null);
        if ($code == null){
            $auth_url = $client->createAuthUrl();
            return $this->redirect()->toUrl(filter_var($auth_url, FILTER_SANITIZE_URL));
          } else {
            $client->authenticate($code);
            if($client->getAccessToken()){
                $objOAuthService = new \Google_Service_Oauth2($client);
                $userData = $objOAuthService->userinfo->get();
                // die(var_dump($userData->email));
                $remote = new \Zend\Http\PhpEnvironment\RemoteAddress;
                $result = $this->getUserTable()->checkLoginPassportNoPass($this->arrConfig, $userData->email, 'GG', $remote->getIpAddress());
                switch ($result->status) {
                    case 1:
                        $this->getSessionStorage()->setRememberMe(1, $this->arrConfig['time_session']);
                        $this->getAuthServices()->setStorage($this->getSessionStorage());
                        $this->getAuthServices()->getStorage()->write($result->result->username);
                        return $this->redirect()->toRoute('home');
                    default:
                        $this->getResponse()->setStatusCode(404);
                        return; 
                }
            }else{
                $auth_url = $client->createAuthUrl();
                return $this->redirect()->toUrl(filter_var($auth_url, FILTER_SANITIZE_URL));
            }
        }
    }

    public function registerAction()
    {
        //check login
        if ($this->getAuthServices()->hasIdentity()){
            return $this->redirect()->toRoute('passport', array(
                'action' => 'edit'
            ));
        }

        $request = $this->getRequest(); 
        $this->layout('layout/passport');
        $messages = '';
        $server_id = '';
        $form = new UserForm();
        $form->get('submit')->setValue('Đăng ký');
        if ($request->isPost()) {
            $user = new User();
            $form->setInputFilter($user->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $user->exchangeArray($form->getData());
                $remote = new \Zend\Http\PhpEnvironment\RemoteAddress;
                $user->ip = $remote->getIpAddress();
                // $pattern = '/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$/';
                $pattern = '/^[A-Za-z0-9!#$%&*?@]{6,30}$/';
                if(!preg_match($pattern, $user->password))
                    $status = 202;
                else if(strlen($user->username) < 6)
                    $status = 203;
                else
                    $result = $this->getUserTable()->registerUserPassport($user, $this->arrConfig);
                switch ($result['status']) {
                    case 1:
                        $results = $this->getUserTable()->checkLoginPassport($user, $this->arrConfig);
                        if($results['status']==1){
                            $this->getSessionStorage()->setRememberMe(1, $this->arrConfig['time_session']);
                            $this->getAuthServices()->setStorage($this->getSessionStorage());
                            $this->getAuthServices()->getStorage()->write($result['result']->username);
                            if($this->params()->fromRoute('id')!=null)
                                $id = (String) $this->params()->fromRoute('id');
                            else
                                $id = 'null';
                            //success
                            if($id == 'null')
                                return $this->redirect()->toRoute('home');
                            else
                                return $this->redirect()->toUrl($this->arrConfig['main_domain'].'/payment'.'/'.$id.".html");
                        }
                        else{
                            $messages = "Lỗi hệ thống";
                            break;
                        }
                    case 0:
                        $messages = "Thông tin đại lý không đúng";
                        break;
                    case -1:
                        $messages = "Tên đăng nhập không hợp lệ";
                        break;
                    case -2:
                        $messages = "Mật khẩu không hợp lệ";
                        break;
                    case -3:
                        $messages = "Email không hợp lệ";
                        break;
                    case -4:
                        $messages = "Họ tên không hợp lệ";
                        break;
                    case -13:
                        $messages = "Tên đăng nhập đã tồn tại";
                        break;
                    case -14:
                        $messages = "Lỗi hệ thống";
                        break;
                    case -23:
                        $messages = "Email đã được đăng ký cho tài khoản khác";
                        break;
                    case 202:
                        $messages = "Mật khẩu không đúng định dạng";
                        break;
                    case 203:
                        $messages = "Tên đăng nhập phải từ 6 ký tự trở lên";
                        break;
                    case -100:
                        $messages = "Chữ ký không đúng";
                        break;
                    default:
                        $messages = "Lỗi không xác định";
                        break;
                }
            }
        }
        return array(
            'form'      => $form,
            'messages'  => $messages,
            'server_id'  => $server_id,
        );
    }

    //register full information
    public function registersAction()
    {
        //check login
        if ($this->getAuthServices()->hasIdentity()){
            return $this->redirect()->toRoute('passport', array(
                'action' => 'edit'
            ));
        }

        $request = $this->getRequest(); 
        $this->layout('layout/passport');
        $messages = '';
        $server_id = '';
        $form = new UserForm();
        $form->get('submit')->setValue('Đăng ký');
        if ($request->isPost()) {
            $user = new User();
            $form->setInputFilter($user->getInputFilter());
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $user->exchangeArray($form->getData());
                // $pattern = '/^\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$/';
                $pattern = '/^[A-Za-z0-9!#$%&*?@]{6,30}$/';
                //check birthday 
                $datePieces = explode("-",$user->birthday);
                if (((Int)date("Y") - intval($datePieces[0])) < 18)
                    $result['status'] = 201;
                else if(!preg_match($pattern, $user->password))
                    $result['status'] = 202;
                else if(strlen($user->username) < 6)
                    $status = 203;
                else
                    $result = $this->getUserTable()->registersUserPassport($user, $this->arrConfig);
                switch ($result['status']) {
                    case 1:
                        $messages = "Đăng ký thành công";
                        break;
                    case 0:
                        $messages = "Thông tin đại lý không đúng";
                        break;
                    case -1:
                        $messages = "Tên đăng nhập không hợp lệ";
                        break;
                    case -2:
                        $messages = "Mật khẩu không hợp lệ";
                        break;
                    case -3:
                        $messages = "Email không hợp lệ";
                        break;
                    case -4:
                        $messages = "Họ tên không hợp lệ";
                        break;
                    case -13:
                        $messages = "Tên đăng nhập đã tồn tại";
                        break;
                    case -14:
                        $messages = "Lỗi hệ thống";
                        break;
                    case -23:
                        $messages = "Email đã được đăng ký cho tài khoản khác";
                        break;
                    case 201:
                        $messages = "Bạn chưa đủ 18 tuổi";
                        break;
                    case 202:
                        $messages = "Mật khẩu phải từ 6 ký tự trở lên";
                        break;
                    case 203:
                        $messages = "Tên đăng nhập phải từ 6 ký tự trở lên";
                        break;
                    case -100:
                        $messages = "Chữ ký không đúng";
                        break;
                    default:
                        $messages = "Lỗi không xác định";
                        break;
                }
            }
        }
        return array(
            'form'      => $form,
            'messages'  => $messages,
            'server_id'  => $server_id,
        );
    }

    public function editAction()
    {
        //check login
        if (!$this->getAuthServices()->hasIdentity()){
            return $this->redirect()->toRoute('passport', array(
                'action' => 'login'
            ));
        }
        $messages = '';
        $result = $this->getUserTable()->getUserPassport($this->getAuthServices()->getIdentity(), $this->arrConfig);
        $user = new User();
        $user = $result['result'];
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form  = new UserForm($dbAdapter);
        $user->identityDate = substr($user->identityDate, 0, 10);
        $this->layout('layout/passport');
        $form->setHydrator(new \Zend\Stdlib\Hydrator\ObjectProperty());
        $form->bind($user);
        $email = $user->email;
        $form->get('submit')->setAttribute('value', 'Cập nhập');
        $form->get('username')->setAttribute('readonly', 'true');
        $current_email = $user->email;
        $current_mobile = $user->mobile;
        if($current_email!='')
            $form->get('email')->setAttribute('readonly', 'true');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $messages = '';
            $form->setData($request->getPost());
            $userForm = $request->getPost();
            if($current_email == '' && $userForm->email=='')
                $result['status'] = 101;
            else if($current_email != '' && $userForm->email!=$current_email)
                $result['status'] = 102;
            else{
                if (!filter_var($userForm->email, FILTER_VALIDATE_EMAIL)) {
                    $result['status'] = 103;
                }
                else if($current_mobile != '' && $userForm->mobile==$current_mobile){
                    $userForm->mobile = null;
                }
                if($current_email != '' && $userForm->email==$current_email)
                    $userForm->email = null;
                $result = $this->getUserTable()->updateUserPassport($userForm, $this->arrConfig);
            }
            switch ($result['status']) {
                case 1:
                    $messages = "Cập nhập thông tin thành công";
                    $form->get('email')->setAttribute('readonly', 'true');
                    break;
                case 0:
                    $messages = "Thông tin đại lý không đúng";
                    break;
                case -1:
                    $messages = "Tên đăng nhập không hợp lệ";
                    break;
                case -3:
                    $messages = "Email không hợp lệ";
                    break;
                case -4:
                    $messages = "Họ tên không hợp lệ";
                    break;
                case -5:
                    $messages = "Ngày sinh không hợp lệ";
                    break;
                case -6:
                    $messages = "Ngày sinh không hợp lệ";
                    break;
                case -7:
                    $messages = "CMND không hợp lệ";
                    break;
                case -8:
                    $messages = "Số điện thoại không hợp lệ";
                    break;
                case -9:
                    $messages = "Địa chỉ không hợp lệ";
                    break;
                case -10:
                    $messages = "Tỉnh/thành phố không hợp lệ";
                    break;
                case -11:
                    $messages = "Tên công ty không hợp lệ";
                    break;
                case -12:
                    $messages = "Địa chỉ công ty không hợp lệ";
                    break;
                case -13:
                    $messages = "Tên đăng nhập đã tồn tại";
                    break;
                case -14:
                    $messages = "Lỗi hệ thống";
                    break;
                case -17:
                    $messages = "Tên đăng nhập không tồn tại";
                    break;
                case -23:
                    $messages = "Email đã được đăng ký cho tài khoản khác";
                    break;
                case -24:
                    $messages = "Dữ liệu không thiếu hoặc không hợp lệ";
                    break;
                case -25:
                    $messages = "Ngày cấp CMND không hợp lệ";
                    break;
                case -26:
                    $messages = "Nơi cấp CMND không hợp lệ";
                    break;
                case -27:
                    $messages = "Địa chỉ IP không hợp lệ";
                    break;
                case -28:
                    $messages = "Số điện thoại đã được đăng ký cho tài khoản khác";
                    break;
                case 101:
                    $messages = "Thông tin của bạn chưa có địa chỉ email, bạn cần nhập email";
                    break;
                case 102:
                    $messages = "Bạn chỉ có thể thay đổi địa chỉ email được 1 lần duy nhất";
                    break;
                case 103:
                    $messages = "Email không đúng định dạng";
                    break;
                default:
                    $messages = "Cập nhập thông tin tài khoản thất bại";
                    break;
            }
        }

        return array(
            'form' => $form,
            'messages'  => $messages
        );
    }

    public function changepassAction()
    {
        //check login
        if (!$this->getAuthServices()->hasIdentity()){
            return $this->redirect()->toRoute('passport', array(
                'action' => 'login'
            ));
        }
        $request = $this->getRequest(); 
        $this->layout('layout/passport');
        $messages = '';
        $form = new UserForm();
        $form->get('submit')->setValue('Đổi mật khẩu');
        if ($request->isPost()) {
            //check password
            // $pattern = '/^\S*(?=\S{6,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$/';
            $pattern = '/^[A-Za-z0-9!#$%&*?@]{6,30}$/';
            $user['oldpassword'] = $request->getPost('password', null);
            $user['newpassword'] = $request->getPost('newpassword', null);
            $user['rewnewpassword'] = $request->getPost('renewpassword', null);
            $user['username'] = $this->getAuthServices()->getIdentity();
            //check validate
            if($user['newpassword']!=$user['rewnewpassword'])
                $result['status'] = -98;
            else if(!preg_match($pattern, $user['newpassword']))
                $result['status'] = -99;
            else
                $result = $this->getUserTable()->changePasswordPassport($user, $this->arrConfig);
            switch ($result['status']) {
                case 1:
                    //success - return login
                    $this->getSessionStorage()->forgetMe();
                    $this->getAuthServices()->clearIdentity();
                    return $this->redirect()->toRoute('passport', array(
                        'action' => 'login'
                    ));
                case 0:
                    $messages = "Thông tin đại lý không đúng";
                    break;
                case -2:
                    $messages = "Mật khẩu không hợp lệ";
                    break;
                case -14:
                    $messages = "Lỗi hệ thống";
                    break;
                case -17:
                    $messages = "Username không tồn tại";
                    break;
                case -18:
                    $messages = "Password không chính xác";
                    break;
                case -19:
                    $messages = "Mật khẩu mới không hợp lệ";
                    break;
                case -98:
                    $messages = "Xác nhận password không chính xác";
                    break;
                case -99:
                    $messages = "Mật khẩu không đúng định dạng";
                    break;
                default:
                    $messages = "Lỗi thay đổi password";
                    break;
            }
        }
        return array(
            'form'      => $form,
            'messages'  => $messages
        );

    }

    //send mail
    public function sendmail($result, $link, $email){
        $html = new MimePart('Xin chào '.$result->username.'.'
        .'<br> Vui lòng nhấn vào link sau để khôi phục mật khẩu '.$link
        .'<br> Xin cám ơn'
        .'<br> 100d.mobi team!');
        $html->type = "text/html";
        $body = new MimeMessage();
        $body->addPart($html);

        $message = new Message();
        $message->addTo($email)
                ->addFrom($this->arrConfig['email_sender'])
                ->setSubject('Email reset password')
                ->setBody($body);
                
        //SendmailTransport
        $transport = new SendmailTransport();
        $transport->send($message);
    }
    
    public function forgetpasswordAction()
    {
        $data = 'Nhập vào email của bạn.';
        $request = $this->getRequest(); 
        if ($request->isPost()) {
            $post = $request->getPost();
            $email = $post['email'];
            if($email==''){
                $data = 'Bạn cần nhập địa chỉ email';
            }else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $data = "Email không đúng định dạng"; 
            }else{
                //call api forgotPasswordPassport
                $forgotPasswordPassport = $this->getUserTable()->forgotPasswordByEmailPassport($email, $this->arrConfig);
                switch($forgotPasswordPassport['status']){
                    case 1:
                        $result = $forgotPasswordPassport['result'];
                        $data = 'Một email reset pass vừa mới được gửi vào email của bạn. (Hãy kiểm tra trong mục spam nếu không tìm thấy)';
                        $link = $this->arrConfig['main_domain']."/passport/resetpassword?username=".$result->username."&secretKey=".$result->resetKey;                        
                        $this->sendmail($result, $link, $email);
                        break;
                    case -3:
                        $data = "Email không hợp lệ";                    
                        break;
                    default: 
                        $data = "Không tìm thấy email này";

                }
            }
        }
        $result = new ViewModel();
        $result->setTerminal(true);
        $result->setVariables(array('data' => $data));
        return $result;
    }

    //api for app
    public function forgetpasswordsAction()
    {
        $status = 0;
        $data = '';
        $request = $this->getRequest(); 
        if ($request->isPost()) {
            $post = $request->getPost();
            $email = $post['email'];
            if($email==''){
                $data = 'Email trống';
            }else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $data = "Email không đúng định dạng"; 
            }else{
                //call api forgotPasswordPassport
                $forgotPasswordPassport = $this->getUserTable()->forgotPasswordByEmailPassport($email, $this->arrConfig);
                switch($forgotPasswordPassport['status']){
                    case 1:
                        $result = $forgotPasswordPassport['result'];
                        // success
                        $status = 1;
                        $data = 'Một email reset pass vừa mới được gửi vào email của bạn';
                        $link = $this->arrConfig['main_domain']."/passport/resetpassword?username=".$result->username."&secretKey=".$result->resetKey;
                        $this->sendmail($result, $link, $email);
                        break;
                    case -3:
                        $data = "Email không hợp lệ";                    
                        break;
                    default: 
                        $data = "Không tìm thấy email này";
                }
            }
        }

        $result = new JsonModel(array(
                'status'=> $status,
                'result' => $data,
        ));
        return $result;
    }



    public function resetpasswordAction(){
        $request = $this->getRequest(); 
        $data = 'Hãy nhập password mới của bạn';
        $username = $this->getRequest()->getQuery('username');
        if(!$username){
            $username='';
            $data = 'Không tìm thấy username!';
        }
        $secretKey = $this->getRequest()->getQuery('secretKey');        
        if(!$secretKey){
            $secretKey='';    
            $data = 'Không tìm thấy secretKey!';
        }     
        if ($request->isPost()) {
            $post = $request->getPost(); 
            //check password
            // $pattern = '/^\S*(?=\S{6,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])\S*$/';
            $pattern = '/^[A-Za-z0-9!#$%&*?@]{6,30}$/';
            if($post['password']==''){
                $data = "Hãy nhập lại mật khẩu";
            }else if($post['password']!=$post['repassword']){
                $data = "Nhập lại password không giống";
            }else if(!preg_match($pattern, $post['password'])){
                $data = "Mật khẩu không đúng định dạng";
            }else{
                $datas['username'] = $post['username'];
                $datas['secretKey'] = $post['secretKey'];
                $datas['password'] = $post['password'];
                $resetPasswordPassport = $this->getUserTable()->resetPasswordPassport($datas, $this->arrConfig);
                switch ($resetPasswordPassport['status']) {
                    case 1:
                        $data = 'Reset password mới thành công.';
                        break;
                    case -20:
                        $data = 'secretKey không hợp lệ.';
                        break;
                    case -21:
                        $data = 'secretKey không đúng hoặc đã hết hạn';
                        break;
                    default:
                        $data = 'Reset password lỗi!';
                        break;
                }
            }
        }
        $result = new ViewModel();
        $result->setTerminal(true);
        $result->setVariables(array('data' => $data,'username' => $username,'secretKey' => $secretKey));
        return $result;
    }

    public function logoutAction()
    {
        $this->getSessionStorage()->forgetMe();
        $this->getAuthServices()->clearIdentity();
        return $this->redirect()->toRoute('home');
    }
}
