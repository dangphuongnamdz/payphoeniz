<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Api\Model;
use Zend\Db\TableGateway\TableGateway;

 class LogIapChargeTable
 {
     protected $tableGateway;

     public function __construct(TableGateway $tableGateway)
     {
         $this->tableGateway = $tableGateway;
     }
     /**
      * get all log
      * @return [type] [description]
      */
     public function fetchAll()
     {
         $resultSet = $this->tableGateway->select();
         return $resultSet;
     }

     /**
      * get log by id
      * @param  [type] $id [description]
      * @return [type]     [description]
      */
     public function getLogIapCharge($id)
     {
         $id  = (int) $id;
         $rowset = $this->tableGateway->select(array('id' => $id));
         $row = $rowset->current();
         if (!$row) {
             throw new \Exception("Could not find row $id");
         }
         return $row;
     }

     /**
      * save or insert log
      * @param  LogIapCharge $log [description]
      * @return [type]            [description]
      */
     public function saveLogIapCharge(LogIapCharge $log){      
         $data = array(
             'transaction_id' => $log->transaction_id,
             'uid' => $log->uid,
             'server_id'  => $log->server_id,
             'product_id' => $log->product_id,
             'order_vnd'  => $log->order_vnd,
             'order_amount' => $log->order_amount,
             'time' => $log->time, 
             'payload'  => $log->payload,
             'agent' => $log->agent,
             'os'  => $log->os,
             'username'  => $log->username,
             'status'  => $log->status,
             'created_at' => new \Zend\Db\Sql\Expression("NOW()"),
             'updated_at' => new \Zend\Db\Sql\Expression("NOW()")
         );

         $id = (int) $log->id;
         if ($id == 0) {
            $this->tableGateway->insert($data);
            return $this->tableGateway->lastInsertValue;
         } else {
             if ($this->getLogIapCharge($id)) {
                 return $this->tableGateway->update($data, array('id' => $id));
             } else {
                 throw new \Exception('LogIapCharge id does not exist');
             }
         }
         return false;
     }
     /**
      * update status log by id
      * @param  LogIapCharge $log [description]
      * @return [type]            [description]
      */
     public function updateLogIapChargeStatus(LogIapCharge $log){      
         $data = array(
             'status'  => $log->status,
             'updated_at' => new \Zend\Db\Sql\Expression("NOW()")
         );
         $id = (int) $log->id;
         if ($this->getLogIapCharge($id)) {
             return $this->tableGateway->update($data, array('id' => $id));
         } else {
             throw new \Exception('LogIapCharge id does not exist');
         }
         return false;
     }

     public function deleteLogIapCharge($id)
     {
         //$this->tableGateway->delete(array('id' => (int) $id));
     }

    // public function addPayMountly($iusername, $iagent, $istatus, $iserver_id){
    //     $dbAdapter = $this->tableGateway->getAdapter();
    //     $driver = $dbAdapter->getDriver();
    //     $connection = $driver->getConnection();
    //     $sql = "CALL sp_card_check_mountly ('".$iusername."',".$istatus.",'".$iagent."', '".$iserver_id."')";
    //     $res = $connection->execute($sql);
    //     $statement = $res->getResource();
    //     $result = $statement->fetchAll(\PDO::FETCH_OBJ);
    // }
    
 }