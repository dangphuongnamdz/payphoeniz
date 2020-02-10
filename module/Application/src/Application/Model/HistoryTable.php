<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

 class HistoryTable
 {
    protected $tableGateway;
    
    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

     public function getCardGetHistory($parameters)
     {
        $dbAdapter = $this->tableGateway->getAdapter();
        $driver = $dbAdapter->getDriver();
        $connection = $driver->getConnection();
        $sql = "CALL sp_card_get_historydatetodate (".$parameters['in_transaction'].",".$parameters['in_username'].",".$parameters['in_serial'].",".$parameters['in_code'].",".$parameters['fromDate']
        .",".$parameters['toDate'].",".$parameters['in_status'].",".$parameters['in_type'].",".$parameters['in_product_id'].")";
        $result = $connection->execute($sql);
        $statement = $result->getResource();
        $result = $statement->fetchAll(\PDO::FETCH_OBJ);
        return $result;
    }

    public function getPayGetHistory($parameters)
    {
       $dbAdapter = $this->tableGateway->getAdapter();
       $driver = $dbAdapter->getDriver();
       $connection = $driver->getConnection();
       $sql = "CALL sp_pay_get_historydatetodate (".$parameters['in_transaction'].",".$parameters['in_username'].",".$parameters['in_role'].",".$parameters['in_serial'].",".$parameters['in_code'].",".$parameters['fromDate']
       .",".$parameters['toDate'].",".$parameters['in_status'].",".$parameters['in_server'].",".$parameters['in_type'].",".$parameters['in_product_id'].")";
       $result = $connection->execute($sql);
       $statement = $result->getResource();
       $result = $statement->fetchAll(\PDO::FETCH_OBJ);
       return $result;
    }

    public function getDetailCardGetHistory($id)
    {
       $dbAdapter = $this->tableGateway->getAdapter();
       $driver = $dbAdapter->getDriver();
       $connection = $driver->getConnection();
       $sql = "CALL sp_card_get_historydetail ('".$id."')";
       $result = $connection->execute($sql);
       $statement = $result->getResource();
       $result = $statement->fetchAll(\PDO::FETCH_OBJ);
       return $result;
    }

    public function getDetailPayGetHistory($id)
    {
       $dbAdapter = $this->tableGateway->getAdapter();
       $driver = $dbAdapter->getDriver();
       $connection = $driver->getConnection();
       $sql = "CALL sp_pay_get_historydetail ('".$id."')";
       $result = $connection->execute($sql);
       $statement = $result->getResource();
       $result = $statement->fetchAll(\PDO::FETCH_OBJ);
       return $result;
    }

 }