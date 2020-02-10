<?php
namespace Api\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\Http\Response;
use Api\HttpRestJson\Client as HttpRestJsonClient;

class AbstractRestfulJsonController extends AbstractRestfulController
{
    protected $httpRestJsonClient;
    protected function methodNotAllowed()
    {
        $this->response->setStatusCode(405);
        throw new \Exception('Do not permission access');
    }

    /**
     * [getHttpRestJsonClient description]
     * @return [type] [description]
     */
    protected function getHttpRestJsonClient(){
        if (!$this->httpRestJsonClient) {
            $this->httpRestJsonClient = new HttpRestJsonClient();
        }
        return $this->httpRestJsonClient;
    }
    

    /**
     * save log
     * @param  [type] $string [description]
     * @param  string $type   [description]
     * @return [type]         [description]
     */
     public function saveLogFile($agent = "none", $string, $type=''){
        $message = $string;
        $format = '%message%';
        $nameLog = date('Y_m_d').".txt";
        $formatter = new \Zend\Log\Formatter\Simple($format);
        if($type!=''){
            if (!file_exists(dirname(__DIR__).'/../../../../public/logs/'.$agent.'/iap/error')) {
                mkdir(dirname(__DIR__).'/../../../../public/logs/'.$agent.'/iap/error', 0777, true);
            }
            $writer = new \Zend\Log\Writer\Stream(dirname(__DIR__).'/../../../../public/logs/'.$agent.'/iap/error/'.$nameLog); 
        }else{
            // if (!file_exists(dirname(__DIR__).'/../../../../public/logs/'.$agent.'/iap')) {
            //     mkdir(dirname(__DIR__).'/../../../../public/logs/'.$agent.'/iap', 0777, true);
            // }
            $writer = new \Zend\Log\Writer\Stream(dirname(__DIR__).'/../../../../public/logs/'.$agent.'/iap/'.$nameLog);  
        }  
        $formatter = new \Zend\Log\Formatter\Simple('%message%');
        $writer->setFormatter($formatter);
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($message);
     }

     # Override default actions as they do not return valid JsonModels
    public function exchangeAction()
    {
        return $this->methodNotAllowed();
    }
}