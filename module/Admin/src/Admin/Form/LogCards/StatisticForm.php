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

 class StatisticForm extends Form
 {
    protected $adapter;
    protected $role_agent;
    protected $level; 
    public function __construct(AdapterInterface $dbAdapter, $name = null)
    {
         $this->adapter =$dbAdapter;
         $session = new Container('User');
         $this->role_agent= $session->offsetGet('role_agent');
         $this->level= $session->offsetGet('level');
         parent::__construct('statistic');
         
         $this->add(array(
             'name' => 'in_time',
             'type' => 'Text',
             'attributes' => array(
                'class' => 'form-control col-sm-3',
                'id'    =>  'config-demo'
             ),
             'options'=>array(
                'label'=>'Time:',
                'label_attributes'=>array(
                    'for' => 'in_time',
                    'class'=>'controll-label'
                ),
            ),

         ));
         $this->add(array(
            'type'=>'select',
            'name'=>'in_product_id',
            'attributes'=>array(
                'class' => 'form-control col-sm-3',
                'id'    =>  'config-demo',
            ),
            'options'=>array(
                'label'=>'Product:',
                'label_attributes'=>array(
                    'for' => 'in_product_id',
                    'class'=>'controll-label'
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
 }