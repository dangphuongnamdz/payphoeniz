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

 class ProductTable
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

     public function stripUnicode($str){
        if(!$str) return false;
        $unicode = array(
            'a'=>'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ',
            'A'=>'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ằ|Ẳ|Ẵ|Ặ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ',
            'd'=>'đ','D'=>'Đ',
            'e'=>'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
            'E'=>'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
            'i'=>'í|ì|ỉ|ĩ|ị',
            'I'=>'Í|Ì|Ỉ|Ĩ|Ị',
            'o'=>'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ',
            'O'=>'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ',
            'u'=>'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
            'U'=>'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
            'y'=>'ý|ỳ|ỷ|ỹ|ỵ','Y'=>'Ý|Ỳ|Ỷ|Ỹ|Ỵ'
        );
        foreach($unicode as $nonUnicode=>$uni) $str = preg_replace("/($uni)/i",$nonUnicode,$str);
        $str = str_replace(' ', '-', strtolower($str));
        $str = str_replace("----", "-", $str);
        $str = str_replace("---", "-", $str);
        $str = str_replace("--", "-", $str);
        return preg_replace('/[^A-Za-z0-9\-]/', '', $str);
      }
      

     public function fetchAll()
     {
         $select = $this->tableGateway->getSql()->select();
         $select->join('users', 'users.id = id_user', array('username'), 'left');
         if($this->level != 1){
            $stringArrRoleAgent = str_replace("]", ")", str_replace("[", "(", $this->role_agent));
            $select->where('agent IN ' . $stringArrRoleAgent);
         }
         $select->order('order ASC');
         $resultSet = $this->tableGateway->selectWith($select);
         return $resultSet;
     }

     public function getProduct($id)
     {
         $id  = (int) $id;
         $rowset = $this->tableGateway->select(array('id' => $id));
         $row = $rowset->current();
         if (!$row) {
             throw new \Exception("Could not find row $id");
         }
         return $row;
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

     public function getLastestSort(){
         $data = $this->object_to_array($this->fetchAll());
         for($i=0;$i<count($data);$i++){
            $datas = array(
                'order'  => (Int) ($data[$i]['order']+1)
            );
            $this->tableGateway->update($datas, array('id' => (Int) $data[$i]['id']));
        }
     }
     
     public function getExistProduct($agent, $id)
     {
         $agent  = (String) $agent;
         $id  = (String) $id;
         $rowset = $this->tableGateway->select(array('agent' => (String)$agent));
         $row = $rowset->current();
         if ($row){
             if($row->id!=$id)
                return false;
            else
                return true;
         }
         return true;
     }

     public function getSlugExistProduct($slug, $id)
     {
         $slug  = (String) $slug;
         $id  = (String) $id;
         $rowset = $this->tableGateway->select(array('slug' => (String)$slug));
         $row = $rowset->current();
         if ($row){
            if($row->id!=$id)
               return false;
           else
                return true;
        }
        return true;
     }
     
     public function saveProduct(Product $product, $avatar)
     {
        $id = (int) $product->id;
        $slug = $this->stripUnicode($product->name);
         //check exist agent
        if($this->getExistProduct($product->agent, $id)==false){
            echo "Agent $product->agent đã tồn tại";
            die();
        }
        //check exist slug
        if($this->getSlugExistProduct($slug, $id)==false){
            echo "Slug $slug đã tồn tại";
            die();
        }
        $data = array(
            'name'  => $product->name,
            'agent'  => $product->agent,
            'status'  => 1,
            'order'    => 1,
            'created_at' => new \Zend\Db\Sql\Expression("NOW()"),
            'updated_at' => new \Zend\Db\Sql\Expression("NOW()"),
            'payment_type'  => $product->payment_type,
            'avatar'  => $avatar,
            'id_user'  => $this->id_user,
            'slug'  => $slug,
			'limit_local'  => $product->limit_local,
			'url_redirect'  => $product->url_redirect,
        );
         if ($id == 0) {
             $this->getLastestSort();
             $this->tableGateway->insert($data);
         } else {
             if ($this->getProduct($id)) {
                if($avatar == ''){
                    $dataupdated = array(
                        'name'  => $product->name,
                        'agent'  => $product->agent,
                        'status'  => $product->status,
                        'updated_at' => new \Zend\Db\Sql\Expression("NOW()"),
                        'id_user'  => $this->id_user,
                        'payment_type'  => $product->payment_type,
                        'slug'  => $slug,
						'limit_local'  => $product->limit_local,
						'url_redirect'  => $product->url_redirect,
                    );
                }
                else{
                    $dataupdated = array(
                        'name'  => $product->name,
                        'agent'  => $product->agent,
                        'status'  => $product->status,
                        'updated_at' => new \Zend\Db\Sql\Expression("NOW()"),
                        'avatar'  => $avatar,
                        'id_user'  => $this->id_user,
                        'payment_type'  => $product->payment_type,                     
                        'slug'  => $slug,
						'limit_local'  => $product->limit_local,
						'url_redirect'  => $product->url_redirect,
                    );
                }
                 $this->tableGateway->update($dataupdated, array('id' => $id));
             } else {
                 throw new \Exception('Product id does not exist');
             }
         }
     }

     public function deleteProduct($id)
     {
         $this->tableGateway->delete(array('id' => (int) $id));
     }     

     public function savesort($desired_position, $current_position, $id_current_position){
        $data = $this->object_to_array($this->fetchAll());
        for($i = 0 ; $i < count($data) ; $i++) {
            //di chuyen xuong
            if($desired_position>$current_position){
                if($data[$i]['order']==$current_position){
                    $datas = array(
                        'order'  => (Int) $desired_position
                    );
                    $this->tableGateway->update($datas, array('id' => (Int) $data[$i]['id']));
                }
                if($data[$i]['order']>$current_position && $data[$i]['order']<=$desired_position){
                    $datas = array(
                        'order'  => (Int) ($data[$i]['order']-1)
                    );
                    $this->tableGateway->update($datas, array('id' => (Int) $data[$i]['id']));
                }
            //di chuyen len
            }else{
                if($data[$i]['order']==$current_position){
                    $datas = array(
                        'order'  => (Int) $desired_position
                    );
                    $this->tableGateway->update($datas, array('id' => (Int) $data[$i]['id']));
                }
                else if($data[$i]['order']>=$desired_position && $data[$i]['order'] < $current_position){
                    $datas = array(
                        'order'  => (Int) ($data[$i]['order']+1)
                    );
                    $this->tableGateway->update($datas, array('id' => (Int) $data[$i]['id']));
                }
            }
        }
        return true;
     }
 }