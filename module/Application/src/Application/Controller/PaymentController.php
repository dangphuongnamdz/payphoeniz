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
use Application\Form\PayHistoryForm;    
use Zend\View\Model\JsonModel;
use Zend\Session\Container;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Adapter;
use Application\Model\Role;    

class PaymentController extends AbstractActionController
{
    protected $authservices;
    protected $arrPassportConfig;
    protected $arrPaymentConfig;
    protected $arrAtmConfig;
    protected $data;
    protected $userTable;
    protected $productTable;
    protected $roleTable;
    protected $paymentTable;
    protected $modelTable;
    protected $menu;
    protected $storage;

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
            $config = $this->getServiceLocator()->get('config');
            $this->arrPaymentConfig = $config ['payment'];
            $this->arrAtmConfig = $config ['atm'];
            $this->arrPassportConfig = $config ['passport'];
            $this->data['domain'] = $config ['domain'];
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

    public function getRoleTable()
    {
        if (!$this->roleTable) {
            $sm = $this->getServiceLocator();
            $this->roleTable = $sm->get('Application\Model\RoleTable');
        }
        return $this->roleTable;
    }

    public function getPaymentTable()
    {
        if (!$this->paymentTable) {
            $sm = $this->getServiceLocator();
            $this->paymentTable = $sm->get('Application\Model\PaymentTable');
        }
        return $this->paymentTable;
    }

    public function getGameTable($agent)
    {
        if (!$this->modelTable) {
            $sm = $this->getServiceLocator();
            $this->modelTable = $sm->get('Application\\Model\\Agent\\' . strtoupper($agent));
        }
        return $this->modelTable;
    }

    public function getCoin($product_id){
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $sql = new Sql($dbAdapter);
        $select = $sql->select();
        $select->where->like('product_id', (String)$product_id);
        $select->from ("gold");
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $result;
    }
	public function getCoinById($product_id,$gold_id){
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $sql = new Sql($dbAdapter);
        $select = $sql->select();
        $select->where->like('product_id', (String)$product_id);
		$select->where->like('product_gold_id', (String)$gold_id);
        $select->from ("gold");
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $result;
    }
	public function getCoinByAmount($product_id,$amount){
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $sql = new Sql($dbAdapter);
        $select = $sql->select();
        $select->where->like('product_id', (String)$product_id);
		$select->where->like('amount', $amount);
        $select->from ("gold");
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        return $result;
    }

    public function getStatusChargeATM(){
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $sql = new Sql($dbAdapter);
        $select = $sql->select();
        $select->where->like('type', 'atm');
        $select->from ("charge_type");
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();
        $arrChargeAtm = $this->object_to_array($result);
        $statusChargeAtm = $arrChargeAtm[0]['status'];
        return $statusChargeAtm;
    }

