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

 class GoldForm extends Form
 {
    protected $adapter;
    public function __construct(AdapterInterface $dbAdapter, $name = null)
    {
        $this->adapter =$dbAdapter;
         parent::__construct('gold');
         $this->setAttribute('enctype','multipart/form-data');
         
         $this->add(array(
             'name' => 'id',
             'type' => 'Hidden',
         ));
         $this->add(array(
             'name' => 'amount',
             'type' => 'Text',
             'attributes' => array(
                'class' => 'form-control', 
             ),
             'options' => array(
                 'label' => 'Amount',
                 'label_attributes' => array(
                    'for' => '',
                    'class' => 'col-sm-9 controll-label',
                 ),
             ),
         ));
         $this->add(array(
            'name' => 'gold',
            'type' => 'Text',
            'attributes' => array(
               'class' => 'form-control', 
            ),
            'options' => array(
                'label' => 'Gold',
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
                'label' => 'Image',
                'label_attributes' => array(
                    'for' => '',
                    'class' => 'col-sm-9 controll-label',
                 ),
            ),
        ));

        $this->add(array(
            'type'=>'select',
            'name'=>'product_id',
            'attributes'=>array(
                'class'=>'form-control',
            ),
            'options'=>array(
                'label'=>'Product:',
                'label_attributes'=>array(
                    'for' => 'product_id',
                    'class'=>'col-sm-9 controll-label'
            ),
                'value_options' => $this->getOptionsForSelect(),
            ),
        ));

        $this->add(array(
            'name' => 'product_gold_id',
            'type' => 'Text',
            'attributes' => array(
               'class' => 'form-control', 
            ),
            'options' => array(
                'label' => 'Product id gold',
                'label_attributes' => array(
                   'for' => '',
                   'class' => 'col-sm-9 controll-label',
                ),
            ),
        ));

        $this->add(array(
            'name' => 'card_month',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
               'class' => 'form-control',
               'value' => '2'
            ),
            'options' => array(
                'label' => 'Thẻ thường/thẻ tháng',
                'value_options' => array(
                    '2' => 'Thẻ thường',
                    '1' => 'Thẻ tháng',
					'3'=>'Quà'
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
                 'value' => 'Đăng ký',
                 'id' => 'submitbutton',
                 'class' => 'col-sm-3 btn btn-basic',
             ),
             
         ));
     }

     public function getOptionsForSelect()
     {
         $dbAdapter = $this->adapter;
         $sql       = 'SELECT agent, name  FROM product where payment_type != 1';
         $statement = $dbAdapter->query($sql);
         $result    = $statement->execute();
         $selectData = array();
         foreach ($result as $res) {
             $selectData[$res['agent']] = $res['name'];
         }
         return $selectData;
     }
 }