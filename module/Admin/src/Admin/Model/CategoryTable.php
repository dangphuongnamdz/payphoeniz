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

 class CategoryTable
 {
     protected $tableGateway;

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
         $resultSet = $this->tableGateway->select();
         return $resultSet;
     }

     public function getCategory($id)
     {
         $id  = (int) $id;
         $rowset = $this->tableGateway->select(array('id' => $id));
         $row = $rowset->current();
         if (!$row) {
             throw new \Exception("Could not find row $id");
         }
         return $row;
     }

     public function saveCategory(Category $category)
     {
         $id = (int) $category->id;
         if ($id == 0) {
            $data = array(
                'tendanhmuc' => $category->tendanhmuc,
                'id_parent' => $category->id_parent,
                'status' => $category->status,
                'alias'  => $this->stripUnicode($category->tendanhmuc),
                'created_at' => new \Zend\Db\Sql\Expression("NOW()"),
                'updated_at' => new \Zend\Db\Sql\Expression("NOW()"),
            );
             $this->tableGateway->insert($data);
         } else {
            $dataupdated = array(
                'tendanhmuc' => $category->tendanhmuc,
                'id_parent' => $category->id_parent,
                'alias'  => $category->alias,
                'status' => $category->status,                
                'updated_at' => new \Zend\Db\Sql\Expression("NOW()"),
            );
             if ($this->getCategory($id)) {
                 $this->tableGateway->update($dataupdated, array('id' => $id));
             } else {
                 throw new \Exception('category id does not exist');
             }
         }
     }

     public function deleteCategory($id)
     {
         $this->tableGateway->delete(array('id' => (int) $id));
     }
 }