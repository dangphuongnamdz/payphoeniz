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
 class Posts
 {
    public $id;
    public $summary;
    public $title;
    public $avatar;
    public $avatar_mobile;
    public $id_user;
    public $status;
    public $content;
    public $id_category;
    public $slug;
    public $view;
    public $order;
    public $created_at;
    public $updated_at;
    public $fullname;
    public $username;
    public $tendanhmuc;
     protected $inputFilter;

     public function exchangeArray($data)
     {
        $this->id     = (!empty($data['id'])) ? $data['id'] : null;
        $this->summary = (!empty($data['summary'])) ? $data['summary'] : null;
        $this->title  = (!empty($data['title'])) ? $data['title'] : null;
        $this->avatar  = (!empty($data['avatar'])) ? $data['avatar'] : null;
        $this->avatar_mobile  = (!empty($data['avatar_mobile'])) ? $data['avatar_mobile'] : null;
        $this->id_user  = (!empty($data['id_user'])) ? $data['id_user'] : null;
        $this->status  = (!empty($data['status'])) ? $data['status'] : null;
        $this->content  = (!empty($data['content'])) ? $data['content'] : null;
        $this->id_category  = (!empty($data['id_category'])) ? $data['id_category'] : null;
        $this->slug  = (!empty($data['slug'])) ? $data['slug'] : null;
        $this->view  = (!empty($data['view'])) ? $data['view'] : null;
        $this->order  = (!empty($data['order'])) ? $data['order'] : null;
        $this->created_at  = (!empty($data['created_at'])) ? $data['created_at'] : null;
        $this->updated_at  = (!empty($data['updated_at'])) ? $data['updated_at'] : null; 
        $this->tendanhmuc  = (!empty($data['tendanhmuc'])) ? $data['tendanhmuc'] : null;
        $this->username  = (!empty($data['username'])) ? $data['username'] : null;
        $this->fullname  = (!empty($data['fullname'])) ? $data['fullname'] : null;
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