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
 class Payment
 {
     public $seri_card;
     public $type_card;
     public $code_card;
     protected $inputFilter;

     public function exchangeArray($data)
     {
         $this->seri_card = (!empty($data['seri_card'])) ? $data['seri_card'] : null;
         $this->type_card = (!empty($data['type_card'])) ? $data['type_card'] : null;
         $this->code_card = (!empty($data['code_card'])) ? $data['code_card'] : null;
     }

      public function setInputFilter(InputFilterInterface $inputFilter)
      {
          throw new \Exception("Not used");
      }
 
      public function getInputFilter()
      {
          if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            $this->inputFilter = $inputFilter;
          }
 
          return $this->inputFilter;
      }
      public function getArrayCopy()
      {
          return get_object_vars($this);
      }
 }