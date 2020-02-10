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
use Application\Form\NapGoldForm;    
use Zend\View\Model\JsonModel;
use Zend\Session\Container;

class PaymentsController extends AbstractActionController
{
    protected $authservices;
    protected $arrPaymentConfig;
    protected $arrPassportConfig;
    protected $userTable;
    protected $productTable;
    protected $menu;
    protected $storage;
    protected $modelTable;
    protected $paymentTable;
    protected $HistoryTransTable;

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
            //get config payment
            $config = $this->getServiceLocator()->get('config');
            $this->arrPaymentConfig = $config ['payment'];
            //get config passport
            $this->arrPassportConfig = $config ['passport'];
            $this->data['sale'] = $config ['data']['sale'];
            //send to layout
            $this->layout()->login = $login;   
            $this->layout()->menu = $arr_tree;   
            $this->layout()->config = $config;
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

    public function getProductTable()
    {
        if (!$this->productTable) {
            $sm = $this->getServiceLocator();
            $this->productTable = $sm->get('Application\Model\ProductTable');
        }
        return $this->productTable;
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
        .$parameters['in_transaction']."',".$parameters['gold'].",'".$parameters['in_username']."','".$parameters['server_id']."','".$parameters['product_id']."',".$parameters['card_status'].")";
        $res = $connection->execute($sql);
        $statement = $res->getResource();
        $result = $statement->fetchAll(\PDO::FETCH_OBJ);
        return $result;
    }   

    public function getGameTable($agent)
    {
        if (!$this->modelTable) {
            $sm = $this->getServiceLocator();
            $this->modelTable = $sm->get('Application\\Model\\Agent\\' . strtoupper($agent));
        }
        return $this->modelTable;
    }

    public function getPaymentTable()
    {
        if (!$this->paymentTable) {
            $sm = $this->getServiceLocator();
            $this->paymentTable = $sm->get('Application\Model\PaymentTable');
        }
        return $this->paymentTable;
    }


    public function getHistoryTransTable()
    {
        if (!$this->HistoryTransTable) {
            $sm = $this->getServiceLocator();
            $this->HistoryTransTable = $sm->get('Application\Model\HistoryTransTable');
        }
        return $this->HistoryTransTable;
    }

