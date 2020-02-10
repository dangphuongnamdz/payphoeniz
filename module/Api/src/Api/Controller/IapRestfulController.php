<?php
namespace Api\Controller;

use Api\Controller\AbstractRestfulJsonController;
use Zend\View\Model\JsonModel;

use Api\Model\LogIapCharge;
use Api\Model\LogIapChargeTable;
 
class IapRestfulController extends AbstractRestfulJsonController {    
    
    protected $logIapChargeTable;
    private $model;
    /**
     * [exchangeAction description]
     * @return [type] [description]
     */
    public function exchangeAction() {
        //get parameters 
        $parameters = [];
        $parameters['uid'] = $this->params()->fromQuery('uid','');//'20814119';
        $parameters['server_id'] = $this->params()->fromQuery('server_id','');//'1';//
        $parameters['product_id'] = $parameters['item_id'] = $this->params()->fromQuery('product_id','');
        $parameters['order_vnd'] = $parameters['money'] = $this->params()->fromQuery('order_vnd',0);
        //$parameters['order_amount'] = $parameters['gold'] = $this->params()->fromQuery('order_amount',0);
        $parameters['sign'] = $this->params()->fromQuery('sign', '');
        $parameters['payload'] = $this->params()->fromQuery('payload','');
        $parameters['agent'] = $this->params()->fromQuery('agent','');
        $parameters['os'] = $this->params()->fromQuery('os','');
        $parameters['username'] = $this->params()->fromQuery('username','');
		$parameters['roleid'] = $this->params()->fromQuery('roleid','0');
		/*$parameters['uid'] = '99844431';
        $parameters['server_id'] = '5';//
        $parameters['product_id'] = $parameters['item_id'] = '1';
        $parameters['order_vnd'] = $parameters['money'] = 390000;
        $parameters['order_amount'] = $parameters['gold'] = 150;
		$parameters['payload'] = 'QO_S4MLRBAJZLc12341';
        $parameters['sign'] = md5($parameters['uid'].$parameters['order_vnd'].$parameters['payload'].'5keym002');
        
        $parameters['agent'] = 'm002';
        $parameters['os'] = 'ios';
        $parameters['username'] = 'hanhtau001';
		$parameters['roleid'] = '141774';*/
        //validate param inputs
		if($parameters['agent']!='m002' && $parameters['agent']!='m005'&& $parameters['agent']!='m003'){
			unset($parameters['roleid']);
		}
        foreach ($parameters as $key => $value) {
            if (empty($value)) {
                return new JsonModel(array('status' => -6, 'result' => $key . ' is required!'));
            }
        }
        $parameters['order_amount'] = $parameters['gold'] = $this->params()->fromQuery('order_amount',0);
        //get config for api
        $config = $this->getServiceLocator()->get('config')['sdk'];

        // time & order_id
        $parameters['time'] = time();
        $parameters['transaction_id'] = date('YmdHis').substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'), 0, 8);
		$parameters['order_id'] = $parameters['transaction_id'];
        // use for 100d->game
        $parameters['order_id'] = $parameters['transaction_id']; 
        //validate sign
        //$sign =  md5($uid . $order_amount . $order_id . $server_id . $key);
		$agentModel = ucfirst($parameters['agent']);
        if ($this->getModel($agentModel) == null) {
            return new JsonModel(array('status' => -12, 'data' => ' Agent is not exists!'));
        }
		if($parameters['agent']=='m001'){
			$keyApp = $config['secret'];
		}else{
			$keyApp = $config['key'][$parameters['agent']];			
        }
        $sign = md5($parameters['uid'] . $parameters['order_amount'] . $parameters['payload'] . $parameters['server_id'] . $keyApp);
		if ($parameters ['sign'] != $sign ) {
            return new JsonModel(array('status' => -100, 'result' => 'sign is invalid!' . $sign)); 
        }
        
        //validate order_id        
        if (strlen($parameters['transaction_id']) < 15 || strlen($parameters['transaction_id']) > 24) {
            return new JsonModel(array('status' => -4, 'result' => 'order_id (transaction_id) is invalid!'));
        }

        // check domain by os
        if (!array_key_exists($parameters['os'], $config['domain'])&&$parameters['agent']=='m001') {
            return new JsonModel(array('status' => -9, 'result' => 'os is invalid (android, ios,...)!'));
        }
        $jsonResponse = 0;
        try {
			if($parameters['agent']=='m001'){
				$key_iap_charge =  $this->model->getServerByServerId($agentModel, $parameters['server_id']);
				$key = $key_iap_charge['data'][0]['key_iap_charge'];
				$signGame = md5($parameters['uid'] . $parameters['order_amount'] . $parameters['order_id'] . $parameters['server_id'] . $key);
			}else{
				$configGame = $this->model->getConfig($parameters['agent']);
				$key = $configGame['api']['exchange']['key'];
				
				if($parameters['agent']=='m002' || $parameters['agent']=='m005' || $parameters['agent']=='m003'){
					$signGame = MD5($parameters ['uid']. $parameters['roleid'].$parameters ['server_id'].$parameters ['order_id'].$parameters ['payload'].$parameters['product_id'].$parameters ['money'].$parameters ['gold'].$parameters ['time'].$key);
				}else{
					$signGame = MD5($parameters ['uid']. $parameters ['server_id'].$parameters ['order_id'].$parameters ['payload'].$parameters['product_id'].$parameters ['order_vnd'].$parameters ['order_amount'].$parameters ['time'].$key);
				}
			}
			
			$parameters ['sign'] = $signGame;
            $logIapCharge = new LogIapCharge();
            $parameters['status'] = 1;            
            $logIapCharge->exchangeArray($parameters);            
            $idLogIapChargeLasted = $this->getLogIapChargeTable()->saveLogIapCharge($logIapCharge);
            $string = '{ "date": "'.date('H:i:s Y-m-d').'", "service": "ChargeIAP'
                .'","agent": "'.$parameters['agent']
                .'","transaction_id": "'.$parameters['transaction_id']
                .'","uid": "'.$parameters ['uid']
                .'","username": "'.$parameters ['username']
                .'","server_id": "'.$parameters ['server_id']
                .'","product_id": "'.$parameters ['product_id']
                .'","order_vnd": "'.$parameters ['order_vnd']
                .'","order_amount": "'.$parameters ['order_amount']
                .'","order_id": "'.$parameters ['order_id']
                .'","time": "'.$parameters ['time']
                .'","payload": "'.$parameters ['payload']
                .'","os": "'.$parameters ['os']
                .'","status": "'.$parameters ['status'].'" }';
				
				$this->saveLogFile($parameters['agent'], $string);
            //if insert log success then call charge api
			
            if ($idLogIapChargeLasted) {
				if($parameters['agent']=='m001'){
					$jsonResponse = $this->getHttpRestJsonClient()->get($config['domain'][$parameters['os']]  . '?' . http_build_query($parameters));
				}else{
					if($parameters['agent']=='m002'){
						$parameters['username'] = $parameters['uid'];
                    }
                    if($parameters['agent']=='m005' || $parameters['agent']=='m003'){
                        $parameters['userid'] = $parameters['uid'];
                    }
					
					$link =$configGame['api']['exchange']['url']  . '?' . http_build_query($parameters);
					
					$jsonResponse = $this->getHttpRestJsonClient()->get($link);
					
					
					if($parameters['agent']=='m003'){						
						$jsonResponse = $jsonResponse['status'];
					}else{
						$jsonResponse = $jsonResponse['code'];
					}
				}
				
                if (intval($jsonResponse) != 1) { 
                    $parameters['id'] = $idLogIapChargeLasted;
                    $parameters['status'] = intval($jsonResponse);
                    $logIapCharge->exchangeArray($parameters);
                    $this->getLogIapChargeTable()->updateLogIapChargeStatus($logIapCharge);
                }
            } else { $string = '{ "date": "'.date('H:i:s Y-m-d').'", "service": "getExchangeIap'
                    .'","status": "-12'
                    .'","message": "insert into log db unsuccess!" }';
                $this->saveLogFile($parameters['agent'], $string, 'error');
                return new JsonModel(array('status' => -7, 'result' => 'insert into log unsuccess! ' . $idLogIapChargeLasted));
				
            }
        } catch(\Exception $ex) {      
            $string = '{ "date": "'.date('H:i:s Y-m-d').'", "service": "getExchangeIap'
                .'","status": "-13'
                .'","message": "Payload => '.$parameters['payload']." Error message => ".$ex->getMessage().'" }';
            $this->saveLogFile($parameters['agent'], $string, 'error');
            //save log 2
            $string = '{ "date": "'.date('H:i:s Y-m-d').'", "service": "ChargeIAP'
                .'","agent": "'.$parameters['agent']
                .'","transaction_id": "'.$parameters['transaction_id']
                .'","uid": "'.$parameters ['uid']
                .'","username": "'.$parameters ['username']
                .'","server_id": "'.$parameters ['server_id']
                .'","product_id": "'.$parameters ['product_id']
                .'","order_vnd": "'.$parameters ['order_vnd']
                .'","order_amount": "'.$parameters ['order_amount']
                .'","order_id": "'.$parameters ['order_id']
                .'","time": "'.$parameters ['time']
                .'","payload": "'.$parameters ['payload']
                .'","os": "'.$parameters ['os']
                .'","status": "-13" }';
            $this->saveLogFile($parameters['agent'], $string, 'error');
            if (strpos($ex->getMessage(), 'Warning: This payload is existed') !== false) {
                return new JsonModel(array('status' => -8, 'result' => 'payload is already used!'));
            }
        }
        /*call save log on pm*/
		$this->callSaveLogIapCharge($parameters);
        return new JsonModel(array('status' => $jsonResponse));
    }    