    public function indexAction()
    {
       
        $product_slug = (String) $this->params()->fromRoute('slug', null);
        if (!$this->getAuthServices()->hasIdentity()){
            return $this->redirect()->toRoute('passport', array(
                'action' => 'login',
                'id'=> $product_slug,
            ));
        }
        $status = false;
        $messages = '';
        $is_pay = 2;
        $is_amount = 0;  
		$gold_id = 0;
        $request = $this->getRequest(); 
        $parameters['in_username'] = $this->getAuthServices()->getIdentity();
        $product_item = $this->getProductTable()->getProduct($product_slug);
		
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new NapGoldForm ($dbAdapter, $product_item->id);
        $form->get('agent')->setValue($product_item->agent);
        $form->setData($request->getPost());
		$listServers = $form->getOptionsForSelect($product_item->id);		
        ///////////////////////CHECK TYPE PAYMENT
        

        if($product_item->payment_type == 1)
            return $this->redirect()->toUrl($this->data['domain'].'/payments'.'/'.$product_slug.".html"); 
        if($product_item->payment_type == 3)
            return $this->redirect()->toUrl($this->data['domain'].'/paymentnone'.'/'.$product_slug.".html"); 
        ///////////////////////GET BALANCE USER
        $resultBalance = $this->getPaymentTable()->getBalance($parameters['in_username'], $product_item->agent, $this->arrPaymentConfig); 
		
        $balance = $resultBalance->balance;
        //////////////////////GET INFO USER
        $result = $this->getUserTable()->getUserPassport($parameters['in_username'], $this->arrPassportConfig);
        $parameters['in_email'] = $result['result']->email;
        $parameters['id_user'] = $result['result']->id;
        $coin = $this->object_to_array($this->getCoin($product_item->agent));
        $parameters['role_id'] = '';
        if ($request->isPost()) {
           
            $parameters['in_type'] = trim($request->getPost('in_type', ''));
            $parameters['in_serie'] = trim($request->getPost('in_serie', ''));
            $parameters['in_pin'] = trim($request->getPost('in_pin', ''));
            $parameters['server_id'] = $request->getPost('server_list', '');
            $parameters['role_id'] = $request->getPost('role_id', '');
			$parameters['role_name'] = $request->getPost('role_name', '');
            $parameters['amount_pay'] = (int) $request->getPost('amount_pay', 0);
            //arameters['in_amount'] = (int) $request->getPost('amount_game', 0);//product_gold_id
			$parameters['product_gold_id'] = (int) $request->getPost('amount_game', 0);
            $parameters['theThang'] = (int) $request->getPost('theThang', 0);
            $parameters['in_gold'] = 0;
            $parameters['balance'] = $balance;
            $parameters['product_id'] = $product_item->agent;
            $actionSubmit = $request->getPost('submit', null);
			$gold_id = $parameters['product_gold_id'];
            if($parameters['server_id']!=''){
                $is_pay = 1;
                //
            }
			
			for($i = 0; $i < count($coin); $i++){
				if($product_item->agent=='m002' || $product_item->agent=='m003'){
					if($parameters['product_gold_id'] == $coin[$i]['product_gold_id']){
						$parameters['in_gold'] = is_numeric($coin[$i]['gold'])?$coin[$i]['gold']:0;
						$parameters['card_month'] = $coin[$i]['card_month'];
						$parameters['in_amount'] = $coin[$i]['amount'];
						$is_amount = $parameters['in_amount'];
						
					}
				}elseif($product_item->agent=='m001'||$product_item->agent=='h001'){
					if($parameters['product_gold_id'] == $coin[$i]['amount']){
						$parameters['in_gold'] = $coin[$i]['gold'];
						$parameters['card_month'] = $coin[$i]['card_month'];
						$parameters['in_amount'] = $coin[$i]['amount'];
						$is_amount = $parameters['in_amount'];
						$parameters['product_gold_id'] = $coin[$i]['product_gold_id'];
					}
				}
			}
			
            if (strpos('Thanh toán', $actionSubmit) !== false) {/////////////////////NAP TIEN VAO GAME////////////////////////
                if($parameters['role_id']==''){
                    $messages = 'Lỗi, không tìm thấy nhân vật';
                }else if($parameters['in_amount']==0){
                    $messages = 'Không nhận được giá trị gói bạn chọn, hãy thử lại';
                }else{				
					
                    if($parameters['in_amount']<=$balance){
						/*echo "<pre>";
						print_r($coin);
						print_r($parameters);exit();*/
                        if($parameters['in_gold']!=0||($parameters['card_month']==3&&$parameters['in_gold']==0)){
							
                            if($parameters['in_amount']>0){
                                $parameters['in_type'] = 'GOLD';
                                $parameters['transId'] = strtolower($parameters['in_type'].$parameters['product_id'].time().substr(str_shuffle("abcdefghijklmnopqrstuvwxyz"), 0, 6));
                                //////////////////////CALL API UPDATE BALANCE
                                $resultUpdate = $this->getPaymentTable()->updatePayment($parameters, $this->arrPaymentConfig); 								
                                if($resultUpdate['status']==1){
                                    $balance = $resultUpdate['balance'];
                                    //////////////////////CALL API PAY GAME
                                    $config = $this->getServiceLocator()->get('config');
                                    $resultGame = $this->getGameTable($parameters['product_id'])->payGame($parameters, $config); 
                                    if($resultGame['status']) {
                                        $status     = true;
                                        $messages = $resultGame['messages'];
                                        $this->flashmessenger()->addMessage($messages);
                                        return $this->redirect()->toUrl('/payment'.'/'.$product_slug.".html");
                                    }    
                                }else if($resultUpdate['status']==-9){
                                    $messages = 'Số dư không đủ để thực hiện giao dịch';
                                }else{
                                    $messages = 'Lỗi trừ tiền trong ví, hãy liên hệ với bộ phận CSKH để được hỗ trợ';
                                }
                            }else{
                                $messages = 'Giá trị amount không hợp lệ';
                            }
                        }else{
                            $messages = 'Gói bạn chọn không tồn tại';
                        }
                    }else{
                        $messages = 'Số dư không đủ để thực hiện giao dịch';
                    }
                }
            }else if (strpos('Nạp ngay', $actionSubmit) !== false) {///////////////////////NAP TIEN VAO VI//////////////////////
				if($product_item->agent=='m003' && $parameters['in_type']=='VTC'){
					$messages = 'Loại thẻ tạm thời chưa được hỗ trợ';
				}elseif($parameters['amount_pay']!=0){///////////////////////NAP TIEN VAO VI BY ATM//////////////////////
                    if(10000 <= $parameters['amount_pay'] && $parameters['amount_pay'] <= 2000000){
                        $resultAtmLink = $this->getPaymentTable()->chargeAtmPayment($parameters, $this->arrAtmConfig); 
                        return $this->redirect()->toUrl($resultAtmLink);
                    }else{
                        $messages = 'Mệnh giá nạp tiền phải từ 10,000đ - 2,000,000đ';
                    }
                }else if($parameters['in_pin']!='' && $parameters['in_serie']!=''){////////////////////NAP TIEN VAO VI BY CARD////////////////////
                    $resultCard = $this->getPaymentTable()->chargeCardPayment($parameters, $this->arrPaymentConfig);
                    if($resultCard['status']==1){
                        $status = true;
                        $balance = $resultCard['balance'];
                    }    
                    $messages = $resultCard['cardMessage'];
                }else{
                    $messages = 'Lỗi không nhận được cú pháp nạp của bạn, f5 lại trình duyệt và thử lại';
                }
            }else{
                $messages = 'Lỗi không nhận được cú  pháp của bạn, f5 lại trình duyệt và thử lại';
            }
        }
        $statusChargeAtm = $this->getStatusChargeATM();		
        $this->layout('layout/passport');
        return array(
            'form'      => $form,
            'name'      => $product_item->name,
            'agent'      => $product_item->agent,
            'username'  => $parameters['in_username'],
            'id_user'  =>  $parameters['id_user'],
            'balance'   => $balance,
            'statusChargeAtm'   => $statusChargeAtm,
            'messages'  => $messages,
            'is_pay'  => $is_pay,
            'is_amount'  => $is_amount,
			'gold_id'	=> $gold_id,
            'role_id'	=> $parameters['role_id'],
            'status'  => $status,
            'messagesSale'    => $this->data['sale'],
            'listServers' => $listServers,
            'alert_sucssess'      => $this->flashmessenger()->getMessages()
        );
    }
    
    

