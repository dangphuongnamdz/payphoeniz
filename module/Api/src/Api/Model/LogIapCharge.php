<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

 namespace Api\Model;
 use Zend\InputFilter\InputFilter;
 use Zend\InputFilter\InputFilterAwareInterface;
 use Zend\InputFilter\InputFilterInterface;
 class LogIapCharge
 {
     public $id;
     public $uid;
     public $server_id;
     public $product_id;
     public $order_vnd;
     public $order_amount;
     public $pay_status;
     public $order_id;
     public $time;
     public $payload;
     public $agent;
     public $os;
     public $username;
     public $created_at;
     public $updated_at;
     protected $inputFilter;

     public function exchangeArray($data)
     {
         $this->id     = (!empty($data['id'])) ? $data['id'] : null;
         $this->transaction_id =  (!empty($data['transaction_id'])) ? $data['transaction_id'] : null;
         $this->uid = (!empty($data['uid'])) ? $data['uid'] : null;
         $this->server_id = (!empty($data['server_id'])) ? $data['server_id'] : null;
         $this->product_id = (!empty($data['product_id'])) ? $data['product_id'] : null;
         $this->order_vnd = (!empty($data['order_vnd'])) ? $data['order_vnd'] : null;
         $this->order_amount = (!empty($data['order_amount'])) ? $data['order_amount'] : null;         
         $this->time = (!empty($data['time'])) ? $data['time'] : null;
         $this->payload = (!empty($data['payload'])) ? $data['payload'] : null;
         $this->agent = (!empty($data['agent'])) ? $data['agent'] : null;
         $this->os = (!empty($data['os'])) ? $data['os'] : null;
         $this->username = (!empty($data['username'])) ? $data['username'] : null;
         $this->status = (!empty($data['status'])) ? $data['status'] : null;
         $this->created_at = (!empty($data['created_at'])) ? $data['created_at'] : null;
         $this->updated_at = (!empty($data['updated_at'])) ? $data['updated_at'] : null;
     }

      public function setInputFilter(InputFilterInterface $inputFilter)
      {
          throw new \Exception("Not used");
      }
 
      public function getInputFilter()
      {
          if (!$this->inputFilter) {
              $inputFilter = new InputFilter();
 
              $inputFilter->add(array(
                  'name'     => 'id',
                  'required' => true,
                  'filters'  => array(
                      array('name' => 'Int'),
                  ),
              ));
              $inputFilter->add(array(
                'name'     => 'server_id',
                'required' => true
              ));
              $inputFilter->add(array(
                'name'     => 'product_id',
                'required' => true
              ));
              $inputFilter->add(array(
                'name'     => 'transaction_id',
                'required' => true
              ));
              $inputFilter->add(array(
                'name'     => 'payload',
                'required' => true
              ));

            $this->inputFilter = $inputFilter;
          }
 
          return $this->inputFilter;
      }
      public function getArrayCopy()
      {
          return get_object_vars($this);
      }
 }