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

 class CompensationForm extends Form
 {
    protected $adapter;
    protected $role_agent;
    protected $level; 
    protected $config;
    public function __construct(AdapterInterface $dbAdapter, $agent, $config)
    {
        $this->adapter =$dbAdapter;
        $this->config =$config;
        $session = new Container('User');
        $this->role_agent= $session->offsetGet('role_agent');
        $this->level= $session->offsetGet('level');
        parent::__construct('payhistory');
         
        $this->add(array(
            'type'=>'select',
            'name'=>'in_amount',
            'attributes'=>array(
                'class' => 'form-control',
                'id'    =>  'config-form',
            ),
            'options'=>array(
                'label'=>'Mệnh giá:',
                'label_attributes'=>array(
                    'for' => 'config-demo',
                    'class'=>'col-sm-9 controll-label'
            ),
                'empty_option' => 'Chọn mệnh giá',
                'value_options' => $this->getOptionsForSelectGold($agent),
            ),
        ));

        $this->add(array(
            'type'=>'select',
            'name'=>'in_server',
            'attributes'=>array(
                'class' => 'form-control in_server',
                'id'    =>  'config-form',
            ),
            'options'=>array(
                'label'=>'Server:',
                'label_attributes'=>array(
                    'for' => 'config-demo',
                    'class'=>'col-sm-9 controll-label'
            ),
                'empty_option' => 'Chọn server',
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
             'name' => 'submit',
             'type' => 'Submit',
             'attributes' => array(
                 'value' => 'Go',
                 'id' => 'submitbutton',
                 'class' => 'col-sm-2 btn btn-basic',
             ),
             
         ));
        $this->add(array(
            'name' => 'custom_amount',
            'type' => 'Text',
            'attributes' => array(
               'class' => 'form-control col-sm-2 custom_amount',
               'id'    =>  'config-form'
            ),
            'options'=>array(
               'label'=>'Tùy chọn mệnh giá thẻ:',
               'label_attributes'=>array(
                   'for' => 'in_transaction',
                   'class'=>'controll-label col-sm-2 custom_amount'
               ),
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
            /*if($this->level != 1){
                $stringArrRoleAgent = str_replace("]", ")", str_replace("[", "(", $this->role_agent));
                $sql = "SELECT servers.server_name, servers.server_id, product.agent FROM servers LEFT JOIN product ON servers.product_id=product.id WHERE product.agent IN ".$stringArrRoleAgent;
             }else{*/
                $sql = "SELECT servers.server_name, servers.server_id, product.agent FROM servers LEFT JOIN product ON servers.product_id=product.id";
             //}
         }else{
            $sql = "SELECT servers.server_name, servers.server_id, product.agent FROM servers LEFT JOIN product ON servers.product_id=product.id WHERE product.agent like '".$agent."'";
         }
         $statement = $dbAdapter->query($sql);
         $result    = $statement->execute();
         $selectData = array();
         foreach ($result as $res) {
             $selectData[$res['server_id']] = "Product: ".$res['agent']." => ".$res['server_name'];
         }
         return $selectData;
     }

    public function getOptionsForSelectGold($agent)
    {
        $config = $this->config;
       
        $dbAdapter = $this->adapter;
        if($agent==null){
            if($this->level != 1){
                $stringArrRoleAgent = str_replace("]", ")", str_replace("[", "(", $this->role_agent));
                $sql = 'SELECT gold, amount FROM gold WHERE product_id IN '.$stringArrRoleAgent;
            }else{
                $sql = 'SELECT gold, amount FROM gold';
            }
        }else{
            $sql = "SELECT gold, amount,product_gold_id FROM gold WHERE product_id like '".$agent."'";
        }
        $statement = $dbAdapter->query($sql);
        $result    = $statement->execute();
        $selectData = array();
        if($agent=='m002' || $agent=='m003'){
            foreach ($result as $res) {
                $gold = $res['gold'];
                if($gold==0||$gold==''){
                    $gold = 'Quà';
                }
                $selectData[$res['product_gold_id']] = number_format($res['amount'])." VND => ".$gold;
            }
        } elseif($agent=='m005'){
            $rate = $config['payment']['m005']['rate_refund'];
            $prices = array(
                '10000' => '10000',
                '20000' => '20000',
                '50000' => '50000',
                '100000' => '100000',
                '200000' => '200000',
                '300000' => '300000',
                '500000' => '500000',
                '1000000' => '1000000',
            );  
            foreach($prices as $key=>$value){
                $amount = $key * $rate;
                $selectData[$amount] = number_format($value)." VND => ".$value * $rate . " gold";
            }
        }else{
            foreach ($result as $res) {
                $selectData[$res['gold']] = number_format($res['amount'])." VND => ".$res['gold'];
            }
        }
        return $selectData;
    }
 }