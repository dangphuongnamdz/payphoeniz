<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Form;

 use Zend\Form\Form;
 use Zend\Db\Adapter\AdapterInterface;
 use Zend\Db\Adapter\Adapter;

 class CategoryForm extends Form
 {
    protected $adapter;
     public function __construct(AdapterInterface $dbAdapter, $name = null)
     {
         $this->adapter =$dbAdapter;
         parent::__construct('categorys');
         $this->add(array(
             'name' => 'id',
             'type' => 'Hidden',
         ));
         $this->add(array(
             'name' => 'tendanhmuc',
             'type' => 'Text',
             'attributes' => array(
                'class' => 'form-control',
             ),
             'options' => array(
                 'label' => 'Tên danh mục',
                 'label_attributes' => array(
                    'for' => '',
                    'class' => 'col-sm-12 controll-label',
                 ),
             ),
         ));
         
         $this->add(array(
            'name' => 'alias',
            'type' => 'Text',
            'attributes' => array(
               'class' => 'form-control',
            ),
            'options' => array(
                'label' => 'Alias',
                'label_attributes' => array(
                   'for' => '',
                   'class' => 'col-sm-12 controll-label',
                ),
            ),
        ));

         $this->add(array(
            'name' => 'avatar',
            'type' => 'Text',
            'attributes' => array(
               'class' => 'form-control',
            ),
            'options' => array(
                'label' => 'URL ảnh đại diện(nếu có)',
                'label_attributes' => array(
                   'for' => '',
                   'class' => 'col-sm-12 controll-label',
                ),
            ),
        ));
        
        $this->add(array(
            'name' => 'status',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
               'class' => 'form-control',
               'value' => '1'
            ),
            'options' => array(
                'label' => 'Trạng thái',
                'value_options' => array(
                    '1' => 'Hiển thị',
                    '2' => 'Ẩn'
                ),
                'label_attributes' => array(
                   'for' => '',
                   'class' => 'col-sm-12 controll-label',
                ),
            ),
        ));

        $this->add(array(
            'type'=>'select',
            'name'=>'id_parent',
            'attributes'=>array(
                'class'=>'form-control',
                'value'=>'0'
            ),
            'options'=>array(
                'label'=>'Danh mục cha:',
                'label_attributes'=>array(
                    'for' => 'id_parent',
                    'class'=>'col-sm-12 controll-label'
            ),
                'value_options' => $this->getOptionsForSelect(),
            ),
        ));

         $this->add(array(
             'name' => 'submit',
             'type' => 'Submit',
             'attributes' => array(
                 'value' => 'Go',
                 'id' => 'submitbutton',
                 'class' => 'col-sm-2 btn btn-basic',
             ),
             
         ));
     }
     
     public function getOptionsForSelect()
     {
         $dbAdapter = $this->adapter;
         $sql       = 'SELECT id, tendanhmuc, id_parent  FROM category';
         $statement = $dbAdapter->query($sql);
         $result    = $statement->execute();
         
         $arr_tree = array();
         $arr_tmp = array();
         foreach ($result as $item) {
             $parentid = $item['id_parent'];
             $id = $item['id'];
         
             if ($parentid  == 0)
             {
                 $arr_tree[$id] = $item;
                 $arr_tmp[$id] = &$arr_tree[$id];
             }
             else 
             {
                 if (!empty($arr_tmp[$parentid])) 
                 {
                     $arr_tmp[$parentid]['children'][$id] = $item;
                     $arr_tmp[$id] = &$arr_tmp[$parentid]['children'][$id];
                 }
             }
         }
         unset($arr_tmp);
         $selectData = array();
         $selectData[0] = 'Root';
         foreach ($arr_tree as $res) {
             $selectData[$res['id']] = $res['tendanhmuc'];
         }
         return $selectData;
     }
 }