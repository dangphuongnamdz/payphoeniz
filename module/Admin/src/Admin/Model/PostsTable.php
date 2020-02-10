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

 class PostsTable
 {
     protected $tableGateway;
     public $id_user;

     public function __construct(TableGateway $tableGateway)
     {
         $this->tableGateway = $tableGateway;
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
         $select->join('category', 'category.id = id_category', array('tendanhmuc'), 'left');
         $select->order('updated_at desc');
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

     public function getLastestSort(){
        $data = $this->object_to_array($this->fetchAll());
        for($i=0;$i<count($data);$i++){
           $datas = array(
               'order'  => (Int) ($data[$i]['order']+1)
           );
           $this->tableGateway->update($datas, array('id' => (Int) $data[$i]['id']));
       }
    }
     public function getPosts($id)
     {
         $id  = (int) $id;
         $rowset = $this->tableGateway->select(array('id' => $id));
         $row = $rowset->current();
         if (!$row) {
             throw new \Exception("Could not find row $id");
         }
         return $row;
     }
     
     public function savePosts(Posts $posts, $avatar, $avatar_mobile)
     {
        $session = new Container('User');
        $id_user= $session->offsetGet('id_user');
        if($posts->summary==null)
            $posts->summary = 'Chưa có dữ liệu';
        if($posts->content==null)
            $posts->content = 'Chưa có dữ liệu';
         $id = (int) $posts->id;
         $slug = $posts->slug;
         if (strpos($slug, 'http') !== false || strpos($slug, 'www') !== false)
            $slug = $posts->slug;
         else
            $slug = $this->stripUnicode($posts->title);
         if ($id == 0) {
            if($avatar=='')
                $avatar = 'no-image.png';
            if($avatar_mobile=='')
                $avatar_mobile = 'no-image-mobile.png';
            $data = array(
                'summary' => $posts->summary,
                'title'  => $posts->title,
                'status'  => 1,
                'order'  => 1,
                'created_at' => new \Zend\Db\Sql\Expression("NOW()"),
                'updated_at' => new \Zend\Db\Sql\Expression("NOW()"),
                'avatar'  => $avatar,
                'avatar_mobile'  => $avatar_mobile,
                'content'  => $posts->content,
                'id_user'  => $id_user,
                'id_category'  => $posts->id_category,
                'slug'  => $slug,
                'view'  => 1,
            );
            $this->getLastestSort();
            $this->tableGateway->insert($data);
         } else {
             if ($this->getPosts($id)) {
                if($avatar=='')
                    $dataupdated = array(
                        'summary' => $posts->summary,
                        'title'  => $posts->title,
                        'updated_at' => new \Zend\Db\Sql\Expression("NOW()"),
                        'status'  => 1,
                        'avatar_mobile'  => $avatar_mobile,    
                        'content'  => $posts->content,
                        'id_user'  => $id_user,
                        'id_category'  => $posts->id_category,
                        'slug'  => $slug,
                    );
                else if($avatar_mobile=='')
                    $dataupdated = array(
                        'summary' => $posts->summary,
                        'title'  => $posts->title,
                        'updated_at' => new \Zend\Db\Sql\Expression("NOW()"),
                        'status'  => 1,
                        'avatar'  => $avatar,     
                        'content'  => $posts->content,
                        'id_user'  => $id_user,
                        'id_category'  => $posts->id_category,
                        'slug'  => $slug,
                    );
                else if($avatar=='' && $avatar_mobile=='')
                    $dataupdated = array(
                        'summary' => $posts->summary,
                        'title'  => $posts->title,
                        'updated_at' => new \Zend\Db\Sql\Expression("NOW()"),
                        'status'  => 1, 
                        'content'  => $posts->content,
                        'id_user'  => $id_user,
                        'id_category'  => $posts->id_category,
                        'slug'  => $slug,
                    );
                else 
                    $dataupdated = array(
                        'summary' => $posts->summary,
                        'title'  => $posts->title,
                        'updated_at' => new \Zend\Db\Sql\Expression("NOW()"),
                        'status'  => 1,
                        'avatar'  => $avatar,    
                        'avatar_mobile'  => $avatar_mobile,    
                        'content'  => $posts->content,
                        'id_user'  => $id_user,
                        'id_category'  => $posts->id_category,
                        'slug'  => $slug,
                    );
                 $this->tableGateway->update($dataupdated, array('id' => $id));
             } else {
                 throw new \Exception('Posts id does not exist');
             }
         }
     }

     public function deletePosts($id)
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