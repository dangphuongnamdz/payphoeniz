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
 class Category
 {
     public $id;
     public $tendanhmuc;
     public $id_parent;
     public $avatar;
     public $alias;
     public $status;
     public $created_at;
     public $updated_at;
     protected $inputFilter;

     public function exchangeArray($data)
     {
         $this->id     = (!empty($data['id'])) ? $data['id'] : null;
         $this->tendanhmuc = (!empty($data['tendanhmuc'])) ? $data['tendanhmuc'] : null;
         $this->alias = (!empty($data['alias'])) ? $data['alias'] : null;
         $this->avatar = (!empty($data['avatar'])) ? $data['avatar'] : null;
         $this->status = (!empty($data['status'])) ? $data['status'] : null;
         $this->id_parent = (!empty($data['id_parent'])) ? $data['id_parent'] : null;
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