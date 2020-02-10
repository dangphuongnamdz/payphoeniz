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
use Admin\Form\LogCards\CompensationForm;   
use Zend\Session\Container;
 use Admin\Model\Savelog;
use Zend\Db\Sql\Sql;

 class GamerController extends AbstractActionController
 {
    protected $gamerTable;
    protected $arrPassportConfig;
    private $domain;
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
        //get config
        $config = $this->getServiceLocator()->get('config');
        $this->arrPassportConfig['agent'] = $config ['passport'] ['agent'];
        $this->arrPassportConfig['key'] = $config ['passport'] ['key'];
        $this->arrPassportConfig['encryptKey'] = $config ['passport'] ['encryptKey'];
        $this->arrPassportConfig['secret'] = $config ['passport'] ['secret'];
        $this->arrPassportConfig['domain'] = $config ['passport'] ['domain'];
        $this->domain = $config ['domain'];
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
	
    public function getGamerTable()
    {
        if (!$this->gamerTable) {
            $sm = $this->getServiceLocator();
            $this->gamerTable = $sm->get('Admin\Model\GamerTable');
        }
        return $this->gamerTable;
    }

    public function saveLogCharge($parameters){
		
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $driver = $dbAdapter->getDriver();
        $connection = $driver->getConnection();
        $sql = "CALL sp_card_insert_transaction ('"
        .$parameters['in_transaction']."','".$parameters['in_username']."','".$parameters['in_email']."','".$parameters['in_pin']
        ."','".$parameters['in_serie']."','".$parameters['in_type']."',".$parameters['status'].",".$parameters['amount'].",".$parameters['gold']
        .",'".$parameters['cardMessage']."',".$parameters['card_status'].",'".$parameters['cardTransid']."',".$parameters['balance'].",'".$parameters['product_id']."')";
		
        $res = $connection->execute($sql);
        $statement = $res->getResource();
        $result = $statement->fetchAll(\PDO::FETCH_OBJ);
        return $result;
    }

    public function saveLogPay($parameters){
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $driver = $dbAdapter->getDriver();
        $connection = $driver->getConnection();
        $sql = "CALL sp_card_create_paygame ('"
        .$parameters['in_transaction']."',".$parameters['gold'].",'".$parameters['in_username']."','".$parameters['in_role']."','".$parameters['server_id']."','".$parameters['product_id']."',".$parameters['card_status'].")";
        $res = $connection->execute($sql);
        $statement = $res->getResource();
        $result = $statement->fetchAll(\PDO::FETCH_OBJ);
        return $result;
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

    public function getCoin($gold, $product_id){
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $sql = new Sql($dbAdapter);
        $select = $sql->select();
        $select->where->like('product_id', (String)$product_id);
        $select->where->like('gold', (String)$gold);
        $select->from ("gold");
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        $data = $this->object_to_array($result);
        return $data[0]['amount'];
    }
	public function getCoinById($gold_id, $product_id){
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $sql = new Sql($dbAdapter);
        $select = $sql->select();
        $select->where->like('product_id', (String)$product_id);
        $select->where->like('product_gold_id', (String)$gold_id);
        $select->from ("gold");
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        $data = $this->object_to_array($result);
		$item['amount'] = $data[0]['amount'];
		$item['gold'] = ($data[0]['gold']>0)?$data[0]['gold']:0;
        return $item;
    }

    public function indexAction(){
        $result = '';
        $username = '';
        $error = '';
        $request = $this->getRequest();
        if ($request->isPost()) {
            $username = $request->getPost('username');
            $result = $this->getGamerTable()->getUserPassport($username, $this->arrPassportConfig);
            switch($result['status']){
                case 1:
                    $error = '';
                    $result = $result['result'];
                    break;
                case -17:
                    $error = "Username không tồn tại";
                    $result = '';
                    break;
                default:
                    $error = "Không tìm được username này";
                    $result = '';
                    break;
            }
        }
        return array(
            'username' => $username,
            'error' => $error,
            'result' => $result,
        );
    }

    public function compensationAction(){
        $messages = '';
        $config = $this->getServiceLocator()->get('config');
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $agent = $this->params()->fromRoute('agent', null);//
        $form = new CompensationForm ($dbAdapter, $agent, $config);
        $form->get('submit')->setValue('Đền bù');
        $request = $this->getRequest();
        $parameters['id_user'] = $this->params()->fromRoute('id', null);//
        $parameters['in_username'] = $this->params()->fromRoute('username', null);//
       
        if ($request->isPost()) {
            $form->setData($request->getPost());
            //create parameters default
            $parameters['in_email'] = 'null';
            $parameters['in_transaction'] = 'REF'.date('YmdHis');
            $parameters['balance'] = 0;
            $parameters['in_type'] = 'REF';
            $parameters['in_pin'] = 'null';
            $parameters['in_serie'] = 'null';
            $parameters['cardTransid'] = 'null';
            //GET POST PARAM
            $parameters['product_id'] = $request->getPost('in_product_id');//
            $parameters['gold'] = $request->getPost('in_amount');//
            $parameters['in_username'] = $request->getPost('username', null);//
            $parameters['id_user'] = $request->getPost('id', null);//
            $parameters['server_id'] = $request->getPost('in_server', null); //
            $parameters['role_id'] = $request->getPost('role_id', null);
            
            if($parameters['product_id'] != 'm005'){
                if( $parameters['gold'] == null){
                    $messages = "Vui lòng chọn mệnh giá";
                    return array(
                        'form' => $form,
                        'username'=>$parameters['in_username'],
                        'id'=>$parameters['id_user'],
                        'messages'  => $messages
                    );
                }
                if($parameters['role_id']!=null){
                    $item = $this->getCoinById($parameters['gold'], $parameters['product_id']);//
                    $parameters['amount'] = $item['amount'];
                    $parameters['gold_id'] = $parameters['gold'];
                    $parameters['gold'] = $item['gold'];
                }else{
                    $parameters['amount'] = $this->getCoin($parameters['gold'], $parameters['product_id']);//
                }
            } else{
                $custom_amount = $request->getPost('custom_amount');//
                if($parameters['gold'] == null && $custom_amount == null){
                    $messages = "Vui lòng chọn mệnh giá hoặc tùy chọn mệnh giá";
                    return array(
                        'form' => $form,
                        'username'=>$parameters['in_username'],
                        'id'=>$parameters['id_user'],
                        'messages'  => $messages
                    );
                }
                $refund_m005 = $config['payment']['m005']['rate_refund'];
                if($custom_amount){
                    if((int)$custom_amount < 10000 || (int)$custom_amount > 1000000){
                        $messages = "Tùy chọn mệnh giá từ khoảng 10.000 tới 1.000.000";
                        return array(
                            'form' => $form,
                            'username'=>$parameters['in_username'],
                            'id'=>$parameters['id_user'],
                            'messages'  => $messages
                        );
                    }
                    $parameters['gold'] = (int)$custom_amount * $refund_m005;
                    $parameters['amount'] = $custom_amount;
                }else{
                    $parameters['amount'] = $parameters['gold']/$refund_m005;
                }
                $parameters['gold_id'] = 1;
            }
			
            $domain = $this->domain;
            $_WSDL_URI_role = "/api/role/info";
			if($parameters['product_id']!='m002' && $parameters['product_id']!='m003' && $parameters['product_id']!='m005'){
                $linkRole = $domain.$_WSDL_URI_role.'?agent='.$parameters['product_id'].'&server='.$parameters['server_id'].'&account='.$parameters['id_user'].'';
                $resultRole = json_decode(file_get_contents($linkRole));
				if ($resultRole->status == 1){
					for($i = 0; $i < count($resultRole); $i++){
						$parameters['in_role'] = $resultRole->data[$i]->role_name;        
					}
				}else{
					$parameters['in_role'] = 'null';
				}
			}else{
				$parameters['in_role'] = $request->getPost('role_name', 'null');
            }
         
            $_WSDL_URI_reg = "/api/role/exchange";
			if($parameters['product_id']=='m002' || $parameters['product_id']=='m003' || $parameters['product_id']=='m005'){				
				$link = $domain.$_WSDL_URI_reg. '?agent='.$parameters['product_id'].'&server='
				.$parameters['server_id'].'&account='.$parameters['id_user'].'&amount='
				.$parameters['amount'].'&game_money='.$parameters['gold'].
				'&role_id='.$parameters['role_id'].'&item_id='.$parameters['gold_id'];
            }else{
				$link = $domain.$_WSDL_URI_reg. '?agent='.$parameters['product_id'].'&server='.$parameters['server_id'].'&account_system_id=0060298&account='.$parameters['id_user'].'&amount='.$parameters['amount'].'&game_money='.$parameters['gold'];
            }
            // var_dump($link);die;
            $result = json_decode(file_get_contents($link));
            switch ($result->status) {
                case 1:   
                    $parameters['status'] = 1;
                    $parameters['card_status'] = 1;
                    $parameters['balance'] = 0;
                    $parameters['cardMessage'] = $messages = 'Đền bù thành công';
                    $resultLog = $this->saveLogCharge($parameters);
                    $resultPay = $this->saveLogPay($parameters);
					$ip = $request->getServer('REMOTE_ADDR'); 
					$this->getSavelogTable()->saveLogAction($this->username, 'Compensation '.$parameters['in_username'].'-'.$parameters['product_id'].'-'.$parameters['amount'],'Compensation',$ip);
                    break;
                default:  
                    $parameters['status'] = -1;
                    $parameters['card_status'] = $result->status;
                    $parameters['balance'] = 0;
                    $parameters['cardMessage'] = $messages = 'Lỗi đền bù, xem mã lỗi';
					
                    $resultLog = $this->saveLogCharge($parameters);
                    break;
            }
        }
        return array(
            'form' => $form,
            'username'=>$parameters['in_username'],
            'id'=>$parameters['id_user'],
            'messages'  => $messages
        );
    }


    public function infoAction(){
        $result = '';
        $username = '';
        $success = '';
        $error = '';
        $request = $this->getRequest();
        $session = new Container('UserPassport');
        if ($request->isPost()) {
            //get config
            $config = $this->getServiceLocator()->get('config');
            $this->arrPassportConfig['agent'] = $config ['passport'] ['agent'];
            $this->arrPassportConfig['key'] = $config ['passport'] ['key'];
            $this->arrPassportConfig['encryptKey'] = $config ['passport'] ['encryptKey'];
            $this->arrPassportConfig['secret'] = $config ['passport'] ['secret'];
            $this->arrPassportConfig['domain'] = $config ['passport'] ['domain'];
            //changestatus
            if($request->getPost('status')){
                $status = $request->getPost('status');
                //$resultStatus = $this->getGamerTable()->updateStatusUserPassport($username, $status, $this->arrPassportConfig);
                $resultStatus['status'] = 1;
                switch($resultStatus['status']){
                    case 1:
                        //$result = $resultStatus['result'];
                        // $session->setExpirationSeconds(60 * 60);
                        // $session->offsetSet('infoUserPassport', $result);
                        $result = $session->offsetGet('infoUserPassport');
                        $username = $result->username;
                        //$success = "Thay đổi trạng thái thành công";
                        $success = "Chưa có API thay đổi trạng thái";
                        break;
                    case -17:
                        $error = "Username không tồn tại";
                        break;
                    default:
                        $error = "Không tìm được username này";
                        break;
                }
            }else{
                $username = $request->getPost('username');
                $resultStatus = $this->getGamerTable()->getUserPassport($username, $this->arrPassportConfig);
                switch($resultStatus['status']){
                    case 1:
                        $error = '';
                        $result = $resultStatus['result'];
                        $session->setExpirationSeconds(60 * 60);
                        $session->offsetSet('infoUserPassport', $result);
                        break;
                    case -17:
                        $error = "Username không tồn tại";
                        $result = '';
                        break;
                    default:
                        $error = "Không tìm được username này";
                        $result = '';
                        break;
                }
            }
        }
        else if($session->offsetGet('infoUserPassport')!=null){
            $result = $session->offsetGet('infoUserPassport');
            $username = $result->username;
        }
        return array(
            'username' => $username,
            'error' => $error,
            'success' => $success,
            'result' => $result,
        );
    }

    public function updateinfoAction(){
        $result = '';
        $error = '';
        $success = '';
        $request = $this->getRequest();
        $session = new Container('UserPassport');
        if($session->offsetGet('infoUserPassport')!=null){
            $result = $session->offsetGet('infoUserPassport');
            $current_mobile = $result->mobile;
            $current_email = $result->email;
            if($request->isPost()){
                $user['username'] = $request->getPost('username');
                $user['fullname'] = $request->getPost('fullname');
                $user['sex'] = $request->getPost('sex');
                $user['birthday'] = $request->getPost('birthday');
                $user['identityNumber'] = $request->getPost('identityNumber');
                $user['identityDate'] = $request->getPost('identityDate');
                $user['identityPlace'] = $request->getPost('identityPlace');
                if($request->getPost('mobile') != $current_mobile)
                    $user['mobile'] = $request->getPost('mobile');
                else
                    $user['mobile'] = null;
                if($request->getPost('email') != $current_email)
                    $user['email'] = $request->getPost('email');
                else
                    $user['email'] = null;
                $user['city'] = $request->getPost('city');
                $user['address'] = $request->getPost('address');
                $user['company'] = $request->getPost('company');
                $user['companyAddress'] = $request->getPost('companyAddress');
                $resultStatus = $this->getGamerTable()->updateUserPassport($user, $this->arrPassportConfig);
                switch($resultStatus['status']){
                    case 1:
                        $success = "Chỉnh sửa thành công";
                        $createDate = $result->createDate;
                        $result = $resultStatus['result'];
                        $result->createDate=$createDate;
                        $session = new Container('UserPassport');
                        $session->setExpirationSeconds(60 * 60);
                        $session->offsetSet('infoUserPassport', $result);
						$ip = $request->getServer('REMOTE_ADDR'); 
						$this->getSavelogTable()->saveLogAction($this->username, 'Update '.$user['username'],'UpdateInfo',$ip);
                        break;
                    case 0:
                        $error = "Thông tin đại lý không đúng";
                        break;
                    case -1:
                        $error = "Tên đăng nhập không hợp lệ";
                        break;
                    case -3:
                        $error = "Email không hợp lệ";
                        break;
                    case -4:
                        $error = "Họ tên không hợp lệ";
                        break;
                    case -5:
                        $error = "Ngày sinh không hợp lệ";
                        break;
                    case -6:
                        $error = "Ngày sinh không hợp lệ";
                        break;
                    case -7:
                        $error = "CMND không hợp lệ";
                        break;
                    case -8:
                        $error = "Số điện thoại không hợp lệ";
                        break;
                    case -9:
                        $error = "Địa chỉ không hợp lệ";
                        break;
                    case -10:
                        $error = "Tỉnh/thành phố không hợp lệ";
                        break;
                    case -11:
                        $error = "Tên công ty không hợp lệ";
                        break;
                    case -12:
                        $error = "Địa chỉ công ty không hợp lệ";
                        break;
                    case -13:
                        $error = "Tên đăng nhập đã tồn tại";
                        break;
                    case -14:
                        $error = "Lỗi hệ thống";
                        break;
                    case -17:
                        $error = "Tên đăng nhập không tồn tại";
                        break;
                    case -23:
                        $error = "Email đã được đăng ký cho tài khoản khác";
                        break;
                    case -24:
                        $error = "Dữ liệu không thiếu hoặc không hợp lệ";
                        break;
                    case 101:
                        $error = "Thông tin của bạn chưa có địa chỉ email, bạn cần nhập email để cập nhập";
                        break;
                    case 102:
                        $error = "Email không đúng định dạng";
                        break;
                    default:
                        $error = "Lỗi không xác định";
                        break;
                }
            }
        }else{
            $error = "Timeout session, thức hiện lại get user";
        }
        return array(
            'error' => $error,
            'success' => $success,
            'result' => $result,
        );
    }


    public function forgetpasswordAction(){
        $messages = '';
        $request = $this->getRequest();
        if ($request->isPost()) {
            $username = $request->getPost('username');
            $password = $request->getPost('password');
            $repassword = $request->getPost('repassword');
            if($password != $repassword){
                $messages = "Password xác nhận không chính xác";
            }
            
            else if(strlen($password) <= 5){
                $messages = "Password lớn hơn 5 ký tự";
            }else{
                $result = $this->getGamerTable()->getUserPassport($username, $this->arrPassportConfig);
                switch($result['status']){
                    case 1:
                        $userPassport = $result['result'];
                        //call api forgotPasswordPassport
                        $forgotPasswordPassport = $this->getGamerTable()->forgotPasswordPassport($userPassport, $this->arrPassportConfig);
                        if($forgotPasswordPassport['status']==1){
                            //call api resetPasswordPassport
                            $forgotPasswordPassport = $forgotPasswordPassport['result'];
                            $resetPasswordPassport = $this->getGamerTable()->resetPasswordPassport($forgotPasswordPassport, $password, $this->arrPassportConfig);
                            if($resetPasswordPassport['status']==1){
                                $messages = 'Reset password mới thành công';
                            }else{
                                $messages = 'Reset password lỗi! Không thể gọi hàm resetPasswordPassport';
                            }
                        }else{
                            $messages = 'Reset password lỗi! Không thế gọi hàm forgotPasswordPassport';
                        }
                        break;
                    case -17:
                        $messages = "Username không tồn tại";
                        break;
                    default:
                        $messages = "Không tìm được username này";
                        break;
                }
            }
        }
        return new ViewModel(array(
            'messages'  => $messages
        ));
    }
 }