    public function getroleAction() {		
        $request = $this->getRequest();
        $response = $this->getResponse();
        if ($request->isPost()) {
            $post_data = $request->getPost();
            $server = $post_data['server_id'];
            $agent = $post_data['agent'];
            $id_users = (String) $post_data['id_user'];
			if(isset($post_data['username'])){
				$username = $post_data['username'];
			}else{
				$username = $this->getAuthServices()->getIdentity();
			}
			if($agent!='m002' && $agent!='m003' && $agent!='m005'){
                $listRole = $this->getRoleTable()->getRole($agent, $id_users, $username, $server);
			}else{
				$listRole=array();
			}
            $status = false;
            if(!empty($listRole)){
                //////////////////////GET ROLE LOCAL
				$txtappent = '';
				$role_name_set = '';
				$i=0;
				foreach($listRole as $role){
					$role_id = $role['role_id'];
					$role_name = $role['role_name'];
					$check = '';
					if($i==0){
						$role_name_set = $role_name;
					}
					elseif($role_id==$post_data['role']){
						$check = 'selected';
						$role_name_set = $role_name;
					}
					$txtappent .= "<option value='".$role_id."' ".$check.">".$role_name."</option>";
					$i++;
				}
                
                $appent="<label for='role_list' class='col-sm-12 controll-label' style='padding-left:0;'>  Chọn nhân vật:</label>";
                $appent=$appent."<select name='role_id' class='form-control valid' id='server_role' aria-required='true' aria-invalid='false'>";
                $appent=$appent.$txtappent;
                $appent=$appent."</select>";
				$appent .= '<input id="role_name" type = "hidden" name = "role_name" value = "'.$role_name_set.'" />';
                $coin = $this->object_to_array($this->getCoin($agent));
                $coinAppent = '';
                if($agent=='h001'){
                    for($i = 0; $i< count($coin); $i++){
                        if($post_data['gold_id'] && $coin[$i]['amount'] == $post_data['gold_id'])
                            $coinAppent=$coinAppent.'<div class="col-md-3 col-sm-3 col-xs-6 item-box"><img src="img/iconcoin/'.$coin[$i]['image'].'" alt="" class="img-thumbnail img-check check"><input type="radio" name="amount_game" id="item4" value="'.$coin[$i]['amount'].'" data-id="'.$coin[$i]['gold'].'" data-product="' .$coin[$i]['product_gold_id']. '" class="hidden" autocomplete="off"></div>';
                        else
                            $coinAppent=$coinAppent.'<div class="col-md-3 col-sm-3 col-xs-6 item-box"><img src="img/iconcoin/'.$coin[$i]['image'].'" alt="" class="img-thumbnail img-check"><input type="radio" name="amount_game" id="item4" value="'.$coin[$i]['amount'].'" data-id="'.$coin[$i]['gold'].'" data-product="' .$coin[$i]['product_gold_id']. '" class="hidden" autocomplete="off"></div>';
                    }
                }else{
                    for($i = 0; $i< count($coin); $i++){
                        if($post_data['gold_id'] && $coin[$i]['amount'] == $post_data['gold_id'])
                            $coinAppent=$coinAppent.'<div class="col-md-3 col-sm-3 col-xs-6 item-box"><img src="img/iconcoin/'.$coin[$i]['image'].'" alt="" class="img-thumbnail img-check check"><input type="radio" name="amount_game" id="item4" value="'.$coin[$i]['amount'].'" data-id="'.$coin[$i]['gold'].'" data-product="' .$coin[$i]['amount']. '" class="hidden" autocomplete="off"></div>';
                        else
                            $coinAppent=$coinAppent.'<div class="col-md-3 col-sm-3 col-xs-6 item-box"><img src="img/iconcoin/'.$coin[$i]['image'].'" alt="" class="img-thumbnail img-check"><input type="radio" name="amount_game" id="item4" value="'.$coin[$i]['amount'].'" data-id="'.$coin[$i]['gold'].'" data-product="' .$coin[$i]['amount']. '" class="hidden" autocomplete="off"></div>';
                    }
                }
                $response->setContent(\Zend\Json\Json::encode(array('status' => true, 'result' => $appent, 'resultCoin' => $coinAppent)));
            }else{ 
              
                //////////////////////CALL API GET ROLE
                $config = $this->getServiceLocator()->get('config');
                $result = $this->getGameTable($agent)->getRole($agent, $id_users, $username, $server, $config);
				
                if($result['status']){
					$listRole = json_decode($result['data']);
					
					$txtappent = '';
					
					if(!empty($listRole)){
						$i=0;
						$role_name_set = '';
						foreach($listRole as $role){
							$role_id = $role->role_id;
							$role_name = $role->role_name;
							$check = '';
							if($i==0){
								$role_name_set = $role_name;
							}
							elseif($role_id==$post_data['role']){
								$check = 'selected';
								$role_name_set = $role_name;
							}
							$txtappent .= "<option value='".$role_id."' ".$check.">".$role_name."</option>";
							$i++;
							if($agent!='m002'){
								$role = new Role();
								$role->agent = $agent;
								$role->username = $username;
								$role->userid = $id_users;
								$role->role_id = $role_id;
								$role->role_name = $role_name;
								$role->server_id = $server;
								$this->getRoleTable()->saveRole($role);
							}
						}
						$appent="<label for='role_list' class='col-sm-12 controll-label' style='padding-left:0;'>  Chọn nhân vật:</label>";
						$appent=$appent."<select name='role_id' class='form-control valid' id='server_role' aria-required='true' aria-invalid='false'>";
						$appent=$appent.$txtappent;
						$appent=$appent."</select>";
						$appent .= '<input id="role_name" type = "hidden" name = "role_name" value = "'.$role_name_set.'" />';
						$coin = $this->object_to_array($this->getCoin($agent));
						$coinAppentGold = '';
						$coinAppentGift = '';
						$coinAppent = '';
						$active = '';
						if($agent=='h001'){
							for($i = 0; $i< count($coin); $i++){
								if($post_data['gold_id'] && $coin[$i]['amount'] == $post_data['gold_id'])
									$coinAppent=$coinAppent.'<div class="col-md-3 col-sm-3 col-xs-6 item-box"><img src="img/iconcoin/'.$coin[$i]['image'].'" alt="" class="img-thumbnail img-check check"><input type="radio" name="amount_game" id="item4" value="'.$coin[$i]['amount'].'" data-id="'.$coin[$i]['gold'].'" data-product="' .$coin[$i]['product_gold_id']. '" class="hidden" autocomplete="off"></div>';
								else
									$coinAppent=$coinAppent.'<div class="col-md-3 col-sm-3 col-xs-6 item-box"><img src="img/iconcoin/'.$coin[$i]['image'].'" alt="" class="img-thumbnail img-check"><input type="radio" name="amount_game" id="item4" value="'.$coin[$i]['amount'].'" data-id="'.$coin[$i]['gold'].'" data-product="' .$coin[$i]['product_gold_id']. '" class="hidden" autocomplete="off"></div>';
							}
						}elseif($agent=='m002' || $agent == 'm003'){
							for($i = 0; $i< count($coin); $i++){
								$check = '';
								if($post_data['gold_id'] && $coin[$i]['product_gold_id'] == $post_data['gold_id']){
									$check = 'check';
									if($coin[$i]['gold']>0){
										$active = 'gold';
									}else{
										$active = 'gift';
									}
								}
								if($coin[$i]['gold']==0||$coin[$i]['gold']==null||$coin[$i]['gold']==''){
									$coinAppentGift=$coinAppentGift.'<div class="col-md-3 col-sm-3 col-xs-6 item-box"><img src="img/iconcoin/'.$coin[$i]['image'].'" alt="" class="img-thumbnail img-check '.$check.'"><input type="radio" name="amount_game" id="item4" value="'.$coin[$i]['product_gold_id'].'" data-id="'.$coin[$i]['gold'].'" data-product="' .$coin[$i]['amount']. '" class="hidden" autocomplete="off" data-product="' .$coin[$i]['product_gold_id']. '"></div>';
								}else{
									$coinAppentGold=$coinAppentGold.'<div class="col-md-3 col-sm-3 col-xs-6 item-box"><img src="img/iconcoin/'.$coin[$i]['image'].'" alt="" class="img-thumbnail img-check '.$check.'"><input type="radio" name="amount_game" id="item4" value="'.$coin[$i]['product_gold_id'].'" data-id="'.$coin[$i]['gold'].'" data-product="' .$coin[$i]['amount']. '" class="hidden" autocomplete="off" data-product="' .$coin[$i]['product_gold_id']. '"></div>';
								}
							}
						}else{
							for($i = 0; $i< count($coin); $i++){
							if($post_data['gold_id'] && $coin[$i]['amount'] == $post_data['gold_id'])
								$coinAppent=$coinAppent.'<div class="col-md-3 col-sm-3 col-xs-6 item-box"><img src="img/iconcoin/'.$coin[$i]['image'].'" alt="" class="img-thumbnail img-check check"><input type="radio" name="amount_game" id="item4" value="'.$coin[$i]['amount'].'" data-id="'.$coin[$i]['gold'].'" data-product="' .$coin[$i]['amount']. '" class="hidden" autocomplete="off"></div>';
							else
								$coinAppent=$coinAppent.'<div class="col-md-3 col-sm-3 col-xs-6 item-box"><img src="img/iconcoin/'.$coin[$i]['image'].'" alt="" class="img-thumbnail img-check"><input type="radio" name="amount_game" id="item4" value="'.$coin[$i]['amount'].'" data-id="'.$coin[$i]['gold'].'" data-product="' .$coin[$i]['amount']. '" class="hidden" autocomplete="off"></div>';
						}							
						}
						
						$response->setContent(\Zend\Json\Json::encode(array('status' => true, 
											'result' => $appent, 
											'resultCoin' => $coinAppent,
											'resultCoinGold'=>$coinAppentGold,
											'resultCoinGift'=>$coinAppentGift,
											'active'=>$active)));
					}else{
						$response->setContent(\Zend\Json\Json::encode(array('status' => false, 'result' => "<br><p>Không tìm thấy thông tin nhân vật</p>")));
					}
                }else{
                    $response->setContent(\Zend\Json\Json::encode(array('status' => false, 'result' => "<br><p>".$result['message']."</p>")));
                }
            }
        }
        return $response;
    }
	public function getgoldAction(){
		$request = $this->getRequest();
        $response = $this->getResponse();
        if ($request->isPost()) {
            $post_data = $request->getPost();
            $gold_id = $post_data['gold_id'];
            $agent = $post_data['agent'];
			/*kiem tra gia tri hop le*/
			$flag = true;
			if (!preg_match('/^[A-Za-z0-9]{1,10}$/',$gold_id)) {
				$validateFlag = FALSE;
				$flag = false;
			} elseif (!preg_match('/^[A-Za-z0-9]{2,6}$/', $agent)) {
				$validateFlag = FALSE;
				$flag = false;
			}
			if($flag){
				if($agent=='m002' || $agent == 'h001' || $agent == 'm003'){
					$result = $this->object_to_array($this->getCoinById($agent,$gold_id));
				}elseif($agent=='m001'){//
					$result = $this->object_to_array($this->getCoinByAmount($agent,$gold_id));
				}				
				if(!empty($result) && isset($result[0])){
					$response->setContent(\Zend\Json\Json::encode(array('status' => true, 'gold'=>$result[0]['gold'],'amount'=>$result[0]['amount'])));
				}
			}else{
				$response->setContent(\Zend\Json\Json::encode(array('status' => false)));
			}
		}
		return $response;
	}
}
