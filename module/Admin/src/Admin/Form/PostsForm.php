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

 class PostsForm extends Form
 {
    protected $adapter;
    public function __construct(AdapterInterface $dbAdapter, $name = null)
    {
        $this->adapter =$dbAdapter;
         parent::__construct('posts');
         $this->setAttribute('enctype','multipart/form-data');
         
         $this->add(array(
             'name' => 'id',
             'type' => 'Hidden',
         ));
         $this->add(array(
             'name' => 'title',
             'type' => 'Text',
             'attributes' => array(
                'class' => 'form-control', 
             ),
             'options' => array(
                 'label' => 'Tiêu đề *',
                 'label_attributes' => array(
                    'for' => '',
                    'class' => 'col-sm-9 controll-label',
                 ),
             ),
         ));
         $this->add(array(
            'name' => 'slug',
            'type' => 'Text',
            'attributes' => array(
               'class' => 'form-control', 
            ),
            'options' => array(
                'label' => 'Slug (tự động tạo)',
                'label_attributes' => array(
                   'for' => '',
                   'class' => 'col-sm-9 controll-label',
                ),
            ),
        ));
         $this->add(array(
            'name' => 'summary',
            'type' => 'textarea',
            'attributes' => array(
               'class' => 'form-control',
               'id' => 'summary',
            ),
            'options' => array(
                'label' => 'Tóm tắt',
                'label_attributes' => array(
                   'for' => '',
                   'class' => 'col-sm-9 controll-label',
                ),
            ),
        ));

        $this->add(array(
            'name' => 'content',
            'type' => 'textarea',
            'attributes' => array(
               'class' => 'form-control content',
               'id' => 'content',
            ),
            'options' => array(
                'label' => 'Nội dung ',
                'label_attributes' => array(
                   'for' => '',
                   'class' => 'col-sm-12 controll-label',
                ),
            ),
        ));

         $this->add(array(
            'name' => 'avatar',
            'attributes' => array(
                'type'  => 'file',
            ),
            'options' => array(
                'label' => 'Avatar',
                'label_attributes' => array(
                    'for' => '',
                    'class' => 'col-sm-9 controll-label',
                 ),
            ),
        ));
        $this->add(array(
            'name' => 'avatar_mobile',
            'attributes' => array(
                'type'  => 'file',
            ),
            'options' => array(
                'label' => 'Avatar trên di dộng',
                'label_attributes' => array(
                    'for' => '',
                    'class' => 'col-sm-9 controll-label',
                 ),
            ),
        ));
        $this->add(array(
            'type'=>'select',
            'name'=>'id_category',
            'attributes'=>array(
                'class'=>'form-control id_category',
            ),
            'options'=>array(
                'label'=>'Danh mục *',
                'label_attributes'=>array(
                    'for' => 'id_category',
                    'class'=>'col-sm-9 controll-label'
            ),
                'empty_option' => 'Chọn danh mục',
                'value_options' => $this->getOptionsForSelect(),
            ),
        ));
        
         $this->add(array(
             'name' => 'submit',
             'type' => 'Submit',
             'attributes' => array(
                 'value' => 'Đăng ký',
                 'id' => 'submitbutton',
                 'class' => 'col-sm-3 btn btn-basic',
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
         foreach ($arr_tree as $res) {
             if(isset($res['children'])){
                $selectData[$res['id']] = '+ '.$res['tendanhmuc'];
                foreach ($res['children'] as $res_sub) {
                    $selectData[$res_sub['id']] = '-- '.$res_sub['tendanhmuc'];
                }
             }else{
                $selectData[$res['id']] = $res['tendanhmuc'];
             }
         }
         return $selectData;
     }
 }