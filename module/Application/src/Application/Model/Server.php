<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

 namespace Application\Model;
 use Zend\InputFilter\InputFilter;
 use Zend\InputFilter\InputFilterAwareInterface;
 use Zend\InputFilter\InputFilterInterface;
 class Server
 {
     public $id;
     public $server_id;
     public $server_name;
    //  public $server_group;
     public $product_id;
     public $server_status;
     public $server_slug;
     public $pay_status;
     public $agent;
     public $key_web_charge;
     public $key_iap_charge;
    //  public $server_order;
     public $created_at;
     public $updated_at;
     protected $inputFilter;

     public function exchangeArray($data)
     {
         $this->id     = (!empty($data['id'])) ? $data['id'] : null;
         $this->server_id = (!empty($data['server_id'])) ? $data['server_id'] : null;
         $this->server_name = (!empty($data['server_name'])) ? $data['server_name'] : null;
         //$this->server_group = (!empty($data['server_group'])) ? $data['server_group'] : null;
         $this->product_id = (!empty($data['product_id'])) ? $data['product_id'] : null;
         $this->server_status = (!empty($data['server_status'])) ? $data['server_status'] : null;
         $this->server_slug = (!empty($data['server_slug'])) ? $data['server_slug'] : null;
        //  $this->server_order = (!empty($data['server_order'])) ? $data['server_order'] : null;
         $this->pay_status = (!empty($data['pay_status'])) ? $data['pay_status'] : null;
         $this->agent = (!empty($data['agent'])) ? $data['agent'] : null;
         $this->key_web_charge = (!empty($data['key_web_charge'])) ? $data['key_web_charge'] : null;
         $this->key_iap_charge = (!empty($data['key_iap_charge'])) ? $data['key_iap_charge'] : null;
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
                'name'     => 'server_name',
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