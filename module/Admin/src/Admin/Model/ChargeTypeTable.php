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

 class ChargeTypeTable
 {
     protected $tableGateway;

     public function __construct(TableGateway $tableGateway)
     {
         $this->tableGateway = $tableGateway;
     }
      
     public function fetchAll()
     {
         $select = $this->tableGateway->getSql()->select();
         $resultSet = $this->tableGateway->selectWith($select);
         return $resultSet;
     }

     public function getChargeType($id)
     {
         $id  = (int) $id;
         $rowset = $this->tableGateway->select(array('id' => $id));
         $row = $rowset->current();
         if (!$row) {
             throw new \Exception("Could not find row $id");
         }
         return $row;
     }

     public function getExistChargeType($type, $id)
     {
         $type  = (String) $type;
         $id  = (Int) $id;
         $rowset = $this->tableGateway->select(array('type' => (String)$type));
         $row = $rowset->current();
         if ($row){
            if($row->id!=$id)
                return false;
            else
                return true;
         }
         return true;
     }
     
     public function saveChargeType(ChargeType $chargeType)
     {
         $data = array(
            'type'  => $chargeType->type,
            'name'  => $chargeType->name,
            'status'  => $chargeType->status,
            'created_at' => new \Zend\Db\Sql\Expression("NOW()"),
            'updated_at' => new \Zend\Db\Sql\Expression("NOW()")
        );
         $id = (int) $chargeType->id;
         //check exist
         if($this->getExistChargeType($chargeType->type, $id)==false){
             echo "Loại thẻ(Type) đã tồn tại";
             die();
         }
         if ($id == 0) {
             $this->tableGateway->insert($data);
         } else {
             if ($this->getChargeType($id)) {
                $dataupdated = array(
                    'type'  => $chargeType->type,
                    'name'  => $chargeType->name,
                    'status'  => $chargeType->status,
                    'updated_at' => new \Zend\Db\Sql\Expression("NOW()")
                );
                $this->tableGateway->update($dataupdated, array('id' => $id));
             } else {
                 throw new \Exception('User id does not exist');
             }
         }
     }

     public function deleteChargeType($id)
     {
         $this->tableGateway->delete(array('id' => (int) $id));
     }     
 }