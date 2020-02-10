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

 class ServerForm extends Form
 {
    protected $adapter;
    public function __construct(AdapterInterface $dbAdapter, $name = null)
     {
         $this->adapter =$dbAdapter;
         parent::__construct('server');
         $this->add(array(
             'name' => 'id',
             'type' => 'Hidden',
         ));
         $this->add(array(
             'name' => 'server_id',
             'type' => 'Text',
             'attributes' => array(
                'class' => 'form-control',
             ),
             'options' => array(
                 'label' => 'Server ID(unique)',
                 'label_attributes' => array(
                    'for' => '',
                    'class' => 'col-sm-1 controll-label',
                 ),
             ),
         ));

         $this->add(array(
            'name' => 'server_name',
            'type' => 'Text',
            'attributes' => array(
               'class' => 'form-control',
            ),
            'options' => array(
                'label' => 'Server Name',
                'label_attributes' => array(
                   'for' => '',
                   'class' => 'col-sm-1 controll-label',
                ),
            ),
        ));
        
        $this->add(array(
            'name' => 'server_status',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
               'class' => 'form-control',
               'value' => '3'
            ),
            'options' => array(
                'label' => 'Server Status',
                'value_options' => array(
                    '1' => 'Bảo trì',
                    '2' => 'Bình thường',
                    '3' => 'New',
                    '4' => 'Hot',
                ),
                'label_attributes' => array(
                   'for' => '',
                   'class' => 'col-sm-1 controll-label',
                ),
            ),
        ));

        $this->add(array(
            'name' => 'server_slug',
            'type' => 'Text',
            'attributes' => array(
               'class' => 'form-control',
            ),
            'options' => array(
                'label' => 'Server slug(unique)',
                'label_attributes' => array(
                   'for' => '',
                   'class' => 'col-sm-1 controll-label',
                ),
            ),
        ));

        $this->add(array(
            'name' => 'key_web_charge',
            'type' => 'Text',
            'attributes' => array(
               'class' => 'form-control',
            ),
            'options' => array(
                'label' => 'Key web charge',
                'label_attributes' => array(
                   'for' => '',
                   'class' => 'col-sm-1 controll-label',
                ),
            ),
        ));

        $this->add(array(
            'name' => 'key_iap_charge',
            'type' => 'Text',
            'attributes' => array(
               'class' => 'form-control',
            ),
            'options' => array(
                'label' => 'Key iap charge',
                'label_attributes' => array(
                   'for' => '',
                   'class' => 'col-sm-1 controll-label',
                ),
            ),
        ));

        $this->add(array(
            'name' => 'pay_status',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
               'class' => 'form-control',
               'value' => '1'
            ),
            'options' => array(
                'label' => 'Pay status',
                'value_options' => array(
                    '1' => 'Yes',
                    '2' => 'No'
                ),
                'label_attributes' => array(
                   'for' => '',
                   'class' => 'col-sm-1 controll-label',
                ),
            ),
        ));

        $this->add(array(
            'name' => 'server_order',
            'type' => 'Text',
            'attributes' => array(
               'class' => 'form-control',
            ),
            'options' => array(
                'label' => "Server order",
                'label_attributes' => array(
                   'for' => '',
                   'class' => 'col-sm-1 controll-label',
                ),
            ),
        ));

        $this->add(array(
            'name' => 'agent',
            'type' => 'Text',
            'attributes' => array(
               'class' => 'form-control',
               'disabled' => 'disabled',
            ),
            'options' => array(
                'label' => "Agent",
                'label_attributes' => array(
                   'for' => '',
                   'class' => 'col-sm-1 controll-label',
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
                    'class'=>'col-sm-1 controll-label'
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
                'class' => 'btn btn-basic',
            ),
            
        ));
     }
     public function getOptionsForSelect()
     {
         $dbAdapter = $this->adapter;
         $sql       = 'SELECT id, name  FROM product';
         $statement = $dbAdapter->query($sql);
         $result    = $statement->execute();
         $selectData = array();
         foreach ($result as $res) {
             $selectData[$res['id']] = $res['name'];
         }
         return $selectData;
     }
 }