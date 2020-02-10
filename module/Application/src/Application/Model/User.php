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
 use Zend\Validator;
 
 class User
 {
     public $username;
     public $password;
     public $fullname;
     public $email;
     public $birthday;
     public $sex;
     public $identityNumber;
     public $identityDate;
     public $identityPlace;
     public $mobile;
     public $address;
     public $city;
     public $company;
     public $companyAddress;
     public $ip;
     protected $inputFilter;

     public function exchangeArray($data)
     {
         $this->username = (!empty($data['username'])) ? $data['username'] : null;
         $this->password = (!empty($data['password'])) ? $data['password'] : null;
         $this->fullname  = (!empty($data['fullname'])) ? $data['fullname'] : null;
         $this->email  = (!empty($data['email'])) ? $data['email'] : null;
         $this->birthday  = (!empty($data['birthday'])) ? $data['birthday'] : null;
         $this->sex  = (!empty($data['sex'])) ? $data['sex'] : null;
         $this->identityNumber  = (!empty($data['identityNumber'])) ? $data['identityNumber'] : null;
         $this->identityDate  = (!empty($data['identityDate'])) ? $data['identityDate'] : null;
         $this->identityPlace  = (!empty($data['identityPlace'])) ? $data['identityPlace'] : null;
         $this->mobile  = (!empty($data['mobile'])) ? $data['mobile'] : null;
         $this->address  = (!empty($data['address'])) ? $data['address'] : null;
         $this->city  = (!empty($data['city'])) ? $data['city'] : null;
         $this->company  = (!empty($data['company'])) ? $data['company'] : null;
         $this->companyAddress  = (!empty($data['companyAddress'])) ? $data['companyAddress'] : null;
         $this->ip  = (!empty($data['ip'])) ? $data['ip'] : null;
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
                'name'     => 'username',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 2,
                            'max'      => 24,
                        ),
                    ),
                ),
            ));

            $inputFilter->add(array(
                'name'     => 'password',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 2,
                            'max'      => 24,
                        ),
                    ),
                ),
            ));

            $inputFilter->add(array(
                'name'     => 'fullname',
                'required' => false,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'max'      => 50,
                        ),
                    ),
                ),
            ));
            $inputFilter->add(array(
                'name'     => 'email',
                'required' => false,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 6,
                            'max'      => 45,
                        ),
                    ),
                ),
            ));

            $inputFilter->add(array(
                'name'     => 'birthday',
                'required' => false,
            ));

            $inputFilter->add(array(
                'name'     => 'sex',
                'required' => false,
            ));

            $inputFilter->add(array(
                'name'     => 'identityNumber',
                'required' => false,
            ));

            $inputFilter->add(array(
                'name'     => 'mobile',
                'required' => false,
            ));

            $inputFilter->add(array(
                'name'     => 'address',
                'required' => false,
            ));

            $inputFilter->add(array(
                'name'     => 'city',
                'required' => false,
            ));

            $inputFilter->add(array(
                'name'     => 'company',
                'required' => false,
            ));

            $inputFilter->add(array(
                'name'     => 'companyAddress',
                'required' => false,
            ));

              $this->inputFilter = $inputFilter;
          }
 
          return $this->inputFilter;
      }
 }