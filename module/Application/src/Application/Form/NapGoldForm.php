<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Form;

 use Zend\Form\Form;
 use Zend\Db\Adapter\AdapterInterface;
 use Zend\Db\Adapter\Adapter;

 class NapGoldForm extends Form
 {
     protected $adapter;
     protected $product_id;
     public function __construct(AdapterInterface $dbAdapter, $product_id = null)
     {
         $this->adapter =$dbAdapter;
         $this->product_id = $product_id;
         parent::__construct('napgold');
         $this->add(array(
            'name' => 'agent',
            'type' => 'Hidden',
            'attributes' => array(
                'id' => 'agent',
             ),
        ));
         $this->add(array(
             'name' => 'in_serie',
             'type' => 'Text',
             'attributes' => array(
                'class' => 'form-control',
             ),
             'options' => array(
                 'label' => 'Số serie',
                 'label_attributes' => array(
                    'for' => 'in_serie',
                    'class' => 'col-sm-12 controll-label',
                 ),
             ),
         ));

         $this->add(array(
            'name' => 'in_pin',
            'type' => 'Text',
            'attributes' => array(
               'class' => 'form-control',
            ),
            'options' => array(
                'label' => 'Mã thẻ',
                'label_attributes' => array(
                   'for' => 'in_pin',
                   'class' => 'col-sm-12 controll-label',
                ),
            ),
        ));
        
        $this->add(array(
            'name' => 'in_type',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
               'class' => 'form-control',
            ),
            'options' => array(
                'label' => 'Loại thẻ',
                'value_options' => $this->getListType(),
                'label_attributes' => array(
                   'for' => 'in_type',
                   'class' => 'col-sm-12 controll-label',
                ),
            ),
        ));

        $this->add(array(
            'type'=>'select',
            'name'=>'server_list',
            'attributes'=>array(
                'class'=>'form-control ',
                'id'   =>'server_list'
            ),
            'options'=>array(
                'label'=>'  Chọn server:',
                'label_attributes'=>array(
                    'for' => 'server_list',
                    'class'=>'col-sm-12 controll-label',
                    'id'   =>'list_server'
            ),
                'empty_option' => 'Chọn server',
                'value_options' => $this->getOptionsForSelect($this->product_id),
            ),
        ));

        $this->add(array(
            'type'=>'select',
            'name'=>'role_id',
            'attributes'=>array(
                'class'=>'form-control ',
            ),
            'options'=>array(
                'label'=>'  Chọn nhân vật:',
                'label_attributes'=>array(
                    'for' => 'role_list',
                    'class'=>'col-sm-12 controll-label'
            ),
                'empty_option' => 'Chọn nhân vật',
            ),
        ));
        
        $this->add(array(
            'name' => 'amount_pay',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
               'class' => 'form-control',
               'value' => ''
            ),
            'options' => array(
                'label' => 'Số tiền thanh toán (VNĐ)',
                'value_options' => array(
                    ''  => 'Chọn số tiền',
                    '10000' => '10,000',
                    '20000' => '20,000',
                    '50000' => '50,000',
                    '100000' => '100,000',
                    '200000' => '200,000',
                    '300000' => '300,000',
                    '500000' => '500,000',
                    '1000000' => '1,000,000',
                    '2000000' => '2,000,000'
                ),
                'label_attributes' => array(
                   'for' => 'amount_pay',
                   'class' => 'col-sm-12 controll-label',
                ),
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => 'Thanh toán ',
                'id' => 'submitbutton',
                'class' => 'col-sm-3 btn btn-primary',
            ),
            
        ));
     }
     
     public function getOptionsForSelect($product_id = null)
     {

         $dbAdapter = $this->adapter;
         $sql       = 'SELECT id, server_name, server_id  FROM servers where pay_status = 1 and product_id = '.$product_id.' order by server_id';
         $statement = $dbAdapter->query($sql);
         $result    = $statement->execute();
         $selectData = array();
         foreach ($result as $res) {
             $selectData[$res['server_id']] = $res['server_name'];
         }
         return $selectData;
     }

     public function getListType()
     {
        $dbAdapter = $this->adapter;
        $sql       = "SELECT type, name  FROM charge_type where status = 1 and type <> 'atm'";
        $statement = $dbAdapter->query($sql);
        $result    = $statement->execute();
        $selectData = array();
        foreach ($result as $res) {
            $selectData[$res['type']] = $res['name'];
        }
        return $selectData;
     }
 }