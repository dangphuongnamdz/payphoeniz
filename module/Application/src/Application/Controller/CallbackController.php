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
use Zend\Session\Container;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Adapter;

class CallbackController extends AbstractActionController
{
    protected $authservices;
    protected $storage;
    protected $userTable;
    protected $productTable;
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

    public function getUserTable()
    {
        if (!$this->userTable) {
            $sm = $this->getServiceLocator();
            $this->userTable = $sm->get('Application\Model\UserTable');
        }
        return $this->userTable;
    }

    public function getGameTable($agent)
    {
        if (!$this->paymentTable) {
            $sm = $this->getServiceLocator();
            $this->paymentTable = $sm->get('Application\\Model\\Agent\\' . strtoupper($agent));
        }
        return $this->paymentTable;
    }

    public function getProductTable()
    {
        if (!$this->productTable) {
            $sm = $this->getServiceLocator();
            $this->productTable = $sm->get('Application\Model\ProductTable');
        }
        return $this->productTable;
    }

    public function updateLogCharge($parameters){		
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $driver = $dbAdapter->getDriver();
        $connection = $driver->getConnection();
        $sql = "CALL sp_card_update_transaction ('"
        .$parameters['trans_id']."',".$parameters['amount'].",".$parameters['gold']
        .",'".$parameters['cardMessage']."',".$parameters['card_status'].",".$parameters['status'].")";
        $res = $connection->execute($sql);
        $statement = $res->getResource();
        $result = $statement->fetchAll(\PDO::FETCH_OBJ);		
        return $result;
    }

    public function getHistoryTransTable()
    {
        if (!$this->HistoryTransTable) {
            $sm = $this->getServiceLocator();
            $this->HistoryTransTable = $sm->get('Application\Model\HistoryTransTable');
        }
        return $this->HistoryTransTable;
    }

    public function successchargeatmAction(){
      
        if (!$this->getAuthServices()->hasIdentity()){
            return $this->redirect()->toRoute('passport', array(
                'action' => 'login'
            ));
        }
    
        $config = $this->getServiceLocator()->get('config');
        $parameters['in_type'] = 'ATM';
        //get return param 
        $parameters['status'] = $parameters['card_status'] = $this->params()->fromQuery('status', null);
        $parameters['amount'] = $this->params()->fromQuery('amount', null);
        $parameters['trans_id'] = $this->params()->fromQuery('p_trans', $this->params()->fromQuery('trans_id', null));
        $parameters['in_transaction'] = $parameters['trans_id'];
        $parameters['sign'] = $this->params()->fromQuery('sign', null);
        $parameters['gold'] = $this->params()->fromQuery('gold', 0);
        $parameters['return_cardType'] = 'null';
        $parameters['cardMessage'] = $this->params()->fromQuery('message', null);
        $parameters['in_username'] = $this->getAuthServices()->getIdentity();
      
        foreach ($parameters as $key => $value) {
            if (empty($value)) {
                $response = $this->getResponse();
                $response->setContent(\Zend\Json\Json::encode(array('status' => false, 'data' => $key . ' is required!')));
                return $response;
            }
        }
       
        $product_item = $this->object_to_array($this->getProductTable()->fetchAll());
        
        for($i = 0; $i< count($product_item); $i++){
            if (strpos($parameters['trans_id'], $product_item[$i]['agent']) !== false)
                $parameters['product_id'] = $product_item[$i]['agent'];
        }
        if(isset($parameters['product_id']) && $parameters['product_id'] == 'm005'){
            $config_payment_rate = $config['payment'][$parameters['product_id']]['rate'];
            foreach ($config_payment_rate as $rate){
                if($rate['type'] == $parameters['in_type']){
                    $parameters['gold'] = (int)$parameters['gold']*$rate['rate'];
                }
            }
        }
        if($parameters['sign'] != md5($parameters['trans_id'] . $parameters['amount'] . $config['atm'][$parameters['product_id']]['secret'])){
            $response = $this->getResponse();
            $response->setContent(\Zend\Json\Json::encode(array('status' => false, 'result' => "Thông tin xác thực không chính xác")));
            return $response;
        }
        //update log db
        $resultLog = $this->updateLogCharge($parameters);
        if((int)$resultLog[0]->result != 1){
            $response = $this->getResponse();
            $response->setContent(\Zend\Json\Json::encode(array('status' => false, 'result' => "Cập nhập thông tin thất bại")));
            return $response;
        }

        //////////////////////////////////////GET INFO USER
        $result = $this->getUserTable()->getUserPassport($parameters['in_username'], $config['passport']);
        $parameters['id_user'] = $result['result']->id;
        ////paytogame
      
        if(isset($parameters['product_id']) && $parameters['product_id'] == 'm005'){
            $history_trans = $this->getHistoryTransTable()->getHistoryTrans($parameters['trans_id']);
            $parameters['server_id'] = $history_trans->server_id;
            $parameters['gold_id'] = 1;
            $parameters ['in_email'] = 'null';
            $results = $this->getGameTable($parameters['product_id'])->payGame($parameters, $config); 
            $parameters['card_status'] = $results['status'] ? 1 : -1;
            $parameters['cardMessage'] = $results['status'] ? 'Nạp vào game thành công' : 'Nạp vào game thất bại';
        }
     
        //save log file
        $string = '{ "date": "'.date('H:i:s Y-m-d').'", "service": "ChargeATM'
            .'","agent": "'.$parameters['product_id']
            .'","username": "'.$parameters ['in_username']
            .'","userId": "'.$parameters['id_user']
            .'","transactionId": "'.$parameters ['trans_id']
            .'","status": "'.$parameters ['status']
            .'","amount": "'.$parameters ['amount']
            .'","trans_id": "'.$parameters ['trans_id']
            .'","sign": "'.$parameters ['sign']
            .'","gold": "'.$parameters ['gold']
            .'","cardType": "'.$parameters ['return_cardType']
            .'","message": "'.$parameters ['cardMessage'].'" }';
        $this->saveLogFile($parameters['product_id'], $string, $parameters['in_type']);
        return $this->redirect()->toUrl($config['atm'][$parameters['product_id']]['successRedirect']);
    }

    public function errorchargeatmAction(){
        $status = urldecode($this->params()->fromQuery('status', null));
        switch ($status) {
            case 0:
                $message = 'Thông tin đại lý không đúng';
                break;
            case -6:
                $message = 'Kênh tạm đóng hoặc không hỗ trợ';
                break;
            case -7:
                $message = 'Mã giao dịch bị trùng';
                break;
            default:
                $message = 'Nạp tiền không thành công';
                break;
        }
        $result = new ViewModel();
        $result->setTerminal(true);
        $result->setVariables(array('status'    =>  $status, 'message' => $message));
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
