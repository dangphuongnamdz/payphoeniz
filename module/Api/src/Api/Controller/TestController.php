<?php
namespace Api\Controller;

use Api\Controller\AbstractRestfulJsonController;
use Zend\View\Model\JsonModel;
//exit(__FILE__);
class TestController extends AbstractRestfulJsonController
{
    public function getList()
    {   // Action used for GET requests without resource Id
        return new JsonModel(
            array('data' =>
                array(
                    array('id'=> 1, 'name' => 'Mothership', 'band' => 'Led Zeppelin'),
                    array('id'=> 2, 'name' => 'Coda',       'band' => 'Led Zeppelin'),
                )
            )
        );
    }

    public function choiAction() {
        $id = $this->params()->fromQuery('bc',1);
        return new JsonModel(array("data" => array($id)));
    }

    public function get($id)
    {   // Action used for GET requests with resource Id
        return new JsonModel(array("data" => array('id'=> 2, 'name' => 'Coda', 'band' => 'Led Zeppelin')));
    }

    public function create($data)
    {   // Action used for POST requests
        return new JsonModel(array('data' => array('id'=> 3, 'name' => 'New Album', 'band' => 'New Band')));
    }

    public function update($id, $data)
    {   // Action used for PUT requests
        return new JsonModel(array('data' => array('id'=> 3, 'name' => 'Updated Album', 'band' => 'Updated Band')));
    }

    public function delete($id)
    {   // Action used for DELETE requests
        return new JsonModel(array('data' => 'album id 3 deleted'));
    }

    /*
    public function get($id)
    {
        $response = $this->getResponseWithHeader()
                         ->setContent( __METHOD__.' get current data with id =  '.$id);
        return $response;
    }
     
    public function getList()
    {
        $response = $this->getResponseWithHeader()
                         ->setContent( __METHOD__.' get the list of data test');
        return $response;
    }
     
    public function create($data)
    {
        $response = $this->getResponseWithHeader()
                         ->setContent( __METHOD__.' create new item of data :
                                                    <b>'.$data['name'].'</b>');
        return $response;
    }
     
    public function update($id, $data)
    {
       $response = $this->getResponseWithHeader()
                        ->setContent(__METHOD__.' update current data with id =  '.$id.
                                            ' with data of name is '.$data['name']) ;
       return $response;
    }
     
    public function delete($id)
    {
        $response = $this->getResponseWithHeader()
                        ->setContent(__METHOD__.' delete current data with id =  '.$id) ;
        return $response;
    }
     
    // configure response
    public function getResponseWithHeader()
    {
        $response = $this->getResponse();
        $response->getHeaders()
                 //make can accessed by *   
                 ->addHeaderLine('Access-Control-Allow-Origin','*')
                 //set allow methods
                 ->addHeaderLine('Access-Control-Allow-Methods','POST PUT DELETE GET');
         
        return $response;
    }*/
}