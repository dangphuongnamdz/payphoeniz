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

 class ChargeTypeForm extends Form
 {
     public function __construct($name = null)
     {
         parent::__construct('chargeType');
         
         $this->add(array(
             'name' => 'id',
             'type' => 'Hidden',
         ));
         $this->add(array(
             'name' => 'type',
             'type' => 'Text',
             'attributes' => array(
                'class' => 'form-control',
             ),
             'options' => array(
                 'label' => 'Loại thanh toán(VTT, ATM, ...) *',
                 'label_attributes' => array(
                    'for' => '',
                    'class' => 'col-sm-9 controll-label',
                 ),
             ),
         ));
         $this->add(array(
            'name' => 'name',
            'type' => 'text',
            'attributes' => array(
               'class' => 'form-control',
            ),
            'options' => array(
                'label' => 'Tên loại thanh toán(Viettel, Mobiphone, Vinaphone,...) *',
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
                'label' => 'Trạng thái *',
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
             'name' => 'submit',
             'type' => 'Submit',
             'attributes' => array(
                 'value' => 'Go',
                 'id' => 'submitbutton',
                 'class' => 'col-sm-3 btn btn-basic',
             ),
             
         ));
     }
 }