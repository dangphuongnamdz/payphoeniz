<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Model;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Paginator\Adapter\DbSelect;

 class SavelogTable
 {
     protected $tableGateway;
     //public $id_user;
     public function __construct(TableGateway $tableGateway)
     {
         $this->tableGateway = $tableGateway;
     }
	public function saveLogAction($username, $content,$function,$ip)
    {
		$string = array();
		
		$string['function']= $function;
		$string['ip'] = $ip;
		$string['action'] = $content;
		$sDb = json_encode($string);
		$data = array(
            'username' => $username,
            'note'  => $sDb,
            'time' => new \Zend\Db\Sql\Expression("NOW()")
        );
		try{
			$this->tableGateway->insert($data);
		}catch(Exception $e){
			throw new \Exception($e->getMessage());
		}
        //save file log. 
		$path = dirname(__DIR__).'/../../../../public/logs/logactions/';
		$filename = $username.'_'.date ( 'Y_m_d' );
		$handle = fopen ( $path . $filename . '.txt', 'a' );
		$string['date'] = date ( 'H:i:s d-m-Y', time () );
		$sFile = json_encode($string);
		fwrite ( $handle, $sFile."\n" );
		fclose ( $handle );		
		
    }
     
 }