    public function indexAction()
    {
        $config = $this->getServiceLocator()->get('config');
        //check login
        $product_slug = (String) $this->params()->fromRoute('slug', null);
        if (!$this->getAuthServices()->hasIdentity()){
            return $this->redirect()->toRoute('passport', array(
                'action' => 'login',
                'id'=> $product_slug,
            ));
        }
        $messages = "";
        $parameters['in_username'] = '';
        $parameters['id_user']  = '';
        $request = $this->getRequest(); 
        $parameters['in_username'] = $this->getAuthServices()->getIdentity();
        //////////////////////////////////////GET INFO USER
        $result = $this->getUserTable()->getUserPassport($parameters['in_username'], $this->arrPassportConfig);
        $parameters['in_email'] = $result['result']->email;
        $parameters['id_user'] = $result['result']->id;
        //////////////////////////////////////GET INFO Product
        $product_item = $this->getProductTable()->getProduct($product_slug);
        if($product_item->payment_type == 2)
            return $this->redirect()->toUrl($this->domain.'/payment'.'/'.$product_slug.".html");
        if($product_item->payment_type == 3)
            return $this->redirect()->toUrl($this->domain.'/paymentnone'.'/'.$product_slug.".html"); 
        if ($request->isPost()) {
            $parameters['in_serie'] = trim($request->getPost('in_serie', null));
            $parameters['in_type'] = $request->getPost('in_type', null);
            $parameters['in_pin'] = trim($request->getPost('in_pin', null));
            $parameters['role_id'] = $request->getPost('role_id', null);
            $parameters['in_username'] = $this->getAuthServices()->getIdentity();
            $parameters['amount_pay'] = $request->getPost('amount_pay', 0);
            $parameters['product_id'] = $product_item->agent;
            //check role
            if($parameters['role_id']==null){
                $messages = 'Lỗi, không tìm thấy nhân vật';
            }else{
                ///atm
                if($parameters['amount_pay']!=0){
                    $parameters['balance'] = 0;
                    $parameters['in_gold'] = 0;
                    if(10000 <= $parameters['amount_pay'] && $parameters['amount_pay'] <= 2000000){
                        try {
                            $parameters['transId'] = $parameters['product_id'].time().substr(str_shuffle("abcdefghijklmnopqrstuvwxyz"), 0, 6);
                            $parameters['server_id'] = $request->getPost('server_list', null); 
                            $this->getHistoryTransTable()->addHistoryTrans($parameters);
                            $resultAtmLink = $this->getPaymentTable()->chargeAtmPayment($parameters, $config['atm']); 
                            return $this->redirect()->toUrl($resultAtmLink);
                        }catch (Exception $e){
                            $messages = 'Lỗi hệ thống';
                        }
                    }else{
                        $messages = 'Mệnh giá nạp tiền phải từ 10,000đ - 2,000,000đ';
                    }
                }else{ ///card
                    $parameters ['sign'] = md5 ( $this->arrPaymentConfig[$product_item->agent]['key'] . md5 ( $parameters ['in_username'] . $parameters ['in_serie'] ) . $this->arrPaymentConfig[$product_item->agent]['secret'] );//
                    $_WSDL_URI_reg = "/payment/chargecardreturnvalue";
                    //////////////////////////////////////      
                    $link = $this->arrPaymentConfig['domain'].$_WSDL_URI_reg
                            . '?agent='.$product_item->agent  . '&key='.urlencode($this->arrPaymentConfig[$product_item->agent]['key']) . '&username='.$parameters['in_username'] 
                            . '&cardCode='.$parameters['in_pin'] . '&cardSerial='.$parameters['in_serie']
                            . '&cardType='.$parameters['in_type'] . '&sign='.$parameters['sign'];
                    $result = json_decode(file_get_contents($link));
                    //create parameters default
                    $parameters['in_transaction'] = $parameters['in_type'].date('YmdHis');
                    if($result->status==1) $parameters['status'] = 1; else $parameters['status'] = -1;
                    if(isset($result->result->amount)) $parameters['amount'] = $result->result->amount; else $parameters['amount'] = 0;
                    if(isset($result->result->gold)) $parameters['gold'] = $result->result->gold; else $parameters['gold'] = 0;
                    if($result->status==1) $parameters['cardMessage'] = 'Nạp thẻ thành công'; else $parameters['cardMessage'] = 'Nạp thẻ thất bại';
                    $parameters['card_status'] = $result->status;
                    if(isset($result->result->transid)) $parameters['cardTransid'] = $result->result->transid; else $parameters['cardTransid'] = '';
                    if(isset($result->result->balance)) $parameters['balance'] = $result->result->balance; else $parameters['balance'] = 0;
                    $config_payment_rate = $config['payment'][$parameters['product_id']]['rate'];
                    foreach ($config_payment_rate as $rate){
                        if($rate['type'] == $parameters['in_type']){
                            $parameters['gold'] = (int)$parameters['gold']*$rate['rate'];
                        }
                    }
                    //save log file
                    $string = '{ "date": "'.date('H:i:s Y-m-d').'", "service": "ChargeCardReturnValue'
                        .'","agent": "'.$product_item->agent
                        .'","productAgent": "'.$parameters['product_id']
                        .'","username": "'.$parameters ['in_username']
                        .'","userId": "'.$parameters ['id_user']
                        .'","email": "'.$parameters ['in_email']
                        .'","cardSerial": "'.$parameters ['in_serie']
                        .'","cardCode": "'.$parameters ['in_pin']
                        .'","cardType": "'.$parameters ['in_type']
                        .'","sign": "'.$parameters ['sign']
                        .'","transactionId": "'.$parameters ['in_transaction']
                        .'","amount": "'.$parameters ['amount']
                        .'","cardMessage": "'.$parameters ['cardMessage']
                        .'","cardTransid": "'.$parameters ['cardTransid']
                        .'","cardStatus": "'.$parameters ['card_status'].'" }';
                    $this->getUserTable()->saveLogFile($parameters['product_id'], $string, $parameters['in_type']);
                    switch ($result->status) {
                        case 1:
                            //save log db
                            $resultLog = $this->saveLogCharge($parameters);
                            if($resultLog!=null){
                                if($resultLog[0]->result!='1'){
                                    $messages = 'Bạn đã nạp thẻ liên tiếp quá số lần cho phép. Hãy đợi một lát rồi nạp lại nhé';
                                }
                            }
                            //call api excharge for server
                            $parameters['server_id'] = $request->getPost('server_list', null); 
                            $domain = $config['domain'];
                            $_WSDL_URI_reg = "/api/role/exchange";
                            if($parameters['product_id'] == 'm005'){
                                $parameters['gold_id'] = 1;
                                $results = $this->getGameTable($parameters['product_id'])->payGame($parameters, $config); 
                                $parameters['card_status'] = $results['status'] ? 1 : -1;
                                $parameters['cardMessage'] = $results['status'] ? 'Nạp vào game thành công' : 'Nạp vào game thất bại';
                            }else{
                                $link = $domain.$_WSDL_URI_reg. '?agent='. $parameters['product_id']  . '&server='.$parameters['server_id'] . '&account_system_id=0060298&account='.$parameters['id_user'].'&amount='.$parameters['amount'].'&game_money='.$parameters['gold'];
                                $results = json_decode(file_get_contents($link));
                                if($results->status==1) $parameters['card_status'] = 1; else $parameters['card_status'] = -1;
                                if($results->status==1) $parameters['cardMessage'] = 'Nạp vào game thành công'; else $parameters['cardMessage'] = 'Nạp vào game thất bại';
                            }
                            //save log file exchage
                            $string = '{ "date": "'.date('H:i:s Y-m-d').'", "service": "ChargeCardReturnValue'
                                .'","agent": "'.$product_item->agent
                                .'","productAgent": "'.$parameters['product_id']
                                .'","username": "'.$parameters ['in_username']
                                .'","userId": "'.$parameters ['id_user']
                                .'","email": "'.$parameters ['in_email']
                                .'","cardSerial": "'.$parameters ['in_serie']
                                .'","cardCode": "'.$parameters ['in_pin']
                                .'","cardType": "'.$parameters ['in_type']
                                .'","sign": "'.$parameters ['sign']
                                .'","transactionId": "'.$parameters ['in_transaction']
                                .'","amount": "'.$parameters ['amount']
                                .'","cardMessage": "'.$parameters ['cardMessage']
                                .'","cardTransid": "'.$parameters ['cardTransid']
                                .'","cardStatus": "'.$parameters ['card_status']
                                .'","link": "'.$link.'" }';
                            $this->getUserTable()->saveLogFile($parameters['product_id'], $string, 'exchange');
                            if($parameters['product_id'] != 'm005'){
                                $resultPay = $this->saveLogPay($parameters);
                            }
                            if($parameters['product_id'] == 'm005'){
                                $messages = $results['messages'];
                                break;
                            }else{
                                if ($results->status==1){
                                    $messages = 'Nạp thành công';
                                    break;
                                }else {
                                    $messages = 'Lỗi nạp thẻ vào game, hãy liên hệ với bộ phận CSKH để được hỗ trợ';
                                    break;
                                }
                            }
                        case -1:
                            $messages = 'Tên đăng nhập không hợp lệ';
                            break;
                        case -2:
                            $messages = 'Email không hợp lệ';
                            break;
                        case -3:
                            $messages = 'Số serial không hợp lệ';
                            break;
                        case -4:
                            $messages = 'Mã pin không hợp lệ';
                            break;
                        case -5:
                            $messages = 'Loại thẻ không hợp lệ';
                            break;
                        case 50:
                            $parameters['status'] = -1;
                            $parameters['cardMessage'] = 'Thẻ đã sử dụng hoặc không tồn tại';
                            $parameters['card_status'] = $result->status;
                            $messages = 'Thẻ đã sử dụng hoặc không tồn tại';
                            $resultLog = $this->saveLogCharge($parameters);
                            if($resultLog!=null){
                                if($resultLog[0]->result!='1'){
                                    $messages = 'Bạn đã nạp thẻ liên tiếp quá số lần cho phép. Hãy đợi một lát rồi nạp lại nhé';
                                }
                            }
                            break;
                        case 53:
                            $parameters['status'] = -1;
                            $parameters['cardMessage'] = 'Thông tin thẻ không đúng';
                            $parameters['card_status'] = $result->status;
                            $messages = 'Thông tin thẻ không đúng';
                            $resultLog = $this->saveLogCharge($parameters);
                            if($resultLog!=null){
                                if($resultLog[0]->result!='1'){
                                    $messages = 'Bạn đã nạp thẻ liên tiếp quá số lần cho phép. Hãy đợi một lát rồi nạp lại nhé';
                                }
                            }
                            break;
                        default:
                            $parameters['status'] = -1;
                            $parameters['cardMessage'] = 'Nạp thẻ thất bại';
                            $parameters['card_status'] = $result->status;
                            $messages = 'Nạp thẻ thất bại';
                            $resultLog = $this->saveLogCharge($parameters);
                            if($resultLog!=null){
                                if($resultLog[0]->result!='1'){
                                    $messages = 'Bạn đã nạp thẻ liên tiếp quá số lần cho phép. Hãy đợi một lát rồi nạp lại nhé';
                                }
                            }
                            break;
                    }
                }
            }
        }
        $this->layout('layout/passport');
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new NapGoldForm ($dbAdapter, $product_item->id);
        $form->get('agent')->setValue($product_item->agent);
        return array(
            'form'      => $form,
            'name'      => $product_item->name,
            'agent'      => $product_item->agent,
            'username'  => $parameters['in_username'],
            'id_user'  =>  $parameters['id_user'],
            'messages'  => $messages,
            'messagesSale'    => $this->data['sale'],
        );
    }
    
    public function noneAction()
    {
        //check login
        $product_slug = (String) $this->params()->fromRoute('slug', null);
        if (!$this->getAuthServices()->hasIdentity()){
            return $this->redirect()->toRoute('passport', array(
                'action' => 'login',
                'id'=> $product_slug,
            ));
        }
        //////////////////////////////////////GET INFO Product
        $product_item = $this->getProductTable()->getProduct($product_slug);
        if($product_item->payment_type == 1)
            return $this->redirect()->toUrl($this->domain.'/payments'.'/'.$product_slug.".html");
        if($product_item->payment_type == 2)
            return $this->redirect()->toUrl($this->domain.'/payment'.'/'.$product_slug.".html");
        return new ViewModel(array(
            
        ));
    }
}
