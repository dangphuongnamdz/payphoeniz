<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Form\LogCards;

 use Zend\Form\Form;
 use Zend\Db\Adapter\AdapterInterface;
 use Zend\Db\Adapter\Adapter;
 use Zend\Session\Container;

 class IAPHistoryForm extends Form
 {
    protected $adapter;
    protected $role_agent;
    protected $level; 

    public function __construct(AdapterInterface $dbAdapter, $agent = null)
    {
         $this->adapter =$dbAdapter;
         $session = new Container('User');
         $this->role_agent= $session->offsetGet('role_agent');
         $this->level= $session->offsetGet('level');
         parent::__construct('iaphistory');
         
         $this->add(array(
            'name' => 'in_time',
            'type' => 'Text',
            'attributes' => array(
               'class' => 'form-control col-sm-2',
               'id'    =>  'config-demo'
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
            'name' => 'in_uid',
            'type' => 'Text',
            'attributes' => array(
               'class' => 'form-control col-sm-2',
               'id'    =>  'config-form'
            ),
            'options'=>array(
               'label'=>'Username:',
               'label_attributes'=>array(
                   'for' => 'in_uid',
                   'class'=>'controll-label col-sm-2'
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
                'value_options' => $this->getOptionsForSelectServer($agent),
            ),
        ));
        $this->add(array(
            'type'=>'select',
            'name'=>'in_product_id',
            'attributes'=>array(
                'class' => 'form-control in_product_id',
                'id'    =>  'config-form',
                'value' => $agent
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
            'name' => 'in_amount_start',
            'type' => 'Text',
            'attributes' => array(
               'class' => 'form-control col-sm-2',
               'id'    =>  'config-form'
            ),
            'options'=>array(
               'label'=>'Mệnh giá từ:',
               'label_attributes'=>array(
                   'for' => 'in_amount_start',
                   'class'=>'controll-label col-sm-2'
               ),
           ),

        ));

        $this->add(array(
            'name' => 'in_amount_end',
            'type' => 'Text',
            'attributes' => array(
               'class' => 'form-control col-sm-2',
               'id'    =>  'config-form'
            ),
            'options'=>array(
               'label'=>'Mệnh giá đến:',
               'label_attributes'=>array(
                   'for' => 'in_amount_end',
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
                    '-1' => 'Fail'
                ),
                'label_attributes' => array(
                   'for' => 'in_status',
                   'class' => 'col-sm-2 controll-label',
                ),
            ),
        ));


        $this->add(array(
            'name' => 'in_os',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
               'class' => 'form-control',
               'id'    =>  'config-form',
               'value' => ''
            ),
            'options' => array(
                'label' => 'Hệ điều hành',
                'value_options' => array(
                    '' => 'All',
                    'android' => 'Android',
                    'ios' => 'IOS'
                ),
                'label_attributes' => array(
                   'for' => 'in_os',
                   'class' => 'col-sm-2 controll-label',
                ),
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
     public function getOptionsForSelectProduct()
     {
         $dbAdapter = $this->adapter;
         if($this->level != 1){
            $stringArrRoleAgent = str_replace("]", ")", str_replace("[", "(", $this->role_agent));
            $sql       = 'SELECT agent, name  FROM product WHERE agent IN '.$stringArrRoleAgent;
         }else{
            $sql       = 'SELECT agent, name  FROM product';        
         }
         $statement = $dbAdapter->query($sql);
         $result    = $statement->execute();
         $selectData = array();
         foreach ($result as $res) {
             $selectData[$res['agent']] = $res['name'];
         }
         return $selectData;
     }

     public function getOptionsForSelectServer($agent)
     {
         $dbAdapter = $this->adapter;
         if($agent==null){
            if($this->level != 1){
                $stringArrRoleAgent = str_replace("]", ")", str_replace("[", "(", $this->role_agent));
                $sql       = 'SELECT server_name, server_id  FROM servers WHERE agent IN '.$stringArrRoleAgent;
             }else{
                $sql       = 'SELECT server_name, server_id  FROM servers'; 
             }
         }else{
            $sql = "SELECT servers.server_name, servers.server_id FROM servers LEFT JOIN product ON servers.product_id=product.id WHERE product.agent like '".$agent."'";
         }
         $statement = $dbAdapter->query($sql);
         $result    = $statement->execute();
         $selectData = array();
         foreach ($result as $res) {
             $selectData[$res['server_id']] = $res['server_name'];
         }
         return $selectData;
     }
 }