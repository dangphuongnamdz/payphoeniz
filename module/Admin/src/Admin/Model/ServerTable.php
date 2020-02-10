<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Model;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Session\Container;

 class ServerTable
 {
     protected $tableGateway;
     public $id_user;
     public $role_agent;
     public $level;

     public function __construct(TableGateway $tableGateway)
     {
         $this->tableGateway = $tableGateway;
         $session = new Container('User');
         $this->id_user= $session->offsetGet('id_user');
         $this->role_agent= $session->offsetGet('role_agent');
         $this->level= $session->offsetGet('level');
     }

     public function fetchAll()
     {
         $select = $this->tableGateway->getSql()->select();
         if($this->level != 1){
            $stringArrRoleAgent = str_replace("]", ")", str_replace("[", "(", $this->role_agent));
            $select->where('agent IN ' . $stringArrRoleAgent);
         }
         $select->order('server_id asc');
         $resultSet = $this->tableGateway->selectWith($select);
         return $resultSet;
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
     public function getServer($id)
     {
         $id  = (int) $id;
         $rowset = $this->tableGateway->select(array('id' => $id));
         $row = $rowset->current();
         if (!$row) {
             throw new \Exception("Could not find row $id");
         }
         return $row;
     }

     public function getExistProduct($agent, $id)
     {
         $agent  = (Int) $agent;
         $id  = (String) $id;
         $rowset = $this->tableGateway->select(array('server_id' => $id,'product_id'=>$agent));
         $row = $rowset->current();
         if ($row){
                return false;
         }
         return true;
     }
	public function getserverDouble($id,$slug,$server_id,$product_id){
		$select = $this->tableGateway->getSql()->select();
		$select->where('id <>'.$id);
		$select->where('server_id="'.$server_id.'"');
		$select->where('product_id= '.$product_id);
		$resultSet = $this->tableGateway->selectWith($select);
		$resultSet = $this->object_to_array($resultSet);
		
		if(!empty($resultSet)){
			return -1;//server_id đã tồn tại
		}else{
			$select->where('id <>'.$id);
			$select->where('server_slug = "'.$slug.'"');
			$select->where('product_id= '.$product_id);
			$resultSet = $this->tableGateway->selectWith($select);
			$resultSet = $this->object_to_array($resultSet);
			if(!empty($resultSet)){
				return -2;//slug đã tồn tại
			}
		}
		return 1;
	}
     public function getSlugExistProduct($slug, $product_id)
     {
         $slug  = (String) $slug;
         $rowset = $this->tableGateway->select(array('server_slug' => (String)$slug,'product_id'=>$product_id));
         $row = $rowset->current();
         if ($row){
               return false;
        }
        return true;
     }

     public function updateStatusAll($arr, $type)
     {
        if($arr!=null){
            if($type==1){
                foreach($arr as $row){
                    $data = array(
                        'pay_status' => 1,
                        'updated_at' => new \Zend\Db\Sql\Expression("NOW()")
                    );       
                    $this->tableGateway->update($data, array('server_id' => $row));
                }
            }else{
                foreach($arr as $row){
                    $data = array(
                        'pay_status' => 2,
                        'updated_at' => new \Zend\Db\Sql\Expression("NOW()")
                    );       
                    $this->tableGateway->update($data, array('server_id' => $row));
                }
            }
        }
        return true;
     }
	public function editServer(Server $server, $agent=null){
		$id = $server->id;
		$slug = $server->server_slug;
		$product_id = $server->product_id;
		$server_id = $server->server_id;
		$check = $this->getserverDouble($id,$slug,$server_id,$product_id);
		switch($check){
			case "-1":
				echo "Server Id = $server->server_id đã tồn tại";
				 die();
				 break;
			case "-2":
				echo "Server Slug = $server->server_slug đã tồn tại";
				 die();
				 break;
			case 1:
				if ($this->getServer($id)) {
					$dataupdated = array(
						'server_id' => $server->server_id,
						'server_name' => $server->server_name,
						'server_status' => $server->server_status,
						'server_slug' => $server->server_slug,
						'pay_status' => $server->pay_status,
						'key_web_charge' => $server->key_web_charge,
						'key_iap_charge' => $server->key_iap_charge,
						'agent' => $agent,
						'product_id' => $server->product_id,
						'updated_at' => new \Zend\Db\Sql\Expression("NOW()")
					);       
					  $this->tableGateway->update($dataupdated, array('id' => $id));
				  } else {
					  throw new \Exception('Server id does not exist');
				  }
				  break;
			default:
				throw new \Exception('Server id does not exist');
				break;
		}
	}
     public function saveServer(Server $server, $agent=null)
     {
         $id = $server->id;
          //check exist agent
         
         $data = array(
            'server_id' => $server->server_id,
            'server_name' => $server->server_name,
            'server_status' => $server->server_status,
            'server_slug' => $server->server_slug,
            'pay_status' => $server->pay_status,
            'key_web_charge' => $server->key_web_charge,
            'key_iap_charge' => $server->key_iap_charge,
            // 'server_order' => $server->server_order,
            // 'server_group' => $server->server_group,
            'product_id' => $server->product_id,
            'agent'      => $agent,
            'created_at' => new \Zend\Db\Sql\Expression("NOW()"),
            'updated_at' => new \Zend\Db\Sql\Expression("NOW()")
        );
          if ($id == 0) {
			  if($this->getExistProduct($server->product_id, $server->server_id)==false){
				 echo "server_id $server->server_id đã tồn tại";
				 die();
			 }
			 //check exist slug
			 if($this->getSlugExistProduct($server->server_slug, $server->product_id)==false){
				 echo "Slug $server->server_slug đã tồn tại";
				 die();
			 }
              $this->tableGateway->insert($data);
          } else {
              if ($this->getServer($id)) {
                $dataupdated = array(
                    'server_id' => $server->server_id,
                    'server_name' => $server->server_name,
                    'server_status' => $server->server_status,
                    'server_slug' => $server->server_slug,
                    'pay_status' => $server->pay_status,
                    'key_web_charge' => $server->key_web_charge,
                    'key_iap_charge' => $server->key_iap_charge,
                    // 'server_order' => $server->server_order,
                    // 'server_group' => $server->server_group,
                    'agent' => $agent,
                    'product_id' => $server->product_id,
                    'updated_at' => new \Zend\Db\Sql\Expression("NOW()")
                );       
                  $this->tableGateway->update($dataupdated, array('id' => $id));
              } else {
                  throw new \Exception('Server id does not exist');
              }
          }
     }

     public function deleteServer($id)
     {
         $this->tableGateway->delete(array('id' => (int) $id));
     }
 }