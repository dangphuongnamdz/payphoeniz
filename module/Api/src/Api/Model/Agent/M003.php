<?php
namespace Api\Model\Agent;

class M003 extends AgentBase {
	/**
	 * Get Role information of Game 1
	 * @param  [type] $game      [description]
	 * @param  Array  $dataInput [description]
	 * @param  [type] $config    [description]
	 * @return [type]            [description]
	 */
	public function getRole(Array $dataInput, $config) { 
		$time = time();
		$config = $config['api'];
		$key = $config['role']['key'];
		$username = $dataInput['account'];//uid
		$server = $dataInput['server'];
		$sign = md5($username.$server.$time.$key);
		$link = $config['role']['url'];
		$link = sprintf($link,$username,$server,$time,$sign);
        $check_link = @file_get_contents($link);
        if ($check_link) {
            $result = json_decode(file_get_contents($link));		
            if ($result->status == 0){
               $data = array();
                for($i = 0; $i < count($result->data); $i++){
					$data[$i]['role_id'] = $result->data[$i]->id;
					$data[$i]['role_name'] = $result->data[$i]->RoleName;                          
                }
				return ['status'    =>  true, 'data'=>json_encode($data)  ];       
                
            }else{
                return ['status'    =>  false, 'message'    =>  'Không lấy được thông tin nhân vật'];
            }
        } else {
            return ['status'    =>  false, 'message'    =>  'Lỗi kết nối'];
        }
	}

	/**
	 * Exchange money of Game 1
	 * @param  [type] $game      [description]
	 * @param  Array  $dataInput [description]
	 * @param  [type] $config    [description]
	 * @return [type]            [description]
	 */
	public function exchange(Array $dataInput, $config, $key) {
		$parameters = $dataInput;
		$time = time();
		$parameters ['transId'] = date('YmdHis').substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'), 0, 6);
		
		$string =  $parameters ['account'].$parameters['role_id'].$parameters ['server'];
		$string .= $parameters['transId'].$parameters ['item_id'].$parameters ['amount'];
		$string .= $parameters['game_money'].$time.$config['api']['exchange']['key'];
		$sign = md5($string);


        $link = $config['api']['exchange']['url_refund'];
		$link = sprintf ($link,$parameters ['account'],$parameters ['role_id'], $parameters['server'],$parameters ['transId'],
							$parameters['item_id'],$parameters ['amount'],$parameters['game_money'], $time, $sign);
	    $result = [];
	    try {
	        $response = $this->getHttpRestJsonClient()->get($link);			
	        $result['status'] = $response['status'];
	        $result['data'] = $response;
	    } catch (Exception $ex) {
	    	$result = ['status' => -13, 'data' => $ex->getMessage()];
	    }	    
	    return $result;
	}

	/**
	 * Sign key hash of Game 1
	 * @param  [type] $data   [description]
	 * @param  [type] $apikey [description]
	 * @return [type]         [description]
	 */
	function genSign($data, $apikey) {
	    ksort($data);
	    $items = array();
	    foreach ($data as $key => $value) {
	        $items[] = $key . "=" . $value;
	    }
	    return md5(join("&", $items) . $apikey);
	}
}