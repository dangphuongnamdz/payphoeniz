<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Model\Agent;

use Zend\Db\TableGateway\TableGateway;

 class M002
 {
    protected $tableGateway;
    
    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function payGame($parameters, $config){ 
		
		$config = $config['game'][$parameters['product_id']];
		$time = time();
		$string =  $parameters ['id_user'].$parameters['role_id'].$parameters['server_id'];
		$string .= $parameters ['transId'].$parameters['product_gold_id'].$parameters ['in_amount'];
		$string .= $parameters['in_gold'].$time.$config['keyPay'];
		$sign = md5($string);
        $link = $config['domainCharge'];
		$link = sprintf ( $link,$parameters ['id_user'],$parameters['role_id'], 
						$parameters['server_id'],$parameters ['transId'],$parameters['product_gold_id'],
						$parameters ['in_amount'],$parameters['in_gold'], $time, $sign);
		//echo $link;exit();
        $result = json_decode(file_get_contents($link));
	
        if($result->code==1) $parameters['cardMessage'] = 'Nạp vào game thành công'; else $parameters['cardMessage'] = 'Nạp vào game thất bại';
        $parameters['card_status'] = $result->code;
		$data = array();
		$data['date'] = date('H:i:s Y-m-d');
		$data['service'] = 'ChargeCard';
		$data['agent'] = $parameters['product_id'];
		$data['productAgent'] = $parameters['product_id'];
		$data['username'] = $parameters ['in_username'];
		$data['role_id'] = $parameters ['role_id'];
		$data['userId'] = $parameters ['id_user'];
		$data['email'] = $parameters ['in_email'];
		$data['amount'] = $parameters ['in_amount'];
		$data['transactionId'] = $parameters ['transId'];
		$data['cardStatus'] = $parameters['card_status'];
		$data['cardMessage'] = $parameters['cardMessage'];
		$data['link'] = $link;
        
        $this->saveLogFile($parameters['product_id'],json_encode($data));
        $resultPay = $this->saveLogPay($parameters);
        if ($parameters['card_status'] == 1){
            return ['status'    =>  true, 'messages'    =>  'Nạp tiền vào game thành công'];
        }else {
            return ['status'    =>  false, 'messages'    =>  'Lỗi nạp thẻ vào game, hãy liên hệ với bộ phận CSKH để được hỗ trợ'];
        }
    }
    
    public function getRole($agent, $id_users, $username, $server, $config){
        $time = time();
		$config = $config['game'][$agent];
		$key = $config['keyRole'];
		$sign = md5($id_users.$server.$time.$key);
        $link = $config['domainRole'];
		$link = sprintf($link,$id_users,$server,$time,$sign);
	
        $check_link = @file_get_contents($link);
        if ($check_link) {
            $result = json_decode(file_get_contents($link));
            if ($result->status == 0){
				$data = array();
				$i=0;
				foreach($result->data as $role){
					$data[$i]['role_id']=$role->id;
					$data[$i]['role_name']=$role->roleName;
					$i++;
				}
               
                return ['status'    =>  true, 'data'=>json_encode($data)];        
                
            }else{
                return ['status'    =>  false, 'message'    =>  'Không lấy được thông tin nhân vật'];
            }
        } else {
            return ['status'    =>  false, 'message'    =>  'Lỗi kết nối'];
        }
		
    }

    public function saveLogFile($agent = "none", $message){
        $format = '%message%';
        $nameLog = date('Y_m_d').".txt";
        $formatter = new \Zend\Log\Formatter\Simple($format);
        $writer = new \Zend\Log\Writer\Stream(dirname(__DIR__).'/../../../../../public/logs/'.$agent.'/exchange/'.$nameLog);
        $formatter = new \Zend\Log\Formatter\Simple('%message%');
        $writer->setFormatter($formatter);
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($message);
     }

     public function saveLogPay($parameters){		 
        $dbAdapter = $this->tableGateway->getAdapter();
        $driver = $dbAdapter->getDriver();
        $connection = $driver->getConnection();
        $sql = "CALL sp_card_create_paygame ('"
        .$parameters['transId']."',".$parameters['in_gold'].",'".$parameters['in_username']."','".$parameters['role_name']."','".$parameters['server_id']."','".$parameters['product_id']."',".$parameters['card_status'].")";   
        $res = $connection->execute($sql);
        $statement = $res->getResource();
        $result = $statement->fetchAll(\PDO::FETCH_OBJ);
        return $result;
    } 

}