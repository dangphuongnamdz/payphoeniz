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

 class LogAgentTable
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
      * save or insert log
      * @param  LogIapCharge $log [description]
      * @return [type]            [description]
      */
     public function saveLog(LogAgent $log){      
         $data = array(
             'transaction_id' => $log->transaction_id,
             'account' => $log->account,
             'function'  => $log->function,
             'agent' => $log->agent,
             'data_input'  => $log->data_input,
             'data_output'  => $log->data_output,
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
                 throw new \Exception('Id does not exist');
             }
         }
         return false;
     }
     /**
      * update status log by id
      * @param  LogIapCharge $log [description]
      * @return [type]            [description]
      */
     public function updateLogStatus(LogIapCharge $log){      
         $data = array(
             'status'  => $log->status,
             'updated_at' => new \Zend\Db\Sql\Expression("NOW()")
         );
         $id = (int) $log->id;
         if ($this->getLogIapCharge($id)) {
             return $this->tableGateway->update($data, array('id' => $id));
         } else {
             throw new \Exception('ID does not exist');
         }
         return false;
     }

     public function deleteLogIapCharge($id)
     {
         //$this->tableGateway->delete(array('id' => (int) $id));
     }
 }