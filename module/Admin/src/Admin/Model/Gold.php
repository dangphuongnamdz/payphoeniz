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
 class Gold
 {
    public $id;
    public $image;
    public $amount;
    public $gold;
    public $product_gold_id;
    public $card_month;
    public $product_id;
    public $created_at;
    public $updated_at;
    protected $inputFilter;

     public function exchangeArray($data)
     { 
        $this->id     = (!empty($data['id'])) ? $data['id'] : null;
        $this->image  = (!empty($data['image'])) ? $data['image'] : null;
        $this->amount  = (!empty($data['amount'])) ? $data['amount'] : null;
        $this->gold  = (!empty($data['gold'])) ? $data['gold'] : null;
        $this->product_gold_id  = (!empty($data['product_gold_id'])) ? $data['product_gold_id'] : null;
        $this->card_month  = (!empty($data['card_month'])) ? $data['card_month'] : null;
        $this->product_id  = (!empty($data['product_id'])) ? $data['product_id'] : null;
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
              $this->inputFilter = $inputFilter;
          }
 
          return $this->inputFilter;
      }
      public function getArrayCopy()
      {
          return get_object_vars($this);
      }
 }