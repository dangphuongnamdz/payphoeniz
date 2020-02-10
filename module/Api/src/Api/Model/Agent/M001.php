<?php
namespace Api\Model\Agent;

class M001 extends AgentBase {
	/**
	 * Get Role information of Game 1
	 * @param  [type] $game      [description]
	 * @param  Array  $dataInput [description]
	 * @param  [type] $config    [description]
	 * @return [type]            [description]
	 */
	public function getRole(Array $dataInput, $config) {
		$data = [
			'appid' => $config['api']['role']['app_id'],
		  	'gid' => $config['api']['game_id'],
		  	'opid' => $config['api']['op_id'],
		  	'sid' => $dataInput['server'],
		  	'uid' => $dataInput['account'],
		  	'time' => time(),
		];
		$data['sign'] = $this->genSign($data, $config['api']['role']['key']);
	    $result = [];	    
	    try {
	  		$response = $this->getHttpRestJsonClient()->post($config['api']['role']['url'], $data);
	  		$result['status'] = $response['code'] == 0 ? 1 : $response['code'];
	        if($response['code'] == 0) { 	        	
	        	if(isset($response['data']) && $response['data']) {// only 1 character   
	        		$result['data'][] = array(
	        			'role_id' => $response['data']['game_role_id'],
	        			'role_name' => $response['data']['role_name'],
	        		);
	        	} 
	        } else {
	        	$result['data'] = $response;
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
		$productId = $config['api']['exchange']['product'][$dataInput['amount']]['product_id'];
		if (empty($productId)) {
			return ['status' => -14, 'data' => 'Product ID is not exists!'];
		}
		$data = [
			'appid' => $config['api']['exchange']['app_id'],
			'product_id' => $productId,
		  	'gid' => $config['api']['game_id'],
		  	'opid' => $config['api']['op_id'],
		  	'sid' => $dataInput['server'],		  	
		  	'uid' => $dataInput['account'],
		  	'account_system_id' => $dataInput['account_system_id'],
		  	'time' => time(),
		  	'orderid' => date('YmdHis').substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'), 0, 6),
		  	'amount' => $dataInput['amount'],
		  	'game_money' => $dataInput['game_money'],		  	
		  	'currency' => 'VND',		  	
		];		
		$data['sign'] = $this->genSign($data, $key);
	    $result = [];
	    try {
	        $response = $this->getHttpRestJsonClient()->post($config['api']['exchange']['url'], $data);
	        $result['status'] = $response['code'] == 0 ? 1 : $response['code'];
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