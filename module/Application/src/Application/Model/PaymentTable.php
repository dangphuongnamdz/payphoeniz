<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

 class PaymentTable
 {
    protected $tableGateway;
    
    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function getBalance($username, $agent, $config){
        $sign = md5 ( $config[$agent]['key'] . md5 ( $username ) . $config[$agent]['secret'] );
        $_WSDL_URI_reg = "/payment/getbalance";
        $link = $config['domain'].$_WSDL_URI_reg
                . '?agent='. $agent  . '&key='.urlencode($config[$agent]['key']) . '&username='.$username
                . '&sign='.$sign;
        $result = json_decode(file_get_contents($link));
        return $result;
    }

    public function updatePayment($parameters, $config){
        $_WSDL_URI_reg = "/payment/updatebalance";
        $parameters ['sign'] = md5 ( $config[$parameters['product_id']]['key'] . md5 ( $parameters ['in_username']) . $config[$parameters['product_id']]['secret'] );//
        $link = $config['domain'].$_WSDL_URI_reg
        . '?agent='.$parameters['product_id']  . '&key='.urlencode($config[$parameters['product_id']]['key']) . '&username='.$parameters['in_username'] 
        . '&gold='.$parameters['in_amount'] . '&action=-1&sign='.$parameters['sign'];
        $result = json_decode(file_get_contents($link));
        if($result->status==1) $parameters['cardMessage'] = 'Trừ tiền thành công'; else $parameters['cardMessage'] = 'Trừ tiền thất bại';
        if($result->status==1) $parameters['balance'] = $result->balance;
        $parameters['card_status'] = (Int) $result->status;
        $parameters['status'] = 1;
        $parameters['in_pin'] = $parameters['in_serie'] = '';
        //save log file update balance
        $string = '{ "date": "'.date('H:i:s Y-m-d').'", "service": "UpdateBalance'
            .'","agent": "'.$parameters['product_id']
            .'","productAgent": "'.$parameters['product_id']
            .'","username": "'.$parameters ['in_username']
            .'","userId": "'.$parameters ['id_user']
            .'","email": "'.$parameters ['in_email']
            .'","amount": "'.$parameters ['in_amount']
            .'","cardCode": "'.$parameters ['in_pin']
            .'","transactionId": "'.$parameters ['transId']
            .'","cardStatus": "'.$parameters['card_status']
            .'","balance": "'.$parameters['balance']
            .'","cardMessage": "'.$parameters['cardMessage'].'" }';
        $this->saveLogFile($parameters['product_id'], $string, $parameters['in_type']);
        switch ($parameters['card_status']) {
            case 1:
                $parameters['cardMessage'] = 'Trừ tiền thành công';
                break;
            case -9:
                $parameters['cardMessage'] = 'Số dư không đủ để thực hiện giao dịch';
                break;
            default:
                $parameters['cardMessage'] = 'Trừ tiền thất bại';
                break;
        }
        $result = $this->saveLogCharge($parameters);
        return ['status'    =>  $parameters['card_status'], 'balance'   =>  $parameters['balance']];
    }

    public function chargeAtmPayment($parameters, $config){
        $parameters['in_amount'] = $parameters['amount_pay'];
        $parameters['in_type'] = 'ATM';
        $parameters ['backUrl'] = $config[$parameters['product_id']]['backUrl'];
        $parameters ['returnUrl'] = $config[$parameters['product_id']]['returnUrl'];
        $parameters['return'] = $config[$parameters['product_id']]['return'];
        if($parameters['product_id'] != 'm005'){
            $parameters['transId'] = $parameters['product_id'].time().substr(str_shuffle("abcdefghijklmnopqrstuvwxyz"), 0, 6);
        }
        $parameters ['sign'] = md5 ( $config[$parameters['product_id']]['key'] . md5( $parameters['in_username'] ). $parameters['return'] . $config[$parameters['product_id']]['secret'] );
        $_WSDL_URI_reg = $config['WSDL_URI_reg'];
        $link = $config['domain'].$_WSDL_URI_reg
        .'?agent='.$parameters['product_id'].'&username='.$parameters['in_username'] 
        .'&amount='.$parameters['in_amount'].'&backUrl='.urlencode($parameters['backUrl']).'&returnUrl='.urlencode($parameters['returnUrl'])
        .'&key='.urlencode($config[$parameters['product_id']]['key']).'&return='.$parameters['return'].'&transId='.$parameters['transId'].'&sign='.$parameters['sign'];
        $parameters['status'] = $parameters['card_status']= -200;//Đang chờ trả về
        $parameters['cardMessage'] = 'Waiting return';
        $result = $this->saveLogCharge($parameters);
        //save log file
        $string = '{ "date": "'.date('H:i:s Y-m-d').'", "service": "WaitingChargeATM'
            .'","agent": "'.$parameters['product_id']
            .'","username": "'.$parameters ['in_username']
            .'","userId": "'.$parameters['id_user']
            .'","transactionId": "'.$parameters ['transId']
            .'","status": "'.$parameters ['card_status']
            .'","amount": "'.$parameters ['in_amount']
            .'","trans_id": "'.$parameters ['transId']
            .'","sign": "'.$parameters ['sign']
            .'","gold": "'.$parameters ['in_gold']
            .'","cardType": "'.$parameters ['in_type']
            .'","message": "'.$parameters ['cardMessage'].'" }';
        $this->saveLogFile($parameters['product_id'], $string, $parameters['in_type']);
        return $link;
    }

    public function chargeCardPayment($parameters, $config){
        $pattern = '/^[!#$%&*?@]$/';
        if(strlen($parameters['in_serie']) < 6 
        || strlen($parameters['in_serie']) > 15 
        || preg_match($pattern, $parameters['in_serie'])){
            $messages = 'Số serie không hợp lệ';
        }else if(strlen($parameters['in_pin']) < 6 
        || strlen($parameters['in_pin']) > 15 
        || preg_match($pattern, $parameters['in_pin'])){
            $messages = 'Mã pin không hợp lệ';                    
        }else{
            $parameters['transId'] = strtolower($parameters['in_type'].time().substr(str_shuffle("abcdefghijklmnopqrstuvwxyz"), 0, 6));
            $parameters ['sign'] = md5 ( $config[$parameters['product_id']]['key'] . md5 ( $parameters ['in_username'] . $parameters ['in_serie'] ) . $config[$parameters['product_id']]['secret'] );
            $_WSDL_URI_reg = "/payment/chargecardinwallet";
            $link = $config['domain'].$_WSDL_URI_reg
                    . '?agent='.$parameters['product_id']  . '&key='.urlencode($config[$parameters['product_id']]['key']) . '&username='.$parameters['in_username'] 
                    . '&cardCode='.$parameters['in_pin'] . '&cardSerial='.$parameters['in_serie']
                    . '&cardType='.$parameters['in_type'] . '&sign='.$parameters['sign'];
            $result = json_decode(file_get_contents($link));
            if($result){
                if($result->status==1) $parameters['status'] = 1; else $parameters['status'] = -1;
                if(isset($result->result->amount)) $parameters['in_amount'] = $result->result->amount; else $parameters['in_amount'] = 0;
                if(isset($result->result->gold)) $parameters['in_gold'] = $result->result->gold; else $parameters['in_gold'] = 0;
                if($result->status==1) $parameters['cardMessage'] = 'Nạp thẻ thành công'; else $parameters['cardMessage'] = 'Nạp thẻ thất bại';
                if(isset($result->result->transid)) $parameters['transId'] = $result->result->transid; else $parameters['transId'] = $parameters['transId'];
                if(isset($result->result->balance)) $parameters['balance'] = $result->result->balance;
                $parameters['card_status'] = (int)$result->status;
                //save log file
                $string = '{ "date": "'.date('H:i:s Y-m-d').'", "service": "ChargeCard'
                .'","productAgent": "'.$parameters['product_id']
                .'","username": "'.$parameters ['in_username']
                .'","userId": "'.$parameters ['id_user']
                .'","email": "'.$parameters ['in_email']
                .'","cardSerial": "'.$parameters ['in_serie']
                .'","cardCode": "'.$parameters ['in_pin']
                .'","cardType": "'.$parameters ['in_type']
                .'","sign": "'.$parameters ['sign']
                .'","transactionId": "'.$parameters ['transId']
                .'","amount": "'.$parameters ['in_amount']
                .'","cardMessage": "'.$parameters ['cardMessage']
                .'","cardTransid": "'.$parameters ['transId']
                .'","cardStatus": "'.$parameters ['card_status'].'" }';
                $this->saveLogFile($parameters['product_id'], $string, $parameters['in_type']);
                switch ($parameters['card_status']) {
                    case 1:
                        $parameters['cardMessage'] = 'Nạp thành công '.number_format($parameters['in_amount']).'đ vào ví';
                        break;
                    case -1:
                        $parameters['cardMessage'] = 'Thông tin thẻ không đúng';
                        break;
                    case -3:
                        $parameters['cardMessage'] = 'Số serial không hợp lệ';
                        break;
                    case -4:
                        $parameters['cardMessage'] = 'Mã pin không hợp lệ';
                        break;
                    case -5:
                        $parameters['cardMessage'] = 'Loại thẻ không hợp lệ';
                        break;
                    case 50:
                        $parameters['cardMessage'] = 'Thẻ đã sử dụng hoặc không tồn tại';
                        break;
                    case 51:
                        $parameters['cardMessage'] = 'Số serial không hợp lệ';
                        break;
                    case 52:
                        $parameters['cardMessage'] = 'Thông tin mã thẻ không đúng định dạng';
                        break;
                    case 53:
                        $parameters['cardMessage'] = 'Thông tin thẻ không đúng';
                        break;
                    case 59:
                        $parameters['cardMessage'] = 'Thẻ không tồn tại hoặc chưa được kích hoạt';
                        break;
                    default:
                        $parameters['cardMessage'] = 'Nạp thẻ thất bại';
                        break;
                }
                $result = $this->saveLogCharge($parameters);
                return ['status'    =>  $parameters['card_status'], 'cardMessage'    =>  $parameters['cardMessage'], 'balance'   =>  $parameters['balance']];
            }else{
                $messages = 'Thông tin thẻ không đúng: Giao dịch không thành công';
            }
        }
        return ['status' => -1000, 'cardMessage' => $messages];
    }

    public function saveLogCharge($parameters){
        $dbAdapter = $this->tableGateway->getAdapter();
        $driver = $dbAdapter->getDriver();
        $connection = $driver->getConnection();
        $sql = "CALL sp_card_insert_transaction ('"
        .$parameters['transId']."','".$parameters['in_username']."','".$parameters['in_email']."','".$parameters['in_pin']
        ."','".$parameters['in_serie']."','".$parameters['in_type']."',".$parameters['status'].",".$parameters['in_amount'].",".$parameters['in_gold']
        .",'".$parameters['cardMessage']."',".$parameters['card_status'].",'".$parameters['transId']."',".$parameters['balance'].",'".$parameters['product_id']."')";
        $res = $connection->execute($sql);
        $statement = $res->getResource();
        $result = $statement->fetchAll(\PDO::FETCH_OBJ);
        return $result;
    }

    public function saveLogFile($agent = "none", $message, $type = null){
        $format = '%message%';
        $nameLog = date('Y_m_d').".txt";
        $formatter = new \Zend\Log\Formatter\Simple($format);
        if ($type) {
            if($type=='ATM'){//charge atm
                $writer = new \Zend\Log\Writer\Stream(dirname(__DIR__).'/../../../../public/logs/'.$agent.'/atm/'.$nameLog);
            }else if($type=='exchange'){//call charge to game api charge
                $writer = new \Zend\Log\Writer\Stream(dirname(__DIR__).'/../../../../public/logs/'.$agent.'/exchange/'.$nameLog);
            }else if($type=='GOLD'){//update balance
                $writer = new \Zend\Log\Writer\Stream(dirname(__DIR__).'/../../../../public/logs/'.$agent.'/exchange/gold/'.$nameLog);
            }else{//charge card
                $writer = new \Zend\Log\Writer\Stream(dirname(__DIR__).'/../../../../public/logs/'.$agent.'/card/'.$type.'/'.$nameLog);
            }
        }
        $formatter = new \Zend\Log\Formatter\Simple('%message%');
        $writer->setFormatter($formatter);
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($message);
     }

}