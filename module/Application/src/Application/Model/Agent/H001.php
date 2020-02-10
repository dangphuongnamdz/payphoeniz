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

 class H001
 {
    protected $tableGateway;
    
    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function payGame($parameters, $config){
        if($parameters['theThang']!=''){
            $parameters['product_gold_id'] = $parameters['theThang'];
        }
        $parameters['time'] = time();
        $parameters['sign'] = md5($parameters['transId'].$parameters['in_username'].$parameters['server_id']
        .$parameters['in_gold'].$parameters['in_amount'].$parameters['time'].$config['game'][$parameters['product_id']]['key']);  //    
        $link = $config['game'][$parameters['product_id']]['domain'].$config['game'][$parameters['product_id']]['WSDL_URI_pay']
        .'&user_name='.$parameters['in_username'].'&serverid='.$parameters['server_id'].'&order_id='.$parameters['transId']
        .'&money='.$parameters['in_amount'].'&coin='.$parameters['in_gold'].'&product_id='.$parameters['product_gold_id']
        .'&time='.$parameters['time'].'&sign='.$parameters['sign'];
        $result = json_decode(file_get_contents($link));
        if($result->code==0) $parameters['cardMessage'] = 'Nạp vào game thành công'; else $parameters['cardMessage'] = 'Nạp vào game thất bại';
        $parameters['card_status'] = (int)$result->code;
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
        if ($parameters['card_status'] == 0){
            $parameters['card_status'] = 1;
            $this->saveLogFile($parameters['product_id'], $string);
            $resultPay = $this->saveLogPay($parameters);
            return ['status'    =>  true, 'messages'    =>  'Nạp vào game thành công '.$parameters['in_gold'].' NB'];
        }else {
            if ($parameters['card_status'] == 1)
                $parameters['card_status'] = 0;
                $this->saveLogFile($parameters['product_id'], $string);
                $resultPay = $this->saveLogPay($parameters);
            return ['status'    =>  false, 'messages'    =>  'Lỗi nạp thẻ vào game, hãy liên hệ với bộ phận CSKH để được hỗ trợ'];
        }
    }
        
    public function getRole($agent, $id_users, $username, $server, $config){
        $time = time();
        $sign = md5($username . $config['game'][$agent]['is_adult'] . $time . $server . $config['game'][$agent]['key']);
        $link = $config['game'][$agent]['domain'] . $config['game'][$agent]['WSDL_URI_ROLE'] . '?user_name='.$username 
        . '&serverid='.$server . '&time=' . $time . '&sign=' . $sign;
        $check_link = @file_get_contents($link);
        if ($check_link) {
            $result = json_decode(file_get_contents($link));
            if ($result->status == 0){
				$data[0]['role_id'] = $result->data->ID;
				$data[0]['role_name'] = $result->data->RoleName;
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