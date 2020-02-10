<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Form;

 use Zend\Form\Form;
 use Zend\Db\Adapter\AdapterInterface;
 use Zend\Db\Adapter\Adapter;
 
 class UserForm extends Form
 {
     protected $adapter;
     protected $id;
     public function __construct(AdapterInterface $dbAdapter, $id = null)
     {
         $this->adapter = $dbAdapter;
         $this->id = $id;
         parent::__construct('user');
         
         $this->add(array(
             'name' => 'id',
             'type' => 'Hidden',
         ));
         $this->add(array(
             'name' => 'username',
             'type' => 'Text',
             'attributes' => array(
                'class' => 'form-control',
             ),
             'options' => array(
                 'label' => 'Tên đăng nhập *',
                 'label_attributes' => array(
                    'for' => '',
                    'class' => 'col-sm-9 controll-label',
                 ),
             ),
         ));
         $this->add(array(
            'name' => 'password',
            'type' => 'password',
            'attributes' => array(
               'class' => 'form-control',
            ),
            'options' => array(
                'label' => 'Password *',
                'label_attributes' => array(
                   'for' => '',
                   'class' => 'col-sm-9 controll-label',
                ),
            ),
        ));
        $this->add(array(
            'name' => 'newpassword',
            'type' => 'password',
            'attributes' => array(
               'class' => 'form-control',
            ),
            'options' => array(
                'label' => 'New Password *',
                'label_attributes' => array(
                   'for' => '',
                   'class' => 'col-sm-9 controll-label',
                ),
            ),
        ));
        $this->add(array(
            'name' => 'renewpassword',
            'type' => 'password',
            'attributes' => array(
               'class' => 'form-control',
            ),
            'options' => array(
                'label' => 'Re new Password *',
                'label_attributes' => array(
                   'for' => '',
                   'class' => 'col-sm-9 controll-label',
                ),
            ),
        ));
         $this->add(array(
            'name' => 'fullname',
            'type' => 'text',
            'attributes' => array(
               'class' => 'form-control',
            ),
            'options' => array(
                'label' => 'Fullname',
                'label_attributes' => array(
                   'for' => '',
                   'class' => 'col-sm-9 controll-label',
                ),
            ),
        ));
        $this->add(array(
            'name' => 'level',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
               'class' => 'form-control select_level',
               'value' => '2'
            ),
            'options' => array(
                'label' => 'Quyền *',
                'value_options' => array(
                    '1' => 'Admin',
                    '2' => 'Editor',
                    '3' => 'Operation',
                ),
                'label_attributes' => array(
                   'for' => '',
                   'disable' => '',
                   'class' => 'col-sm-9 controll-label',
                ),
            ),
        ));
        $this->add(array(
            'name' => 'status',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
               'class' => 'form-control',
               'value' => '1'
            ),
            'options' => array(
                'label' => 'Trạng thái *',
                'value_options' => array(
                    '1' => 'Show',
                    '2' => 'Hide'
                ),
                'label_attributes' => array(
                   'for' => '',
                   'class' => 'col-sm-9 controll-label',
                ),
            ),
        ));
        $this->add(array(
            'name' => 'email',
            'type' => 'email',
            'attributes' => array(
               'class' => 'form-control',
            ),
            'options' => array(
                'label' => 'Email',
                'label_attributes' => array(
                   'for' => '',
                   'class' => 'col-sm-9 controll-label',
                ),
            ),
        ));


        
        $this->add(array(
            'name' => 'role_agent',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'class' => 'form-control',
                'id'    => 'role_agent',
                'multiple' => 'multiple',
            ),
            'options' => array(
                'label' => 'Quyền Product',
                'label_attributes' => array(
                    'for' => '',                   
                    'class' => 'col-sm-9 controll-label',
                ),
            'value_options' => $this->getOptionsForSelect(),
            ),
        ));


         $this->add(array(
             'name' => 'submit',
             'type' => 'Submit',
             'attributes' => array(
                 'value' => 'Go',
                 'id' => 'submitbutton',
                 'class' => 'col-sm-3 btn btn-basic',
             ),
             
         ));
     }

     public function getOptionsForSelect()
     {
         $dbAdapter = $this->adapter;
         $sql       = 'SELECT agent, name  FROM product';
         $statement = $dbAdapter->query($sql);
         $result    = $statement->execute();
         $selectData = array();
         if($this->id){
            $sql2       = 'SELECT role_agent  FROM users where id = '.$this->id;
            $statement2 = $dbAdapter->query($sql2);
            $result2    = $statement2->execute();
            foreach ($result2 as $res2)
                $role_agent = $res2['role_agent'];
         }
         foreach ($result as $res) {
            if($this->id){
                if (strpos($role_agent, $res['agent']) !== false){
                    $selectData2 = array(
                        'value' => $res['agent'],
                        'label' => $res['name'],
                        'selected' => true,
                    );
                }else{
                    $selectData2 = array(
                        'value' => $res['agent'],
                        'label' => $res['name'],
                        'selected' => false,
                    );
                }
            }else{
                $selectData2 = array(
                    'value' => $res['agent'],
                    'label' => $res['name'],
                    'selected' => true,
                );
            }
            array_push($selectData, $selectData2);
         }
         return $selectData;
     }
 }