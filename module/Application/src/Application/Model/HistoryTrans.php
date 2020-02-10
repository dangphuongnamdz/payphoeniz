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

 class HistoryTrans
 {
    public $id;
    public $trans_id;
    public $server_id;
    public $created_at;
    protected $inputFilter;

    public function exchangeArray($data)
    { 
    $this->id     = (!empty($data['id'])) ? $data['id'] : null;
    $this->trans_id  = (!empty($data['trans_id'])) ? $data['trans_id'] : null;
    $this->server_id  = (!empty($data['server_id'])) ? $data['server_id'] : null;
    $this->created_at  = (!empty($data['created_at'])) ? $data['created_at'] : null;
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
}