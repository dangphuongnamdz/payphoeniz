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

 class PostTable
 {
     protected $tableGateway;
     public $id;
     public $category;
     public $view;

     public function __construct(TableGateway $tableGateway)
     {
         $this->tableGateway = $tableGateway;
     }
     //fetch slider
     public function fetchSlider()
     {
         $select = $this->tableGateway->getSql()->select();
         $select->join('users', 'users.id = posts.id_user', array('fullname'), 'left');
         $select->join('category', 'category.id = posts.id_category', array('tendanhmuc'), 'left');
         $select->order('order asc');
         $resultSet = $this->tableGateway->selectWith($select);
         return $resultSet;
     }

     //tang view
     public function tangview($id, $view){
        $id  = (int) $id;
        $view  = (int) $view;
        if($view==0)
            $view=1;
        $view=$view+1;
        $dataupdated = array(
            'view'  => $view
        );
        $this->tableGateway->update($dataupdated, array('id' => $id));
     }
     
     //get detail post
     public function fetchDetailPost($id)
     {
         $id  = (int) $id;
         $select = $this->tableGateway->getSql()->select();
         $select->join('users', 'users.id = posts.id_user', array('fullname'), 'left');
         $select->join('category', 'category.id = posts.id_category', array('tendanhmuc'), 'left');
         $select->where->like('posts.id', $id);
         $rowset = $this->tableGateway->selectWith($select);
         $row = $rowset->current();
         if (!$row) {
             throw new \Exception("Could not find row $id");
         }
         //tang luot view
         $this->tangview($id, $row->view);
         return $row;
     }

    //fetch cho search
    public function fetchPostSearch($key)
    {
        $keySearch = '%'.$key.'%';
        $select = $this->tableGateway->getSql()->select();
        $select->join('users', 'users.id = id_user', array('fullname'), 'left');
        $select->join('category', 'category.id = id_category', array('tendanhmuc'), 'left');
        $select->order('created_at desc')->limit(20);
        $select->where->like('post.title', (String)$keySearch);
        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet;
    }

    public function fetchPostCungChuyenMuc($id, $idchuyenmuc)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->join('users', 'users.id = posts.id_user', array('fullname'), 'left');
        $select->join('category', 'category.id = posts.id_category', array('tendanhmuc'), 'left');
        $select->where->like('posts.id_category', (Int)$idchuyenmuc);
        $select->where('posts.id <> '.(Int)$id);
        $select->order('posts.created_at desc')->limit(3);
        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet;
    }
}