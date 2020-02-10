<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Session\Container;

 class StatisticTable
 {
    protected $tableGateway;
    
    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
        $session = new Container('User');
        $this->role_agent= $session->offsetGet('role_agent');
        $this->level= $session->offsetGet('level');
    }

     public function getStatisticPeriodic($parameters)
     {
        $dbAdapter = $this->tableGateway->getAdapter();
        if($this->level != 1 && $parameters['in_product_id'] == 'null'){
            $arrResult = array();
            foreach (json_decode($this->role_agent) as &$agent) {
                $driver = $dbAdapter->getDriver();
                $connection = $driver->getConnection();
                $sql = "CALL sp_statistic_periodic (".$parameters['fromDate'].", ".$parameters['toDate'].", '".$agent."')";
                $result = $connection->execute($sql);
                $statement = $result->getResource();
                $result = $statement->fetchAll(\PDO::FETCH_OBJ);
                $statement->closeCursor();
                $arrResult = array_merge($result, $arrResult);
            }
            return $arrResult;
        }
        $driver = $dbAdapter->getDriver();
        $connection = $driver->getConnection();
        $sql = "CALL sp_statistic_periodic (".$parameters['fromDate'].", ".$parameters['toDate'].", ".$parameters['in_product_id'].")";
        $result = $connection->execute($sql);
        $statement = $result->getResource();
        $result = $statement->fetchAll(\PDO::FETCH_OBJ);
        $statement->closeCursor();
        return $result;
     }

     public function getCardGetHistory($parameters)
     {
        $dbAdapter = $this->tableGateway->getAdapter();
        if($this->level != 1 && $parameters['in_product_id'] == 'null'){
            $arrResult = array();
            foreach (json_decode($this->role_agent) as &$agent) {
                $driver = $dbAdapter->getDriver();
                $connection = $driver->getConnection();
                $sql = "CALL sp_card_get_historydatetodate (".$parameters['in_transaction'].",".$parameters['in_username'].",".$parameters['in_serial'].",".$parameters['in_code'].",".$parameters['fromDate']
                .",".$parameters['toDate'].",".$parameters['in_status'].",".$parameters['in_type'].",'".$agent."')";
                $result = $connection->execute($sql);
                $statement = $result->getResource();
                $result = $statement->fetchAll(\PDO::FETCH_OBJ);
                $statement->closeCursor();
                $arrResult = array_merge($result, $arrResult);
            }
            return $arrResult;
        }
        $driver = $dbAdapter->getDriver();
        $connection = $driver->getConnection();
        $sql = "CALL sp_card_get_historydatetodate (".$parameters['in_transaction'].",".$parameters['in_username'].",".$parameters['in_serial'].",".$parameters['in_code'].",".$parameters['fromDate']
        .",".$parameters['toDate'].",".$parameters['in_status'].",".$parameters['in_type'].",".$parameters['in_product_id'].")";
        $result = $connection->execute($sql);
        $statement = $result->getResource();
        $result = $statement->fetchAll(\PDO::FETCH_OBJ);
        $statement->closeCursor();
        return $result;
    }

    public function getPayGetHistory($parameters)
    {
        $dbAdapter = $this->tableGateway->getAdapter();
        if($this->level != 1 && $parameters['in_product_id'] == 'null'){
            $arrResult = array();
            foreach (json_decode($this->role_agent) as &$agent) {
                $driver = $dbAdapter->getDriver();
                $connection = $driver->getConnection();
                $sql = "CALL sp_pay_get_historydatetodate (".$parameters['in_transaction'].",".$parameters['in_username'].",".$parameters['in_role'].",".$parameters['in_serial'].",".$parameters['in_code'].",".$parameters['fromDate']
                .",".$parameters['toDate'].",".$parameters['in_status'].",".$parameters['in_server'].",".$parameters['in_type'].",'".$agent."')";
                $result = $connection->execute($sql);
                $statement = $result->getResource();
                $result = $statement->fetchAll(\PDO::FETCH_OBJ);
                $statement->closeCursor();
                $arrResult = array_merge($result, $arrResult);
            }
            return $arrResult;
        }
        $driver = $dbAdapter->getDriver();
        $connection = $driver->getConnection();
        $sql = "CALL sp_pay_get_historydatetodate (".$parameters['in_transaction'].",".$parameters['in_username'].",".$parameters['in_role'].",".$parameters['in_serial'].",".$parameters['in_code'].",".$parameters['fromDate']
        .",".$parameters['toDate'].",".$parameters['in_status'].",".$parameters['in_server'].",".$parameters['in_type'].",".$parameters['in_product_id'].")";
        $result = $connection->execute($sql);
        $statement = $result->getResource();
        $result = $statement->fetchAll(\PDO::FETCH_OBJ);
        $statement->closeCursor();
        return $result;
    }

    public function getIAPGetHistory($parameters)
    {
       $dbAdapter = $this->tableGateway->getAdapter();
       if($this->level != 1 && $parameters['in_product_id'] == 'null'){
           $arrResult = array();
           foreach (json_decode($this->role_agent) as &$agent) {
                $driver = $dbAdapter->getDriver();
                $connection = $driver->getConnection();
               $sql = "CALL sp_iap_get_historydatetodate (".$parameters['in_transaction'].",".$parameters['in_uid'].",".$parameters['in_server'].",".$parameters['fromDate']
               .",".$parameters['toDate'].",".$parameters['in_status'].",".$parameters['in_amount_start'].",".$parameters['in_amount_end'].",'".$agent."',".$parameters['in_os'].")";
               $result = $connection->execute($sql);
               $statement = $result->getResource();
               $result = $statement->fetchAll(\PDO::FETCH_OBJ);
               $statement->closeCursor();
               $arrResult = array_merge($result, $arrResult);
           }
           return $arrResult;
       }
        $driver = $dbAdapter->getDriver();
        $connection = $driver->getConnection();
        $sql = "CALL sp_iap_get_historydatetodate (".$parameters['in_transaction'].",".$parameters['in_uid'].",".$parameters['in_server'].",".$parameters['fromDate']
        .",".$parameters['toDate'].",".$parameters['in_status'].",".$parameters['in_amount_start'].",".$parameters['in_amount_end'].",".$parameters['in_product_id'].",".$parameters['in_os'].")";
        $result = $connection->execute($sql);
        $statement = $result->getResource();
        $result = $statement->fetchAll(\PDO::FETCH_OBJ);
        $statement->closeCursor();
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

    public function getDetailIAPGetHistory($id)
    {
       $dbAdapter = $this->tableGateway->getAdapter();
       $driver = $dbAdapter->getDriver();
       $connection = $driver->getConnection();
       $sql = "CALL sp_iap_get_historydetail ('".$id."')";
       $result = $connection->execute($sql);
       $statement = $result->getResource();
       $result = $statement->fetchAll(\PDO::FETCH_OBJ);
       return $result;
    }

 }