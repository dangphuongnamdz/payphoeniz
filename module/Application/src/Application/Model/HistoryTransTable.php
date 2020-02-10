<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Model;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class HistoryTransTable
{
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    
    }
    
    public function addHistoryTrans($trans)
    {
        $data = array(
            'trans_id' => $trans['transId'],
            'server_id' => $trans['server_id'],
            'created_at' => new \Zend\Db\Sql\Expression("NOW()"),
        );
        $this->tableGateway->insert($data);
    }

    public function getHistoryTrans($trans_id)
    {
        $select = $this->tableGateway->getSql()->select();
        $select->where->like('trans_id', $trans_id);
        $select->limit(1);
        return $this->tableGateway->selectWith($select)->current();
    }
}