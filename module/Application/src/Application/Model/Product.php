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
 class Product
 {
    public $id;
    public $name;
    public $avatar;
    public $id_user;
    public $status;
    public $order;
    public $slug;
    public $agent;
    public $payment_type;
    public $created_at;
    public $updated_at;
    public $username;
	public $limit_local;
	public $url_redirect;
    protected $inputFilter;

     public function exchangeArray($data)
     { 
	 
        $this->id     = (!empty($data['id'])) ? $data['id'] : null;
        $this->name  = (!empty($data['name'])) ? $data['name'] : null;
        $this->avatar  = (!empty($data['avatar'])) ? $data['avatar'] : null;
        $this->id_user  = (!empty($data['id_user'])) ? $data['id_user'] : null;
        $this->status  = (!empty($data['status'])) ? $data['status'] : null;
        $this->order  = (!empty($data['order'])) ? $data['order'] : null;
        $this->slug  = (!empty($data['slug'])) ? $data['slug'] : null;
        $this->agent  = (!empty($data['agent'])) ? $data['agent'] : null;
        $this->payment_type  = (!empty($data['payment_type'])) ? $data['payment_type'] : null;
        $this->created_at  = (!empty($data['created_at'])) ? $data['created_at'] : null;
        $this->updated_at  = (!empty($data['updated_at'])) ? $data['updated_at'] : null; 
        $this->username  = (!empty($data['username'])) ? $data['username'] : null;
		$this->limit_local  = (!empty($data['limit_local'])) ? $data['limit_local'] : null;
		$this->url_redirect  = (!empty($data['url_redirect'])) ? $data['url_redirect'] : null;
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