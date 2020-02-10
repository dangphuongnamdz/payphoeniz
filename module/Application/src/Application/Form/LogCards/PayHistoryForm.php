<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Form\LogCards;

 use Zend\Form\Form;
 use Zend\Db\Adapter\AdapterInterface;
 use Zend\Db\Adapter\Adapter;

 class PayHistoryForm extends Form
 {
    protected $adapter;
    public function __construct(AdapterInterface $dbAdapter, $name = null)
    {
         $this->adapter =$dbAdapter;
         parent::__construct('payhistory');
         
         $this->add(array(
            'name' => 'in_time',
            'type' => 'Text',
            'attributes' => array(
               'class' => 'form-control col-sm-2',
               'id'    =>  'config-demo',
               'autocomplete' => 'off'
            ),
            'options'=>array(
                'label'=>'Time:',
               'label_attributes'=>array(
                   'for' => 'in_time',
                   'class'=>'controll-label col-sm-2'
               ),
           ),
        ));


         $this->add(array(
            'name' => 'in_transaction',
            'type' => 'Text',
            'attributes' => array(
               'class' => 'form-control col-sm-2',
               'id'    =>  'config-form'
            ),
            'options'=>array(
               'label'=>'Mã giao dịch:',
               'label_attributes'=>array(
                   'for' => 'in_transaction',
                   'class'=>'controll-label col-sm-2'
               ),
           ),
        ));
        $this->add(array(
            'name' => 'in_username',
            'type' => 'Text',
            'attributes' => array(
               'class' => 'form-control col-sm-2',
               'id'    =>  'config-form'
            ),
            'options'=>array(
               'label'=>'Username:',
               'label_attributes'=>array(
                   'for' => 'in_username',
                   'class'=>'controll-label col-sm-2'
               ),
           ),

        ));

        $this->add(array(
            'name' => 'in_serial',
            'type' => 'Text',
            'attributes' => array(
               'class' => 'form-control col-sm-2',
               'id'    =>  'config-form'
            ),
            'options'=>array(
               'label'=>'Serial:',
               'label_attributes'=>array(
                   'for' => 'in_serial',
                   'class'=>'controll-label col-sm-2'
               ),
           ),

        ));


        $this->add(array(
            'name' => 'in_status',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
               'class' => 'form-control',
               'id'    =>  'config-form',
               'value' => ''
            ),
            'options' => array(
                'label' => 'Status',
                'value_options' => array(
                    '' => 'All',
                    '1' => 'Success',
                    '0' => 'Fail'
                ),
                'label_attributes' => array(
                   'for' => 'in_status',
                   'class' => 'col-sm-2 controll-label',
                ),
            ),
        ));


        $this->add(array(
            'name' => 'in_type',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
               'class' => 'form-control',
               'id'    =>  'config-form',
               'value' => ''
            ),
            'options' => array(
                'label' => 'Loại thẻ',
                'value_options' => array(
                    '' => 'All',
                    'VTT' => 'Viettel',
                    'VNP' => 'Vinaphone',
                    'VMS' => 'Mobiphone',
                    'REF' => 'Đền bù'
                ),
                'label_attributes' => array(
                   'for' => 'in_type',
                   'class' => 'col-sm-2 controll-label',
                ),
            ),
        ));
        $this->add(array(
            'type'=>'select',
            'name'=>'in_server',
            'attributes'=>array(
                'class' => 'form-control',
                'id'    =>  'config-form',
            ),
            'options'=>array(
                'label'=>'Server:',
                'label_attributes'=>array(
                    'for' => 'config-demo',
                    'class'=>'col-sm-2 controll-label'
            ),
                'empty_option' => 'All',
                'value_options' => $this->getOptionsForSelect(),
            ),
        ));
        $this->add(array(
            'type'=>'select',
            'name'=>'in_product_id',
            'attributes'=>array(
                'class' => 'form-control',
                'id'    =>  'config-form',
            ),
            'options'=>array(
                'label'=>'Product:',
                'label_attributes'=>array(
                    'for' => 'config-demo',
                    'class'=>'col-sm-2 controll-label'
            ),
                'empty_option' => 'All',
                'value_options' => $this->getOptionsForSelectProduct(),
            ),
        ));
         $this->add(array(
             'name' => 'submit',
             'type' => 'Submit',
             'attributes' => array(
                 'value' => 'Go',
                 'id' => 'submitbutton',
                 'class' => 'col-sm-2 btn btn-primary',
             ),
             
         ));
     }
     public function getOptionsForSelectProduct()
     {
         $dbAdapter = $this->adapter;
         $sql       = 'SELECT agent, name  FROM product';
         $statement = $dbAdapter->query($sql);
         $result    = $statement->execute();
         $selectData = array();
         foreach ($result as $res) {
             $selectData[$res['agent']] = $res['name'];
         }
         return $selectData;
     }

     public function getOptionsForSelect()
     {
         $dbAdapter = $this->adapter;
         $sql       = 'SELECT server_name, server_id  FROM servers';
         $statement = $dbAdapter->query($sql);
         $result    = $statement->execute();
         $selectData = array();
         foreach ($result as $res) {
             $selectData[$res['server_id']] = $res['server_name'];
         }
         return $selectData;
     }
 }