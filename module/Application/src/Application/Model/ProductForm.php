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

 class ProductForm extends Form
 {
    protected $adapter;
    public function __construct(AdapterInterface $dbAdapter, $name = null)
    {
        $this->adapter =$dbAdapter;
         parent::__construct('product');
         $this->setAttribute('enctype','multipart/form-data');
         
         $this->add(array(
             'name' => 'id',
             'type' => 'Hidden',
         ));
         $this->add(array(
             'name' => 'name',
             'type' => 'Text',
             'attributes' => array(
                'class' => 'form-control', 
             ),
             'options' => array(
                 'label' => 'Tên game *',
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
                'label' => 'Slug (tự động tạo - unique)',
                'label_attributes' => array(
                   'for' => '',
                   'class' => 'col-sm-9 controll-label',
                ),
            ),
        ));
        $this->add(array(
            'name' => 'agent',
            'type' => 'Text',
            'attributes' => array(
               'class' => 'form-control', 
            ),
            'options' => array(
                'label' => 'Agent *(unique)',
                'label_attributes' => array(
                   'for' => '',
                   'class' => 'col-sm-9 controll-label',
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
                   'class' => 'col-sm-9 controll-label',
                ),
            ),
        ));
        $this->add(array(
            'name' => 'payment_type',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
               'class' => 'form-control',
               'value' => '1'
            ),
            'options' => array(
                'label' => 'Loại Payment',
                'value_options' => array(
                    '1' => 'Thanh toán không cần ví',
                    '2' => 'Thanh toán bằng ví',
                    '3' => 'Bảo trì hoặc chưa phát hành',
                ),
                'label_attributes' => array(
                   'for' => '',
                   'class' => 'col-sm-9 controll-label',
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
             'name' => 'submit',
             'type' => 'Submit',
             'attributes' => array(
                 'value' => 'Đăng ký',
                 'id' => 'submitbutton',
                 'class' => 'col-sm-3 btn btn-basic',
             ),
             
         ));
     }
 }