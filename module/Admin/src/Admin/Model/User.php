<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

 namespace Admin\Model;
 use Zend\InputFilter\InputFilter;
 use Zend\InputFilter\InputFilterAwareInterface;
 use Zend\InputFilter\InputFilterInterface;
 class User
 {
     public $id;
     public $username;
     public $password;
     public $fullname;
     public $level;
     public $status;
     public $email;
     public $role_agent;
     public $passwordtoken;
     public $dateresetpasswordtoken;
     public $created_at;
     public $updated_at;
     protected $inputFilter;

     public function exchangeArray($data)
     {
         $this->id     = (!empty($data['id'])) ? $data['id'] : null;
         $this->username = (!empty($data['username'])) ? $data['username'] : null;
         $this->password  = (!empty($data['password'])) ? $data['password'] : null;
         $this->fullname  = (!empty($data['fullname'])) ? $data['fullname'] : null;
         $this->level  = (!empty($data['level'])) ? $data['level'] : null;
         $this->status  = (!empty($data['status'])) ? $data['status'] : null;
         $this->email  = (!empty($data['email'])) ? $data['email'] : null;
         $this->role_agent  = (!empty($data['role_agent'])) ? $data['role_agent'] : null;
         $this->passwordtoken  = (!empty($data['passwordtoken'])) ? $data['passwordtoken'] : null;
         $this->dateresetpasswordtoken  = (!empty($data['dateresetpasswordtoken'])) ? $data['dateresetpasswordtoken'] : null;
         $this->created_at  = (!empty($data['created_at'])) ? $data['created_at'] : null;
         $this->updated_at  = (!empty($data['updated_at'])) ? $data['updated_at'] : null;
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
                'name'     => 'email',
                'required' => false,
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