<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Model;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

 class ProductTable
 {
     protected $tableGateway;
     public function __construct(TableGateway $tableGateway)
     {
         $this->tableGateway = $tableGateway;
     }
     
     public function fetchAll()
     {
         $select = $this->tableGateway->getSql()->select();
		 $select->where('status=1');
         $select->order('order asc');
         $resultSet = $this->tableGateway->selectWith($select);
         return $resultSet;
     }
     public function getProduct($slug)
     {
         $slug  = (String) $slug;
         $rowset = $this->tableGateway->select(array('slug' => $slug));
         $row = $rowset->current();
         if (!$row) {
             throw new \Exception("Could not find row $slug");
         }
         return $row;
     }
}