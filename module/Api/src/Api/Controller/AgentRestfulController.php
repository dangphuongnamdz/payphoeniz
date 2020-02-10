<?php
namespace Api\Controller;

use Api\Controller\AbstractRestfulJsonController;
use Zend\View\Model\JsonModel;
use Api\Model\LogAgent;
use Api\Model\LogAgentTable;

class AgentRestfulController extends AbstractRestfulJsonController {
    private $model;
    private $logTable;
    /**
     * Get list server from DB
     * @return [type] [description]
     */
    public function serverInfoAction()
    {
        //get parameters 
        $parameters = [];
        $parameters['agent'] = $agent = $this->params()->fromQuery('agent', null);
       
        //validate param inputs
        foreach ($parameters as $key => $value) {
            if (empty($value)) {
                return new JsonModel(array('status' => -11, 'data' => $key . ' is required!'));
            }
        }                
        $agentModel = ucfirst($agent);
        if ($this->getModel($agentModel) == null) {
            return new JsonModel(array('status' => -12, 'data' => ' Agent is not exists!'));
        }

        $result = [];
        try {
            $result = $this->model->getServer($agent);
        } catch(\Exception $ex) {         
            $result = ['status'=>-13, 'data'=>$ex->getMessage()];   
            // $string = '{ "date": "'.date('H:i:s Y-m-d').'", "service": "getServerInfo'
            //     .'","status": "-13'
            //     .'","message": "Server info error message => '.$ex->getMessage().'" }';
            // $this->saveLogFile($agent, $string, 'error');
        }
        //$result return array with status key and array role info of api
        return new JsonModel($result);
    }

    /**
     * Get Role Information use for Ajax, after selected a server
     * @return [type] [description]
     */
    public function roleInfoAction()
    {
        //get parameters 
        $parameters = [];
        $parameters['agent'] = $agent = $this->params()->fromQuery('agent', null);
        $parameters['server'] = $this->params()->fromQuery('server', null);
        $parameters['account'] = $this->params()->fromQuery('account', null);
       
        //validate param inputs
        foreach ($parameters as $key => $value) {
            if (empty($value)) {
                return new JsonModel(array('status' => -11, 'data' => $key . ' is required!'));
            }
        }                
        $agentModel = ucfirst($agent);
        if ($this->getModel($agentModel) == null) {
            return new JsonModel(array('status' => -12, 'data' => ' Agent is not exists!'));
        }

        $result = [];
        try {
            $config = $this->model->getConfig($agent);
            $result = $this->model->getRole($parameters, $config);
        } catch(\Exception $ex) {    
            $result = ['status'=>-13, 'data'=>$ex->getMessage()];   
            // $string = '{ "date": "'.date('H:i:s Y-m-d').'", "service": "getRoleInfo'
            //     .'","status": "-13'
            //     .'","message": "Server info error message '.$ex->getMessage().'" }';
            // $this->saveLogFile($agent, $string, 'error');
        }
        //$result return array with status key and array role info of api
        return new JsonModel($result);
    }

    /**
     * [exchangeAction description]
     * @return [type] [description]
     */
    public function exchangeAction() {
        //get parameters 
        $parameters = [];
        $parameters['agent'] = $agent = $this->params()->fromQuery('agent', null);
        $parameters['account_system_id'] = $this->params()->fromQuery('account_system_id', 1);
        $parameters['server'] = $this->params()->fromQuery('server', null);
        $parameters['account'] = $this->params()->fromQuery('account', null);
        $parameters['amount'] = $this->params()->fromQuery('amount', 0);
        $parameters['game_money'] = $this->params()->fromQuery('game_money', 0);     
        $parameters['currency'] = $this->params()->fromQuery('currency', 'VND');
		$parameters['item_id'] = $this->params()->fromQuery('item_id', 0);
		$parameters['role_id'] = $this->params()->fromQuery('role_id', 0);

        // validate param inputs
        foreach ($parameters as $key => $value) {
            if (empty($value)) {
				if(($agent!='m002') && $key=='game_money'){
					return new JsonModel(array('status' => -11, 'data' => $key . ' is required!'));
				}
            }
        }

        $agentModel = ucfirst($agent);
        if ($this->getModel($agentModel) == null) {
            return new JsonModel(array('status' => -12, 'data' => ' Agent is not exists!'));
        }

        $result = [];
        try {
			$key ='';
            $config = $this->model->getConfig($agent);
			if($agent=='m001'){
				$key_web_charge =  $this->model->getServerByServerId($agent, $parameters['server']);
				$key = $key_web_charge['data'][0]['key_web_charge'];
			}
            $result = $this->model->exchange($parameters, $config, $key);
        } catch(\Exception $ex) {            
            $result = ['status'=>-13, 'data'=>$ex->getMessage()];
            
        }
		
        //save log
        $log = new LogAgent();
        $logParams = [];
        $logParams['status'] = $result['status'];
        $logParams['transaction_id'] = date('YmdHis').substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'), 0, 10);
        $logParams['account'] = $parameters['account']; 
        $logParams['account_system_id'] = $parameters['account_system_id']; 
        $logParams['function'] = __FUNCTION__;
        $logParams['agent'] = $parameters['agent'];
        $logParams['data_input'] = json_encode($parameters);
        $logParams['data_output'] = json_encode($result);
        $log->exchangeArray($logParams);            
        $this->getLogAgentTable()->saveLog($log);

        //$result return array with status key and array data info of api
        return new JsonModel($result);        
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

    /**
     * [getLogTable description]
     * @return [type] [description]
     */
    public function getLogAgentTable() {
         if (!$this->logTable) {
             $sm = $this->getServiceLocator();
             $this->logTable = $sm->get('Api\Model\LogAgentTable');
         }
         return $this->logTable;
    }
    
}