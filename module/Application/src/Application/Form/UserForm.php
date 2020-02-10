<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Form;

 use Zend\Form\Form;
 
 class UserForm extends Form
 {
     public function __construct($name = null)
     {
         parent::__construct('user');

         //username
         $this->add(array(
             'name' => 'username',
             'type' => 'Text',
             'attributes' => array(
                'class' => 'form-control',
                'id'    => 'username'
             ),
             'options' => array(
                 'label' => 'Tên đăng nhập *',
                 'label_attributes' => array(
                    'for' => 'username',
                    'class' => 'col-sm-12 controll-label',
                 ),
             ),
         ));

         //password
         $this->add(array(
            'name' => 'password',
            'type' => 'password',
            'attributes' => array(
               'class' => 'form-control',
               'id'    => 'password'
            ),
            'options' => array(
                'label' => 'Mật khẩu *',
                'label_attributes' => array(
                   'for' => 'password',
                   'class' => 'col-sm-12 controll-label',
                ),
            ),
        ));
        
        //repassword
        $this->add(array(
            'name' => 'repassword',
            'type' => 'password',
            'attributes' => array(
               'class' => 'form-control',
               'id'    => 'repassword'
            ),
            'options' => array(
                'label' => 'Xác nhận password *',
                'label_attributes' => array(
                   'for' => 'repassword',
                   'class' => 'col-sm-12 controll-label',
                ),
            ),
        ));

        //newpassword
        $this->add(array(
            'name' => 'newpassword',
            'type' => 'password',
            'attributes' => array(
               'class' => 'form-control',
               'id' => 'newpassword',
            ),
            'options' => array(
                'label' => 'Mật khẩu mới *',
                'label_attributes' => array(
                   'for' => 'newpassword',
                   'class' => 'col-sm-12 controll-label',
                ),
            ),
        ));

        //renewpassword
        $this->add(array(
            'name' => 'renewpassword',
            'type' => 'password',
            'attributes' => array(
               'class' => 'form-control',
               'id' => 'renewpassword',
            ),
            'options' => array(
                'label' => 'Xác nhận mật khẩu mới *',
                'label_attributes' => array(
                   'for' => 'renewpassword',
                   'class' => 'col-sm-12 controll-label',
                ),
            ),
        ));

        //fullname
         $this->add(array(
            'name' => 'fullname',
            'type' => 'text',
            'attributes' => array(
               'class' => 'form-control',
               'id' => 'fullname',
            ),
            'options' => array(
                'label' => 'Họ tên',
                'label_attributes' => array(
                   'for' => 'fullname',
                   'class' => 'col-sm-12 controll-label',
                ),
            ),
        ));

        //email
        $this->add(array(
            'name' => 'email',
            'type' => 'email',
            'attributes' => array(
               'class' => 'form-control',
               'id' => 'email',
            ),
            'options' => array(
                'label' => 'Email *',
                'label_attributes' => array(
                   'for' => 'email',
                   'class' => 'col-sm-12 controll-label',
                ),
            ),
        ));

        //birthdate
        $this->add(array(
            'name' => 'birthday',
            'type' => 'text',
            'attributes' => array(
               'class' => 'form-control',
               'id' => 'birthday',
               'data-format' => 'YYYY-MM-DD',
               'data-template' => 'YYYY MMM D',
               'value' => '1990-01-01'         
            ),
            'options' => array(
                'label' => 'Ngày sinh',
                'label_attributes' => array(
                   'for' => 'birthday',
                   'class' => 'col-sm-12 controll-label',
                   'id'  => 'labelbirthday',                   
                ),
            ),
        ));

        //gioi tinh
        $this->add(array(
            'name' => 'sex',
            'type' => 'Radio',
            'attributes' => array(
                'id'=>'sex',
                'value'=>'1',
            ),
            'options' => array(
                'label' => 'Giới tính',
                'label_attributes' => array(
                   'for' => 'sex',
                   'class' => 'col-sm-12 controll-label',
                   'id'  => 'labelsex',
                ),
                'value_options'=> array(                 
                    '1'=>' Nam',
                    '2'=>' Nữ',
                ),
            ),
        ));
        //cmnd
        $this->add(array(
            'name' => 'identityNumber',
            'type' => 'text',
            'attributes' => array(
               'class' => 'form-control',
               'id' => 'identityNumber',
            ),
            'options' => array(
                'label' => 'Số CMND',
                'label_attributes' => array(
                   'for' => 'identityNumber',
                   'class' => 'col-sm-12 controll-label',
                ),
            ),
        ));

        //identityDate
        $this->add(array(
            'name' => 'identityDate',
            'type' => 'text',
            'attributes' => array(
               'class' => 'form-control',
               'id' => 'identityDate',
               'data-format' => 'YYYY-MM-DD',
               'data-template' => 'YYYY MMM D',
               'value' => '1990-01-01'         
            ),
            'options' => array(
                'label' => 'Ngày cấp CMND',
                'label_attributes' => array(
                   'for' => 'identityDate',
                   'class' => 'col-sm-12 controll-label',
                   'id'  => 'labelbirthday',                   
                ),
            ),
        ));
         //identityPlace
         $this->add(array(
            'name' => 'identityPlace',
            'type' => 'text',
            'attributes' => array(
               'class' => 'form-control',
               'id' => 'identityPlace',             
            ),
            'options' => array(
                'label' => 'Nơi cấp CMND',
                'label_attributes' => array(
                   'for' => 'address',
                   'class' => 'col-sm-12 controll-label',
                ),
            ),
        ));

        //mobile
        $this->add(array(
            'name' => 'mobile',
            'type' => 'text',
            'attributes' => array(
               'class' => 'form-control',
               'id' => 'mobile',               
            ),
            'options' => array(
                'label' => 'Số điện thoại',
                'label_attributes' => array(
                   'for' => 'mobile',
                   'class' => 'col-sm-12 controll-label',
                ),
            ),
        ));
        //address
        $this->add(array(
            'name' => 'address',
            'type' => 'text',
            'attributes' => array(
               'class' => 'form-control',
               'id' => 'address',             
            ),
            'options' => array(
                'label' => 'Địa chỉ',
                'label_attributes' => array(
                   'for' => 'address',
                   'class' => 'col-sm-12 controll-label',
                ),
            ),
        ));
        //city
        $this->add(array(
            'name' => 'city',
            'type' => 'select',
            'attributes' => array(
               'class' => 'form-control',
               'id'    => 'city'
            ),
            'options' => array(
                'label' => 'Thành phố',
                'label_attributes' => array(
                   'for' => 'city',
                   'class' => 'col-sm-12 controll-label',
                ),
                'value_options' => $this->getOptionsForSelect(),
            ),

        ));

        //company
        $this->add(array(
            'name' => 'company',
            'type' => 'text',
            'attributes' => array(
               'class' => 'form-control',
               'id' => 'company',             
            ),
            'options' => array(
                'label' => 'Tên công ty',
                'label_attributes' => array(
                   'for' => 'company',
                   'class' => 'col-sm-12 controll-label',
                ),
            ),
        ));
        //companyAddress
        $this->add(array(
            'name' => 'companyAddress',
            'type' => 'text',
            'attributes' => array(
               'class' => 'form-control',
               'id' => 'companyAddress',    
            ),
            'options' => array(
                'label' => 'Địa chỉ công ty',
                'label_attributes' => array(
                   'for' => 'companyAddress',
                   'class' => 'col-sm-12 controll-label',
                ),
            ),
        ));
        $this->add(array(
            'type' => 'Zend\Form\Element\Csrf',
            'name' => 'security',
            'options' => array(
                'csrf_options' => array(
                    'timeout' => 20000
                )
            )
        )); 
         $this->add(array(
             'name' => 'submit',
             'type' => 'Submit',
             'attributes' => array(
                 'value' => 'Submit',
                 'id' => 'submitbutton',
                 'class' => 'col-sm-3 btn btn-success',
             ),
         ));
     }
     public function getOptionsForSelect()
     {
        $string = file_get_contents("./data/city.json");
        $json_a = json_decode($string, true);
        $selectData = array();
        $selectData[00] = 'Chọn thành phố';
        foreach ($json_a as $person_name => $person_a) {
            $selectData[$person_a['code']] = $person_a['name'];
        }
        return $selectData;
     }
 }