    /**
     * [getAlbumTable description]
     * @return [type] [description]
     */
    public function getLogIapChargeTable() {
         if (!$this->logIapChargeTable) {
             $sm = $this->getServiceLocator();
             $this->logIapChargeTable = $sm->get('Api\Model\LogIapChargeTable');
         }
         return $this->logIapChargeTable;
    }   
	public function callSaveLogIapCharge($parameters){
		$config = $this->getServiceLocator()->get('config')['payment'];
		$domain = $config['domain'].'/payment/savelogiap?';
		$secret = $config[$parameters['agent']]['secret'];
		$str = $parameters['transaction_id'].$parameters['uid'].$parameters['username'].$parameters['server_id'].$parameters['product_id'];
		$str .= $parameters['order_vnd'].$parameters['order_amount'].$parameters['agent'].$secret;
		$sign = md5($str);
		$url = $domain.'uid='.$parameters['uid'].'&server_id='.$parameters['server_id'].'&item_id='.$parameters['product_id'].'&order_vnd=';
		$url .= $parameters['order_vnd'].'&order_amount='.$parameters['order_amount'].'&transaction='.$parameters['transaction_id'].'&agent=';
		$url .= $parameters['agent'].'&username='.$parameters['username'].'&sign='.$sign;
		$result = file_get_contents($url);
		return $result;
	}
    /**
     * Get Game model by selected
     * @param  [type] $model [description]
     * @return [type]        [description]
     */
    public function getModel($model)
    {
        if (!$this->model) {
            $className = 'Api\\Model\\Agent\\' . $model;
            if (class_exists($className)) {
                $this->model = new $className();
            } else {
                $this->model = null;
            }
        }
        return $this->model;
    } 
    
}