<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonAdmin for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Mvc\MvcEvent;
use Admin\Form\LogCards\StatisticForm;    
use Admin\Form\LogCards\CardHistoryForm;   
use Admin\Form\LogCards\PayHistoryForm;   
use Admin\Form\LogCards\IAPHistoryForm;   
use Zend\Session\Container;

 class StatisticController extends AbstractActionController
 {
    protected $productTable;
    protected $serverTable;
    protected $statisticTable;
    public $per_page = 100;
    public function onDispatch(MvcEvent $e)
    {
        if (! $this->getServiceLocator()->get('AuthService')->hasIdentity()){
            return $this->redirect()->toRoute('login');
        }
        return parent::onDispatch($e);
    }

    public function object_to_array($data)
    {
        if (is_array($data) || is_object($data))
        {
            $result = array();
            foreach ($data as $key => $value)
            {
                $result[$key] = $this->object_to_array($value);
                
            }
            return $result;
        }
        return $data;
    }

    public function getStatisticTable()
    {
        if (!$this->statisticTable) {
            $sm = $this->getServiceLocator();
            $this->statisticTable = $sm->get('Admin\Model\StatisticTable');
        }
        return $this->statisticTable;
    }
    
    public function getProductTable()
    {
        if (!$this->productTable) {
            $sm = $this->getServiceLocator();
            $this->productTable = $sm->get('Admin\Model\ProductTable');
        }
        return $this->productTable;
    }

    public function getServerTable()
    {
        if (!$this->serverTable) {
            $sm = $this->getServiceLocator();
            $this->serverTable = $sm->get('Admin\Model\ServerTable');
        }
        return $this->serverTable;
    }

    public function indexAction()
    {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new StatisticForm($dbAdapter);
        $form->get('submit')->setValue('Search');
        $request = $this->getRequest();
        //set null for param
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        if ($request->isPost()) {
            $time = $request->getPost('in_time');
            $time = explode('-',$time);
            $fromDate = str_replace('-','',date('Y-m-d',strtotime($time[0])));
            $toDate = 	str_replace('-','',date('Y-m-d',strtotime($time[1])));
            ///////////////////////////////////////////////////
            $parameters['fromDate'] = $fromDate;
            $parameters['toDate'] = $toDate;
            $parameters['in_product_id'] = ($request->getPost('in_product_id') == '') ? 'null' : "'".$request->getPost('in_product_id')."'";          
            ///////////////////////////////////////////////////
            $result = $this->getStatisticTable()->getStatisticPeriodic($parameters); 
            $form->setData($request->getPost());
            $totalAmount = 0;
            $totalGold = 0;
            foreach ($result as &$row) {
                $totalAmount+=$row->amount+$row->amount_iap;
                $totalGold+=$row->gold+$row->gold_iap;
            }
            return (array(
                'result' => $result,
                'totalAmount' => $totalAmount,
                'totalGold' => $totalGold,
                'form' => $form,
                'status' => true
            ));
        }else{
            $current_date = date('Y-m-d');        
            $parameters['fromDate'] = str_replace('-','',$current_date);
            $parameters['toDate'] = str_replace('-','',$current_date);
            $parameters['in_product_id']  = 'null';            
            $array = $this->getStatisticTable()->getStatisticPeriodic($parameters); 
            $totalAmount = 0;
            $totalGold = 0;
            foreach ($array as &$row) {
                $totalAmount+=$row->amount+$row->amount_iap;
                $totalGold+=$row->gold+$row->gold_iap;
            }
            return array(
                'form' => $form,
                'result' => $array,
                'totalAmount' => $totalAmount,
                'totalGold' => $totalGold,
                'status' => false            
            );
        }
    }

    public function cardGetHistoryAction()
    {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new CardHistoryForm($dbAdapter);
        $form->get('submit')->setValue('Search');
        $request = $this->getRequest();
        $session = new Container('log');
        $session->setExpirationSeconds(60 * 30);
        if ($request->isPost()) {
            $time = $request->getPost('in_time');
            $time = explode('-',$time);
            $fromDate = str_replace('-','',date('Y-m-d',strtotime($time[0])));
            $toDate = 	str_replace('-','',date('Y-m-d',strtotime($time[1])));
            ///////////////////////////////////////////////////
            $in_username    =   ($request->getPost('in_username') == '')    ? 'null'     : "'".$request->getPost('in_username')."'";
            $in_transaction =   ($request->getPost('in_transaction') == '') ? 'null'     : "'".$request->getPost('in_transaction')."'";
            $in_status      =   ($request->getPost('in_status') == '')      ? 'null'     : "'".$request->getPost('in_status')."'";
            $in_type        =   ($request->getPost('in_type') == '')        ? 'null'     : "'".$request->getPost('in_type')."'" ;
            $in_serial      =   ($request->getPost('in_serial') == '')      ? 'null'     : "'".$request->getPost('in_serial')."'";
            $in_code        =   ($request->getPost('in_code') == '')      ? 'null'     : "'".$request->getPost('in_code')."'";
            $in_product_id  =   ($request->getPost('in_product_id') == '')  ? 'null'     : "'".$request->getPost('in_product_id')."'";            
            $parameters['in_username'] = $in_username;
            $parameters['in_transaction'] = $in_transaction;
            $parameters['in_status'] = $in_status;
            $parameters['in_type'] = $in_type;
            $parameters['in_serial'] = $in_serial;
            $parameters['in_code'] = $in_code;
            $parameters['in_product_id'] = $in_product_id;            
            $parameters['fromDate'] = $fromDate;
            $parameters['toDate'] = $toDate;
            ///////////////////////////////////////////////////
            $array = $this->getStatisticTable()->getCardGetHistory($parameters); 
            $total = 0;
            foreach ($array as &$row) {
                if($row->card_status == 1)
                    if (!in_array($row->card_type, array('GOLD', 'REF')))
                        $total+=$row->amount;
            }
            //save session
            $session->offsetSet('cardGetHistory', $array);
            $current_page = 1;
            $total_rows = count($array);
            $pages = ceil($total_rows / $this->per_page);
            $start = $current_page * $this->per_page - $this->per_page;
            $slice = array_slice($array, $start, $this->per_page);
            $form->setData($request->getPost());
            return (array(
                'result' => $slice,
                'form' => $form,
                'pages'  => $pages,
                "products" => $this->object_to_array($this->getProductTable()->fetchAll()),
                'total' => $total,                
                'current_page' => $current_page,
                'status' => true                
            ));
        }
        //Paging session
        else if($this->params()->fromQuery('page')!=null){
            if($session->offsetGet('cardGetHistory')!=null){
                $array = $session->offsetGet('cardGetHistory');
                $total = 0;
                foreach ($array as &$row) {
                    if($row->card_status == 1)
                        if (!in_array($row->card_type, array('GOLD', 'REF')))
                            $total+=$row->amount;
                }
                $total_rows = count($array);
                $pages = ceil($total_rows / $this->per_page);
                $current_page = $this->params()->fromQuery('page', 1);
                $start = $current_page * $this->per_page - $this->per_page;
                $slice = array_slice($array, $start, $this->per_page);
                $form->setData($request->getPost());
                return (array(
                    'result' => $slice,
                    'form' => $form,
                    'pages'  => $pages,
                    "products" => $this->object_to_array($this->getProductTable()->fetchAll()),
                    'total' => $total,
                    'current_page' => $current_page,
                    'status' => true                
                ));
            }
        }//default
        else{
            //set null for param
            $parameters['in_username'] = 'null';
            $parameters['in_transaction'] = 'null';
            $parameters['in_status'] = 'null';
            $parameters['in_type'] = 'null';
            $parameters['in_serial'] = 'null';
            $parameters['in_code'] = 'null';
            $parameters['in_product_id'] = 'null';    
            date_default_timezone_set('Asia/Ho_Chi_Minh');
            $date = date('Y-m-d');        
            $parameters['fromDate'] = str_replace('-','',$date);
            $parameters['toDate'] = str_replace('-','',$date);
            $array = $this->getStatisticTable()->getCardGetHistory($parameters);
            $total = 0;
            foreach ($array as &$row) {
                if($row->card_status == 1)
                    if (!in_array($row->card_type, array('GOLD', 'REF')))
                        $total+=$row->amount;
            }
            return array(
                'form' => $form,
                "products" => $this->object_to_array($this->getProductTable()->fetchAll()),
                'result' => $array,
                'total' => $total,
                'status' => false                
            );
        }
    }

    public function detailcardGetHistoryAction()
    {
        $id = $this->params()->fromQuery('id', null);
        if($id == null){
            return $this->redirect()->toRoute('adminthongke', array(
                'action' => 'cardGetHistory'
            ));
        }
        $result = $this->getStatisticTable()->getDetailCardGetHistory($id);
        if(empty($result)){
            return $this->redirect()->toRoute('adminthongke', array(
                'action' => 'cardGetHistory'
            ));
        }
        else
        {
            return array(
                'result' => $result,
            );
        }
    }

    public function exportcardAction(){
        $session = new Container('log');
        if($session->offsetGet('cardGetHistory')!=null){
            $resultData = $session->offsetGet('cardGetHistory');
            date_default_timezone_set("Asia/Ho_Chi_Minh");
            // Create new PHPExcel object
            $objPHPExcel = new \PHPExcel();
            // Set document properties
            $objPHPExcel->getProperties()->setCreator("100d Admin")
                                        ->setLastModifiedBy("100d Admin")
                                        ->setTitle("100d Admin")
                                        ->setSubject("100d Admin")
                                        ->setDescription("100d Admin")
                                        ->setKeywords("100d Admin")
                                        ->setCategory("100d Admin");
            //Xét chiều rộng cho từng, nếu muốn set height thì dùng setRowHeight()
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
            //Xét in đậm cho khoảng cột
            $objPHPExcel->getActiveSheet()->getStyle('A1:C1')->getFont()->setBold(true);
            //Tạo tiêu đề cho từng cột
            $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Mã giao dịch');
            $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Loại thẻ');
            $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Game');
            $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Mệnh giá');
            $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Giá trị duy đổi');
            $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Mã lỗi thẻ');
            $objPHPExcel->getActiveSheet()->setCellValue('G1', 'Username');
            $objPHPExcel->getActiveSheet()->setCellValue('H1', 'Thông báo lỗi');
            $objPHPExcel->getActiveSheet()->setCellValue('I1', 'Ngày thực hiện');
            // thực hiện thêm dữ liệu vào từng ô bằng vòng lặp
            // dòng bắt đầu = 2
            $numRow = 2;
            for ($i=0; $i < count($resultData); $i++) { 
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $numRow, $resultData[$i]->transaction_id);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $numRow, $resultData[$i]->card_type);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $numRow, $resultData[$i]->product_id);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $numRow, $resultData[$i]->amount);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $numRow, $resultData[$i]->gold);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $numRow, $resultData[$i]->card_status);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $numRow, $resultData[$i]->username);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $numRow, $resultData[$i]->card_message);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $numRow, $resultData[$i]->create_date);
                $numRow++;
            }
            $objPHPExcel->getActiveSheet()->setTitle('Simple');
            // Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $objPHPExcel->setActiveSheetIndex(0);
            // Redirect output to a client’s web browser (Excel2007)
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="excel_'.date('Y-m-d h:i:s').'.xlsx"');
            header('Cache-Control: max-age=0');
            // If you're serving to IE 9, then the following may be needed
            header('Cache-Control: max-age=1');
            // If you're serving to IE over SSL, then the following may be needed
            header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
            header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header ('Pragma: public'); // HTTP/1.0
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save('php://output');
            exit;
        }
        echo 'Không có dữ liệu';
        exit();
    }

    public function payGetHistoryAction()
    {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $agent = $this->params()->fromRoute('id', null);
        if($agent!=null){
            $form = new PayHistoryForm ($dbAdapter, $agent);
        }else{
            $form = new PayHistoryForm ($dbAdapter);            
        }
        $form->get('submit')->setValue('Search');
        $request = $this->getRequest();
		$totalGold = 0;
        if ($request->isPost()) {
            $session = new Container('log');
            $session->setExpirationSeconds(60 * 30);
            $time = $request->getPost('in_time');
            $time = explode('-',$time);
            $fromDate = str_replace('-','',date('Y-m-d',strtotime($time[0])));
            $toDate = 	str_replace('-','',date('Y-m-d',strtotime($time[1])));
            ///////////////////////////////////////////////////
            $in_username    =   ($request->getPost('in_username') == '')    ? 'null'     : "'".$request->getPost('in_username')."'";
            $in_role        =   ($request->getPost('in_role') == '')    ? 'null'     : "'".$request->getPost('in_role')."'";
            $in_transaction =   ($request->getPost('in_transaction') == '') ? 'null'     : "'".$request->getPost('in_transaction')."'";
            $in_status      =   ($request->getPost('in_status') == '')      ? 'null'     : "'".$request->getPost('in_status')."'";
            $in_type        =   ($request->getPost('in_type') == '')        ? 'null'     : "'".$request->getPost('in_type')."'" ;
            $in_code        =   ($request->getPost('in_code') == '')      ? 'null'     : "'".$request->getPost('in_code')."'";
            $in_serial      =   ($request->getPost('in_serial') == '')      ? 'null'     : "'".$request->getPost('in_serial')."'";
            $in_server      =   ($request->getPost('in_server') == '')      ? 'null'     : "'".$request->getPost('in_server')."'";
            $in_product_id  =   ($request->getPost('in_product_id') == '')  ? 'null'     : "'".$request->getPost('in_product_id')."'";
            $parameters['in_username'] = $in_username;
            $parameters['in_role'] = $in_role;
            $parameters['in_transaction'] = $in_transaction;
            $parameters['in_status'] = $in_status;
            $parameters['in_type'] = $in_type;
            $parameters['in_code'] = $in_code;
            $parameters['in_serial'] = $in_serial;
            $parameters['in_server'] = $in_server;
            $parameters['in_product_id'] = $in_product_id;
            $parameters['fromDate'] = $fromDate;
            $parameters['toDate'] = $toDate;
            ///////////////////////////////////////////////////
            $array = $this->getStatisticTable()->getPayGetHistory($parameters); 
            $total = 0;
			
            foreach ($array as &$row) {
                if($row->pay == 1){					
                    $total+=$row->amount;
					$totalGold+=$row->gold;
				}
            }
            //save session
            $session->offsetSet('payGetHistory', $array);
            $current_page = 1;
            $total_rows = count($array);
            $pages = ceil($total_rows / $this->per_page);
            $start = $current_page * $this->per_page - $this->per_page;
            $slice = array_slice($array, $start, $this->per_page);
            $form->setData($request->getPost());
            return (array(
                'result' => $slice,
                'form' => $form,
                'pages'  => $pages,
                "products" => $this->object_to_array($this->getProductTable()->fetchAll()), 
                "servers" => $this->object_to_array($this->getServerTable()->fetchAll()),
                'total' => $total,
				'totalGold' => $totalGold,
                'current_page' => $current_page,
                'status' => true                
            ));
        }//Paging session
        else if($this->params()->fromQuery('page')!=null){
            $session = new Container('log');
            $session->setExpirationSeconds(60 * 30);
            if($session->offsetGet('payGetHistory')!=null){
                $array = $session->offsetGet('payGetHistory');
                $total = 0;
                foreach ($array as &$row) {
                    if($row->pay == 1){
                        $total+=$row->amount;
						$totalGold+=$row->gold;
					}
                }
                $total_rows = count($array);
                $pages = ceil($total_rows / $this->per_page);
                $current_page = $this->params()->fromQuery('page', 1);
                $start = $current_page * $this->per_page - $this->per_page;
                $slice = array_slice($array, $start, $this->per_page);
                $form->setData($request->getPost());
                return (array(
                    'result' => $slice,
                    'form' => $form,
                    'pages'  => $pages,
                    "products" => $this->object_to_array($this->getProductTable()->fetchAll()),
                    "servers" => $this->object_to_array($this->getServerTable()->fetchAll()),
                    'total' => $total, 
					'totalGold' => $totalGold,
                    'current_page' => $current_page,
                    'status' => true                
                ));
            }
        }//default
        else{
            //set null for param
            $parameters['in_username'] = 'null';
            $parameters['in_role'] = 'null';
            $parameters['in_transaction'] = 'null';
            $parameters['in_status'] = 'null';
            $parameters['in_type'] = 'null';
            $parameters['in_code'] = 'null';
            $parameters['in_serial'] = 'null';
            $parameters['in_server'] = 'null';
            $parameters['in_product_id'] = ($agent != null) ? "'".$agent."'" : 'null';
            date_default_timezone_set('Asia/Ho_Chi_Minh');
            $current_date = date('Y-m-d');        
            $parameters['fromDate'] = str_replace('-','',$current_date);
            $parameters['toDate'] = str_replace('-','',$current_date);
            ///////////////////////////////////////////////////
            $array = $this->getStatisticTable()->getPayGetHistory($parameters); 
            $total = 0;
            foreach ($array as &$row) {
                if($row->pay == 1){
                    $total+=$row->amount;
					$totalGold+=$row->gold;
				}
            }
            return array(
                'form' => $form,
                'result' => $array,
                "products" => $this->object_to_array($this->getProductTable()->fetchAll()), 
                "servers" => $this->object_to_array($this->getServerTable()->fetchAll()),
                'total' => $total, 
				'totalGold' => $totalGold,
                'status' => false                
            );
        }
    }

    public function detailpayGetHistoryAction()
    {
        $id = $this->params()->fromQuery('id', null);
        if($id == null){
            return $this->redirect()->toRoute('adminthongke', array(
                'action' => 'payGetHistory'
            ));
        }
        $result = $this->getStatisticTable()->getDetailPayGetHistory($id);
        if(empty($result)){
            return $this->redirect()->toRoute('adminthongke', array(
                'action' => 'payGetHistory'
            ));
        }
        else
        {
            return array(
                'result' => $result,
            );
        }
    }

    public function exportpayAction(){
        $session = new Container('log');
        if($session->offsetGet('payGetHistory')!=null){
            $resultData = $session->offsetGet('payGetHistory');
            date_default_timezone_set("Asia/Ho_Chi_Minh");
            // Create new PHPExcel object
            $objPHPExcel = new \PHPExcel();
            // Set document properties
            $objPHPExcel->getProperties()->setCreator("100d Admin")
                                        ->setLastModifiedBy("100d Admin")
                                        ->setTitle("100d Admin")
                                        ->setSubject("100d Admin")
                                        ->setDescription("100d Admin")
                                        ->setKeywords("100d Admin")
                                        ->setCategory("100d Admin");
            //Xét chiều rộng cho từng, nếu muốn set height thì dùng setRowHeight()
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
            //Xét in đậm cho khoảng cột
            $objPHPExcel->getActiveSheet()->getStyle('A1:C1')->getFont()->setBold(true);
            //Tạo tiêu đề cho từng cột
            $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Mã giao dịch');
            $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Loại thẻ');
            $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Username');
            $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Rolename');
            $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Mệnh giá');
            $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Giá trị duy đổi');
            $objPHPExcel->getActiveSheet()->setCellValue('G1', 'Mã lỗi thẻ');
            $objPHPExcel->getActiveSheet()->setCellValue('H1', 'Thông báo lỗi');
            $objPHPExcel->getActiveSheet()->setCellValue('I1', 'Game');
            $objPHPExcel->getActiveSheet()->setCellValue('J1', 'Server');
            $objPHPExcel->getActiveSheet()->setCellValue('K1', 'Mã lỗi nạp game');
            $objPHPExcel->getActiveSheet()->setCellValue('L1', 'Thông báo nạp game');
            $objPHPExcel->getActiveSheet()->setCellValue('M1', 'Ngày thực hiện');
            // thực hiện thêm dữ liệu vào từng ô bằng vòng lặp
            // dòng bắt đầu = 2
            $numRow = 2;
            for ($i=0; $i < count($resultData); $i++) { 
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $numRow, $resultData[$i]->transaction_id);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $numRow, $resultData[$i]->card_type);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $numRow, $resultData[$i]->username);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $numRow, $resultData[$i]->role);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $numRow, $resultData[$i]->amount);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $numRow, $resultData[$i]->gold);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $numRow, $resultData[$i]->card_status);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $numRow, $resultData[$i]->card_message);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $numRow, $resultData[$i]->product_id);
                $objPHPExcel->getActiveSheet()->setCellValue('J' . $numRow, $resultData[$i]->server_id);
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $numRow, $resultData[$i]->pay);
                $pay_message = ($resultData[$i]->pay==1)?'Nạp vào game thành công':'Nạp vào game thất bại';
                $objPHPExcel->getActiveSheet()->setCellValue('L' . $numRow, $pay_message);
                $objPHPExcel->getActiveSheet()->setCellValue('M' . $numRow, $resultData[$i]->create_date);
                $numRow++;
            }
            $objPHPExcel->getActiveSheet()->setTitle('Simple');
            // Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $objPHPExcel->setActiveSheetIndex(0);
            // Redirect output to a client’s web browser (Excel2007)
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="excel_'.date('Y-m-d h:i:s').'.xlsx"');
            header('Cache-Control: max-age=0');
            // If you're serving to IE 9, then the following may be needed
            header('Cache-Control: max-age=1');
            // If you're serving to IE over SSL, then the following may be needed
            header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
            header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header ('Pragma: public'); // HTTP/1.0
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save('php://output');
            exit;
        }
        echo 'Không có dữ liệu';
        exit();
    }

    public function iapGetHistoryAction()
    {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $agent = $this->params()->fromRoute('id', null);
        if($agent!=null){
            $form = new IAPHistoryForm ($dbAdapter, $agent);
        }else{
            $form = new IAPHistoryForm ($dbAdapter);            
        }
        $form->get('submit')->setValue('Search');
        $request = $this->getRequest();
        $session = new Container('log');
        $session->setExpirationSeconds(60 * 30);
		$totalGold = 0;
        if ($request->isPost()) {
            $time = $request->getPost('in_time');
            $time = explode('-',$time);
            $fromDate = str_replace('-','',date('Y-m-d',strtotime($time[0])));
            $toDate = 	str_replace('-','',date('Y-m-d',strtotime($time[1])));
            ///////////////////////////////////////////////////
            $in_uid    =   ($request->getPost('in_uid') == '')    ? 'null'     : "'".$request->getPost('in_uid')."'";
            $in_transaction =   ($request->getPost('in_transaction') == '') ? 'null'     : "'".$request->getPost('in_transaction')."'";
            $in_status      =   ($request->getPost('in_status') == '')      ? 'null'     : "'".$request->getPost('in_status')."'";
            $in_amount_start        =   ($request->getPost('in_amount_start') == '')        ? 10     : "'".$request->getPost('in_amount_start')."'" ;
            $in_amount_end      =   ($request->getPost('in_amount_end') == '')      ? 1000000000     : "'".$request->getPost('in_amount_end')."'";
            $in_server      =   ($request->getPost('in_server') == '')      ? 'null'     : "'".$request->getPost('in_server')."'";
            $in_product_id  =   ($request->getPost('in_product_id') == '')  ? 'null'     : "'".$request->getPost('in_product_id')."'";
            $in_os  =   ($request->getPost('in_os') == '')  ? 'null'     : "'".$request->getPost('in_os')."'";
            $parameters['in_uid'] = $in_uid;
            $parameters['in_transaction'] = $in_transaction;
            $parameters['in_status'] = $in_status;
            $parameters['in_amount_start'] = $in_amount_start;
            $parameters['in_amount_end'] = $in_amount_end;
            $parameters['in_server'] = $in_server;
            $parameters['in_product_id'] = $in_product_id;
            $parameters['fromDate'] = $fromDate;
            $parameters['toDate'] = $toDate;
            $parameters['in_os'] = $in_os;
            ///////////////////////////////////////////////////
            $array = $this->getStatisticTable()->getIAPGetHistory($parameters); 
            $total = 0;
			
            foreach ($array as &$row) {
                if($row->status == 1)
				{
                    $total+=$row->order_vnd;
					$totalGold+=$row->order_amount;
				}
            }
            //save session
            $session->offsetSet('iapGetHistory', $array);
            $current_page = 1;
            $total_rows = count($array);
            $pages = ceil($total_rows / $this->per_page);
            $start = $current_page * $this->per_page - $this->per_page;
            $slice = array_slice($array, $start, $this->per_page);
            $form->setData($request->getPost());
            return (array(
                'result' => $slice,
                'form' => $form,
                'pages'  => $pages,
                "products" => $this->object_to_array($this->getProductTable()->fetchAll()),
                "servers" => $this->object_to_array($this->getServerTable()->fetchAll()),
                'total' => $total,
				'totalGold'=>$totalGold,
                'current_page' => $current_page,
                'status' => true                
            ));
        }//Paging session
        else if($this->params()->fromQuery('page')!=null){
            if($session->offsetGet('iapGetHistory')!=null){
                $array = $session->offsetGet('iapGetHistory');
                $total = 0;
                foreach ($array as &$row) {
                    if($row->status == 1){
                        $total+=$row->order_vnd;
						$totalGold+=$row->order_amount;
					}
                }
                $total_rows = count($array);
                $pages = ceil($total_rows / $this->per_page);
                $current_page = $this->params()->fromQuery('page', 1);
                $start = $current_page * $this->per_page - $this->per_page;
                $slice = array_slice($array, $start, $this->per_page);
                $form->setData($request->getPost());
                return (array(
                    'result' => $slice,
                    'form' => $form,
                    'pages'  => $pages,
                    "products" => $this->object_to_array($this->getProductTable()->fetchAll()),
                    "servers" => $this->object_to_array($this->getServerTable()->fetchAll()),
                    'total' => $total, 
					'totalGold'=>$totalGold,					
                    'current_page' => $current_page,
                    'status' => true                
                ));
            }
        }//default
        else{
            //set null for param
            $parameters['in_uid'] = 'null';
            $parameters['in_transaction'] = 'null';
            $parameters['in_status'] = 'null';
            $parameters['in_amount_start'] = 100;
            $parameters['in_amount_end'] = 1000000000;
            $parameters['in_server'] = 'null';
            $parameters['in_product_id'] = ($agent != null) ? "'".$agent."'" : 'null';
            date_default_timezone_set('Asia/Ho_Chi_Minh');
            $current_date = date('Y-m-d');        
            $parameters['fromDate'] = str_replace('-','',$current_date);
            $parameters['toDate'] = str_replace('-','',$current_date);
            $parameters['in_os'] = 'null';
            $array = $this->getStatisticTable()->getIAPGetHistory($parameters);  
            $total = 0;
            foreach ($array as &$row) {
                if($row->status == 1){
                    $total+=$row->order_vnd;
					$totalGold+=$row->order_amount;
				}
            }
            return array(
                'form' => $form,
                'result' => $array,
                "products" => $this->object_to_array($this->getProductTable()->fetchAll()), 
                "servers" => $this->object_to_array($this->getServerTable()->fetchAll()),
                'total' => $total,  
				'totalGold'=>$totalGold,
                'status' => false                
            );
        }
    }

    public function detailiapGetHistoryAction()
    {
        
        $id = $this->params()->fromQuery('id', null);
        if($id == null){
            return $this->redirect()->toRoute('adminthongke', array(
                'action' => 'iapGetHistory'
            ));
        }
        $result = $this->getStatisticTable()->getDetailIAPGetHistory($id);
        if(empty($result)){
            return $this->redirect()->toRoute('adminthongke', array(
                'action' => 'iapGetHistory'
            ));
        }
        else
        {
            return array(
                'result' => $result,
            );
        }
    }

    public function exportiapAction(){
        $session = new Container('log');
        if($session->offsetGet('iapGetHistory')!=null){
            $resultData = $session->offsetGet('iapGetHistory');
            date_default_timezone_set("Asia/Ho_Chi_Minh");
            // Create new PHPExcel object
            $objPHPExcel = new \PHPExcel();
            // Set document properties
            $objPHPExcel->getProperties()->setCreator("100d Admin")
                                        ->setLastModifiedBy("100d Admin")
                                        ->setTitle("100d Admin")
                                        ->setSubject("100d Admin")
                                        ->setDescription("100d Admin")
                                        ->setKeywords("100d Admin")
                                        ->setCategory("100d Admin");
            //Xét chiều rộng cho từng, nếu muốn set height thì dùng setRowHeight()
            $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
            //Xét in đậm cho khoảng cột
            $objPHPExcel->getActiveSheet()->getStyle('A1:C1')->getFont()->setBold(true);
            //Tạo tiêu đề cho từng cột
            $objPHPExcel->getActiveSheet()->setCellValue('A1', 'Mã giao dịch');
            $objPHPExcel->getActiveSheet()->setCellValue('B1', 'Uid');
            $objPHPExcel->getActiveSheet()->setCellValue('C1', 'Username');
            $objPHPExcel->getActiveSheet()->setCellValue('D1', 'Mệnh giá');
            $objPHPExcel->getActiveSheet()->setCellValue('E1', 'Giá trị duy đổi');
            $objPHPExcel->getActiveSheet()->setCellValue('F1', 'Payload');
            $objPHPExcel->getActiveSheet()->setCellValue('G1', 'Game');
            $objPHPExcel->getActiveSheet()->setCellValue('H1', 'Server');
            $objPHPExcel->getActiveSheet()->setCellValue('I1', 'OS');
            $objPHPExcel->getActiveSheet()->setCellValue('J1', 'Trạng thái');
            $objPHPExcel->getActiveSheet()->setCellValue('K1', 'Ngày thực hiện');

            // thực hiện thêm dữ liệu vào từng ô bằng vòng lặp
            // dòng bắt đầu = 2
            $numRow = 2;
            for ($i=0; $i < count($resultData); $i++) { 
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $numRow, $resultData[$i]->transaction_id);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $numRow, $resultData[$i]->uid);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $numRow, $resultData[$i]->username);
                $objPHPExcel->getActiveSheet()->setCellValue('D' . $numRow, $resultData[$i]->order_amount);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $numRow, $resultData[$i]->order_vnd);
                $objPHPExcel->getActiveSheet()->setCellValue('F' . $numRow, $resultData[$i]->payload);
                $objPHPExcel->getActiveSheet()->setCellValue('G' . $numRow, $resultData[$i]->agent);
                $objPHPExcel->getActiveSheet()->setCellValue('H' . $numRow, $resultData[$i]->server_id);
                $objPHPExcel->getActiveSheet()->setCellValue('I' . $numRow, $resultData[$i]->os);
                $pay_message = ($resultData[$i]->status==1)?'Thành công':'Thất bại';
                $objPHPExcel->getActiveSheet()->setCellValue('J' . $numRow, $pay_message);
                $objPHPExcel->getActiveSheet()->setCellValue('K' . $numRow, $resultData[$i]->created_at);
                $numRow++;
            }
            $objPHPExcel->getActiveSheet()->setTitle('Simple');
            // Set active sheet index to the first sheet, so Excel opens this as the first sheet
            $objPHPExcel->setActiveSheetIndex(0);
            // Redirect output to a client’s web browser (Excel2007)
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="excel_'.date('Y-m-d h:i:s').'.xlsx"');
            header('Cache-Control: max-age=0');
            // If you're serving to IE 9, then the following may be needed
            header('Cache-Control: max-age=1');
            // If you're serving to IE over SSL, then the following may be needed
            header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
            header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
            header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
            header ('Pragma: public'); // HTTP/1.0
            $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
            $objWriter->save('php://output');
            exit;
        }
        echo 'Không có dữ liệu';
        exit();
    }
    
 }