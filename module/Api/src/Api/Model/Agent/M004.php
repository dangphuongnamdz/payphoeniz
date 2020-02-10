<?php
namespace Api\Model\Agent;

class M004 extends AgentBase {
	/**
	 * Get Role information of Game 1
	 * @param  [type] $game      [description]
	 * @param  Array  $dataInput [description]
	 * @param  [type] $config    [description]
	 * @return [type]            [description]
	 */
	public function getRole(Array $dataInput, $config) { 		
		$time = time();
		$agent = $dataInput['agent'];
		$username = $dataInput['account'];
		$server = $dataInput['server'];
		$key = $config['api']['role']['key'];
		$sign = md5($username.$server.$time.$key);
		
        $link = $config['api']['role']['url'];
		$link = sprintf($link,$username,$server,$time,$sign);
	    $result = [];	    
	    try {
	  		$response = $this->getHttpRestJsonClient()->get($link);
	        if($response['status'] == 0) { 	        	
	        	if(isset($response['data']) && $response['data']) {// only 1 character   
	        		$result['data'][] = array(
	        			'role_id' => $response['data']['ID'],
	        			'role_name' => $response['data']['RoleName'],
	        		);
	        	} 
				$result['status'] = 1;
	        } else {
	        	$result['data'] = $response;
				$result['status'] = $response['status'];
	        }
	    } catch (Exception $ex) {
	        $result = ['status' => -13, 'data' => $ex->getMessage()];
	    }
	    return $result;
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
		$productId = $config['api']['exchange']['product'][$dataInput['amount']]['product_id'];
		if (empty($productId)) {
			return ['status' => -14, 'data' => 'Product ID is not exists!'];
		}
		$time = time();
		
		$parameters ['transId'] = date('YmdHis').substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'), 0, 6);
		$string =  $parameters ['account'].$parameters['server'].$parameters ['transId'];
		$string .= $productId.$parameters ['amount'].$parameters['game_money'].$time.$config['api']['exchange']['key'];
		$sign = md5($string);
        $link = $config['api']['exchange']['url'];
		$link = sprintf ( $link,$parameters ['account'], $parameters['server'],$parameters ['transId'],
							$productId,$parameters ['amount'],$parameters['game_money'], $time, $sign);
	    $result = [];
	    try {
	        $response = $this->getHttpRestJsonClient()->get($link);			
	        $result['status'] = $response['code'] ;
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