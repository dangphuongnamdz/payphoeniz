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
 class LogAgent
 {
     public $id;
     public $transaction_id;
     public $account;
     public $function;
     public $agent;
     public $data_input;
     public $data_output;
     public $status;
     public $created_at;
     public $updated_at;
     protected $inputFilter;

     public function exchangeArray($data)
     {
         $this->id     = (!empty($data['id'])) ? $data['id'] : null;
         $this->transaction_id =  (!empty($data['transaction_id'])) ? $data['transaction_id'] : null;
         $this->account = (!empty($data['account'])) ? $data['account'] : null;
         $this->function = (!empty($data['function'])) ? $data['function'] : null;
         $this->agent = (!empty($data['agent'])) ? $data['agent'] : null;
         $this->data_input = (!empty($data['data_input'])) ? $data['data_input'] : null;
         $this->data_output = (!empty($data['data_output'])) ? $data['data_output'] : null;
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
                'name'     => 'account',
                'required' => true
              ));
              $inputFilter->add(array(
                'name'     => 'transaction_id',
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