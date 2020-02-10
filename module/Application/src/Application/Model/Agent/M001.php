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

 class M001
 {
    protected $tableGateway;
    
    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function payGame($parameters, $config){
        $_WSDL_URI_reg = "/api/role/exchange";
        $link = $config['domain'].$_WSDL_URI_reg. '?agent='.$parameters['product_id']  . '&server='.$parameters['server_id'] . '&account_system_id=0060298&account='
        .$parameters['id_user'].'&amount='.$parameters['in_amount'].'&game_money='.$parameters['in_gold'];	
        $result = json_decode(file_get_contents($link));
        if($result->status==1) $parameters['cardMessage'] = 'Nạp vào game thành công'; else $parameters['cardMessage'] = 'Nạp vào game thất bại';
        $parameters['card_status'] = (int)$result->status;
        $string = '{ "date": "'.date('H:i:s Y-m-d').'", "service": "ChargeCard'
            .'","agent": "'.$parameters['product_id']
            .'","productAgent": "'.$parameters['product_id']
            .'","username": "'.$parameters ['in_username']
            .'","userId": "'.$parameters ['id_user']
            .'","email": "'.$parameters ['in_email']
            .'","amount": "'.$parameters ['in_amount']
            .'","transactionId": "'.$parameters ['transId']
            .'","cardStatus": "'.$parameters['card_status']
            .'","cardMessage": "'.$parameters['cardMessage']
            .'","link": "'.$link.'" }';
        $this->saveLogFile($parameters['product_id'], $string);
        $resultPay = $this->saveLogPay($parameters);
        if ($parameters['card_status'] == 1){
            return ['status'    =>  true, 'messages'    =>  'Nạp tiền vào game thành công'];
        }else {
            return ['status'    =>  false, 'messages'    =>  'Lỗi nạp thẻ vào game, hãy liên hệ với bộ phận CSKH để được hỗ trợ'];
        }
    }
    
    public function getRole($agent, $id_users, $username, $server, $config){
        $_WSDL_URI_reg = "/api/role/info";
        $link = $config['domain'].$_WSDL_URI_reg.'?agent='.$agent.'&server='.$server.'&account='.$id_users.'';
        $check_link = @file_get_contents($link);
        if ($check_link) {
            $result = json_decode(file_get_contents($link));
            if ($result->status == 1){
				$data = array();
                for($i = 0; $i < count($result); $i++){
					$data[$i]['role_id'] = $result->data[$i]->role_id;
					$data[$i]['role_name'] = $result->data[$i]->role_name;                          
                }
				return ['status'    =>  true, 'data'=>json_encode($data)  ];  
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