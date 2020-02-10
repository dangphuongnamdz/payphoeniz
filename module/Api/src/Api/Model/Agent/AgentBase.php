<?php
namespace Api\Model\Agent;

use Api\Model\Agent\AgentInterface;
use Api\HttpRestJson\Client as HttpRestJsonClient;

abstract class AgentBase implements AgentInterface {
	protected $httpRestJsonClient;
	/**
	 * Get config of common & merge with agent
	 * @param  [type] $agent [description]
	 * @return [type]       [description]
	 */
	function getConfig($agent) {		
		$config = new \Zend\Config\Config(include dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config/agent.config.php');
		$gameConfigFile = dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/config/agent/' . strtolower($agent) . '.config.php';
		if(is_file($gameConfigFile)) {		    
		    $gameConfig  = new \Zend\Config\Config(include $gameConfigFile, true);
		    $config->merge($gameConfig)->setReadOnly();		    
		}
		return $config;
	}

	/**
     * [getHttpRestJsonClient description]
     * @return [type] [description]
     */
    protected function getHttpRestJsonClient(){
        if (!$this->httpRestJsonClient) {
            $this->httpRestJsonClient = new HttpRestJsonClient();
        }
        return $this->httpRestJsonClient;
    }

    /**
     * Get Server List common use for all agent
     * @param  [type] $agent [description]
     * @return [type]       [description]
     */
   	public function getServer($agent)
    {
    	$config = new \Zend\Config\Config(include dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))).'/config/autoload/global.php');
    	$dbAdapter = new \Zend\Db\Adapter\Adapter($config['db']->toArray());
    	
        $sql       = 'SELECT id, server_name, server_id  FROM servers WHERE agent LIKE "' . $agent . '"';
        $selectData = array();
        try {
            $statement = $dbAdapter->query($sql);
            $result    = $statement->execute();            
            if ($result) {
                $data = [];               
                foreach ($result as $res) {
                    $data[] = $res;
                }
                $selectData = [
                    'status' => 1,
                    'data' => $data,
                ];
            }
        } catch (Exception $ex) {
            $selectData = [
                'status' => -200,
                'data' => $ex->getMessage(),
            ];
        }
        return $selectData;        
     }

     public function getServerByServerId($agent, $server_id)
     {
         $config = new \Zend\Config\Config(include dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))).'/config/autoload/global.php');
         $dbAdapter = new \Zend\Db\Adapter\Adapter($config['db']->toArray());
         
         $sql       = 'SELECT key_web_charge, key_iap_charge  FROM servers WHERE agent LIKE "' . $agent . '" and server_id LIKE "'. $server_id .'"';
         $selectData = array();
         try {
             $statement = $dbAdapter->query($sql);
             $result    = $statement->execute();            
             if ($result) {
                 $data = [];               
                 foreach ($result as $res) {
                     $data[] = $res;
                 }
                 $selectData = [
                     'status' => 1,
                     'data' => $data,
                 ];
             }
         } catch (Exception $ex) {
             $selectData = [
                 'status' => -200,
                 'data' => $ex->getMessage(),
             ];
         }
         return $selectData;        
      }

     public function getRole(Array $dataInput, $config) {
     }

     public function exchange(Array $dataInput, $config, $key) {
     }
}
