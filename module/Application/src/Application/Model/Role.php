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
 class Role
 {
     public $id;
     public $agent;
     public $username;
     public $userid;
     public $role_id;
     public $role_name;
     public $server_id;
     public $created_at;
     public $updated_at;
     protected $inputFilter;

     public function exchangeArray($data)
     {
         $this->id     = (!empty($data['id'])) ? $data['id'] : null;
         $this->agent = (!empty($data['agent'])) ? $data['agent'] : null;
         $this->username = (!empty($data['username'])) ? $data['username'] : null;
         $this->userid = (!empty($data['userid'])) ? $data['userid'] : null;
         $this->role_id = (!empty($data['role_id'])) ? $data['role_id'] : null;
         $this->role_name = (!empty($data['role_name'])) ? $data['role_name'] : null;
         $this->server_id = (!empty($data['server_id'])) ? $data['server_id'] : null;
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
                  'name'     => 'tendanhmuc',
                  'required' => true
              ));
              $inputFilter->add(array(
                'name'     => 'id_parent',
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