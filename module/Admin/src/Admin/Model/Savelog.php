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
 class Savelog
 {
     public $id;
     public $username;
     public $time;
     public $note;
     protected $inputFilter;

     public function exchangeArray($data)
     {
         $this->id     = (!empty($data['id'])) ? $data['id'] : null;
         $this->username = (!empty($data['username'])) ? $data['username'] : null;
         $this->time = (!empty($data['time'])) ? $data['time'] : null;
         $this->note = (!empty($data['note'])) ? $data['note'] : null;
         
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
 

            $this->inputFilter = $inputFilter;
          }
 
          return $this->inputFilter;
      }
      public function getArrayCopy()
      {
          return get_object_vars($this);
      }
 }