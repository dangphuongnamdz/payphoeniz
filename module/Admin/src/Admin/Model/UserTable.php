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
use Zend\Paginator\Paginator;
use Zend\Session\Container;
use Zend\Math\Rand;
use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;

 class UserTable
 {
     protected $tableGateway;
     public $id_user;
     public function __construct(TableGateway $tableGateway)
     {
         $this->tableGateway = $tableGateway;
     }

     public function fetchAllUser($paginated=false)
     {
        $session = new Container('User');
         $id= $session->offsetGet('id_user');
         if ($paginated) {
             $select = new Select('users');
             $select->where('id <> '.$id);
             $select->order('created_at desc');
             $resultSetPrototype = new ResultSet();
             $resultSetPrototype->setArrayObjectPrototype(new User());
             $paginatorAdapter = new DbSelect(
                 $select,
                 $this->tableGateway->getAdapter(),
                 $resultSetPrototype
             ); 
             $paginator = new Paginator($paginatorAdapter);
             return $paginator;
         }
         $resultSet = $this->tableGateway->select();
         return $resultSet;
     }

     public function getUser($id)
     {
         $id  = (int) $id;
         $rowset = $this->tableGateway->select(array('id' => $id));
         $row = $rowset->current();
         if (!$row) {
             throw new \Exception("Could not find row $id");
         }
         return $row;
     }

     public function getUserByname($username)
     {
         $rowset = $this->tableGateway->select(array('username' => $username));
         $row = $rowset->current();
         return $row;
     }

     public function getUserByPasswordToken($passwordtoken)
     {
         $rowset = $this->tableGateway->select(array('passwordtoken' => $passwordtoken));
         $row = $rowset->current();
         if(!$row){
            return false;
        }
        // $userTokenDate = $row->dateresetpasswordtoken;
        // $now = new \Datetime('now');
        // $now = $now->getTimestamp();
        // if(($now - $userTokenDate) > 86400){ //24*60*60
        //     return false;
        // }
        return true;
     }

     public function getUserByEmail($email)
     {
         $rowset = $this->tableGateway->select(array('email' => $email));
         $row = $rowset->current();

         return $row;
     }
     
     public function saveUser(User $user)
     {
        if(!$user->fullname)
            $user->fullname = '';
        if(!$user->email)
            $user->email = '';
        if($user->role_agent)
            $arrRoleAgent = json_encode($user->role_agent);
        else
            $arrRoleAgent = null;
        $data = array(
            'username' => $user->username,
            'password'  => Md5($user->password),
            'fullname'  => $user->fullname,
            'status'  => $user->status,
            'level'  => $user->level,
            'email'  => $user->email,
            'role_agent'  => $arrRoleAgent,
            'created_at' => new \Zend\Db\Sql\Expression("NOW()"),
            'updated_at' => new \Zend\Db\Sql\Expression("NOW()"),
        );
         $id = (int) $user->id;
         if ($id == 0) {
            if($this->getUserByname($user->username)){
                throw new \Exception('Username exist');
            }
             $this->tableGateway->insert($data);
         } else {
             if ($this->getUser($id)) {
                $dataupdate = array(
                    'username' => $user->username,
                    'fullname'  => $user->fullname,
                    'status'  => $user->status,
                    'level'  => $user->level,
                    'email'  => $user->email,
                    'role_agent'  => $arrRoleAgent,
                    'updated_at' => new \Zend\Db\Sql\Expression("NOW()"),
                );
                $this->tableGateway->update($dataupdate, array('id' => $id));
             } else {
                 throw new \Exception('User id does not exist');
             }
         }
     }

     public function changePassword($newpassword, $id = null)
     {
        if(!$id){
            $session = new Container('User');
            $id= $session->offsetGet('id_user');
        }
        if ($this->getUser($id)) {
            $dataupdate = array(
                'passwordtoken'  => null,
                'dateresetpasswordtoken'  => null,
                'password' => Md5($newpassword),
                'updated_at' => new \Zend\Db\Sql\Expression("NOW()"),
            );
        $this->tableGateway->update($dataupdate, array('id' => $id));
        } else {
            throw new \Exception('User id does not exist');
        }
     }
     
     public function deleteUser($id)
     {
         $this->tableGateway->delete(array('id' => (int) $id));
     }

     public function createTokenPasswordReset($email)
     {
        $token = Rand::getString(32,"0123456789qwertyuiopasdfghjklzxcvbnm", true);
        $data = array(
            'passwordtoken'  => $token,
            'dateresetpasswordtoken' => new \Zend\Db\Sql\Expression("NOW()"),
        );
        //update token va token_date vao db
        $this->tableGateway->update($data, array('email' => $email));
        return $token;
    }
    
 }