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

 class RoleTable
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

     public function getRole($agent, $id_user, $username, $server)
     {
         $rowset = $this->tableGateway->select(array('agent' => $agent, 'userid' => $id_user, 'username' => $username, 'server_id' => $server));
        //$row = $rowset->current();
         /* if (!$row) {
             return '';
         }*/
         return $rowset->toArray();
     }

     public function saveRole(Role $role)
     {
        $data = array(
            'agent'  => $role->agent,
            'username'  => $role->username,
            'userid'  => $role->userid,
            'role_name'  => $role->role_name,
            'role_id'  => $role->role_id,
            'server_id'  => $role->server_id,
            'created_at' => new \Zend\Db\Sql\Expression("NOW()"),
            'updated_at' => new \Zend\Db\Sql\Expression("NOW()"),
        );
        $this->tableGateway->insert($data);
    }
 }