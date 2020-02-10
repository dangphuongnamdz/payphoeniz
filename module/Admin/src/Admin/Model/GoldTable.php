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
use Zend\Session\Container;

 class GoldTable
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
            $select->where('product_id IN ' . $stringArrRoleAgent);
         }
         $select->order('amount ASC');
         $resultSet = $this->tableGateway->selectWith($select);
         return $resultSet;
     }

     public function getGold($id)
     {
         $id  = (int) $id;
         $rowset = $this->tableGateway->select(array('id' => $id));
         $row = $rowset->current();
         if (!$row) {
             throw new \Exception("Could not find row $id");
         }
         return $row;
     }

     
     public function saveGold(Gold $gold, $image)
     {
         $data = array(
            'amount'  => $gold->amount,
            'gold'  => $gold->gold,
            'product_id'  => $gold->product_id,
            'product_gold_id'  => $gold->product_gold_id,
            'created_at' => new \Zend\Db\Sql\Expression("NOW()"),
            'updated_at' => new \Zend\Db\Sql\Expression("NOW()"),
            'image'  => $image,
        );
         $id = (int) $gold->id;
         if ($id == 0) {
             $this->tableGateway->insert($data);
         } else {
             if ($this->getGold($id)) {
                if($image == ''){
                    $dataupdated = array(
                        'amount'  => $gold->amount,
                        'gold'  => $gold->gold,
                        'product_id'  => $gold->product_id,
                        'product_gold_id'  => $gold->product_gold_id,            
                        'card_month'  => $gold->card_month,
                        'updated_at' => new \Zend\Db\Sql\Expression("NOW()"),
                    );
                }
                else{
                    $dataupdated = array(
                        'amount'  => $gold->amount,
                        'gold'  => $gold->gold,
                        'product_id'  => $gold->product_id,     
                        'product_gold_id'  => $gold->product_gold_id,            
                        'card_month'  => $gold->card_month,                        
                        'updated_at' => new \Zend\Db\Sql\Expression("NOW()"),
                        'image'  => $image,
                    );
                }
                $this->tableGateway->update($dataupdated, array('id' => $id));
             } else {
                 throw new \Exception('User id does not exist');
             }
         }
     }

     public function deleteGold($id)
     {
         $this->tableGateway->delete(array('id' => (int) $id));
     }     
 }