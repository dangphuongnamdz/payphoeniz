<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Admin\Controller;

 use Zend\Mvc\Controller\AbstractActionController;
 use Zend\View\Model\ViewModel;
 use Zend\Mvc\MvcEvent;
 use Admin\Model\Posts;         
 use Admin\Form\PostsForm; 
  use Admin\Model\Savelog;

 class PostsController extends AbstractActionController
 {
    protected $postsTable;
    protected $savelogTable;
	protected $username;
	public function getAuthService()
	{
		$this->authservice = $this->getServiceLocator()->get('AuthService'); 
		return $this->authservice;  
	}
	 
    public function onDispatch(MvcEvent $e)
    {
        if (! $this->getServiceLocator()->get('AuthService')->hasIdentity()){
            return $this->redirect()->toRoute('login');
        }
		$this->username = $this->getAuthService()->getStorage()->read();
        return parent::onDispatch($e);
    }
	public function getSavelogTable()
    {
        if (!$this->savelogTable) {
            $sm = $this->getServiceLocator();
            $this->savelogTable = $sm->get('Admin\Model\SavelogTable');
        }
        return $this->savelogTable;
    }
    public function getPostsTable()
    {
        if (!$this->postsTable) {
            $sm = $this->getServiceLocator();
            $this->postsTable = $sm->get('Admin\Model\PostsTable');
        }
        return $this->postsTable;
    }
    
    public function indexAction()
    {
        return new ViewModel(array(
            'posts' => $this->getPostsTable()->fetchAll()
        ));
    }

    public function addAction()
    {
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new PostsForm($dbAdapter); 
        $form->get('submit')->setValue('Thêm');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $posts = new Posts();
            $form->setInputFilter($posts->getInputFilter());
            
            $nonFile = $request->getPost()->toArray();
            $nonFiles = $request->getPost()->toArray();
            $File    = $this->params()->fromFiles('avatar');
            $Files    = $this->params()->fromFiles('avatar_mobile');
            $data = array_merge(
                 $nonFile, //POST 
                 array('avatar'=> $File['name']) //FILE...
             );
             $datas = array_merge(
                $nonFiles, //POST 
                array('avatar_mobile'=> $File['name']) //FILE...
            );
             $newName = "";
             $newNames = "";
             if($File['name']!=''){
                $adapter = new \Zend\File\Transfer\Adapter\Http(); 
                $adapter->setDestination(dirname(__DIR__).'/../../../../public/img/assets');
                $destination = dirname(__DIR__).'/../../../../public/img/assets';

                $ext = pathinfo($File['name'], PATHINFO_EXTENSION);
                $newName = md5(rand(). $File['name']) . '.' . $ext;
                $adapter->addFilter('File\Rename', array( 
                    'target' => $destination . '/' . $newName,
                ));

                if ($adapter->receive($File['name'])) {
                //success
                }
             }
             if($Files['name']!=''){
                $adapters = new \Zend\File\Transfer\Adapter\Http(); 
                $adapters->setDestination(dirname(__DIR__).'/../../../../public/img/assets');
                $destinations = dirname(__DIR__).'/../../../../public/img/assets';

                $exts = pathinfo($Files['name'], PATHINFO_EXTENSION);
                $newNames = md5(rand(). $Files['name']) . '.' . $exts;
                $adapters->addFilter('File\Rename', array(
                    'target' => $destinations . '/' . $newNames,
                ));

                if ($adapters->receive($Files['name'])) {
                //success
                }
             }
             $form->setData($request->getPost());
             
             if ($form->isValid()) {
                 $posts->exchangeArray($form->getData());
                 $this->getPostsTable()->savePosts($posts, $newName, $newNames);
				 $ip = $request->getServer('REMOTE_ADDR'); 
				 $this->getSavelogTable()->saveLogAction($this->username, 'Add '.$post->slug,'AddPost',$ip);
                 return $this->redirect()->toRoute('adminpost');
             }
        }
        return array('form' => $form);
    }


    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('adminpost', array(
                'action' => 'add'
            ));
        }
        try {
            $posts = $this->getPostsTable()->getPosts($id);
        }
        catch (\Exception $ex) {
            return $this->redirect()->toRoute('adminpost', array(
                'action' => 'index'
            ));
        }

        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new PostsForm($dbAdapter);
        $form->bind($posts);
        $form->get('submit')->setAttribute('value', 'Chỉnh sửa');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($posts->getInputFilter());
           
            $nonFile = $request->getPost()->toArray();
            $nonFiles = $request->getPost()->toArray();
            $File    = $this->params()->fromFiles('avatar');
            $Files    = $this->params()->fromFiles('avatar_mobile');
            $data = array_merge(
                 $nonFile, //POST 
                 array('avatar'=> $File['name']) //FILE...
             );
             $datas = array_merge(
                $nonFiles, //POST 
                array('avatar_mobile'=> $Files['name']) //FILE...
            );
             $newName = "";
             $newNames = "";
            if($File['name']!=''){
                $adapter = new \Zend\File\Transfer\Adapter\Http(); 
                if($posts->avatar!=null && $posts->avatar_mobile != 'no-image.png'){
                    if (file_exists(dirname(__DIR__).'/../../../../public/img/assets/'.$posts->avatar))  
                        unlink(dirname(__DIR__).'/../../../../public/img/assets/'.$posts->avatar);
                }
                $adapter->setDestination(dirname(__DIR__).'/../../../../public/img/assets');
                $destination = dirname(__DIR__).'/../../../../public/img/assets';

                $ext = pathinfo($File['name'], PATHINFO_EXTENSION);
                $newName = md5(rand(). $File['name']) . '.' . $ext;
                $adapter->addFilter('File\Rename', array(
                    'target' => $destination . '/' . $newName,
                ));

                if ($adapter->receive($File['name'])) {
                //success
                }
            }
            if($Files['name']!=''){
                $adapters = new \Zend\File\Transfer\Adapter\Http(); 
                if($posts->avatar_mobile!=null && $posts->avatar_mobile != 'no-image.png'){
                    if (file_exists(dirname(__DIR__).'/../../../../public/img/assets/'.$posts->avatar_mobile))  
                        unlink(dirname(__DIR__).'/../../../../public/img/assets/'.$posts->avatar_mobile);
                }
                $adapters->setDestination(dirname(__DIR__).'/../../../../public/img/assets');
                $destinations = dirname(__DIR__).'/../../../../public/img/assets';

                $exts = pathinfo($Files['name'], PATHINFO_EXTENSION);
                $newNames = md5(rand(). $Files['name']) . '.' . $exts;
                $adapters->addFilter('File\Rename', array(
                    'target' => $destinations . '/' . $newNames,
                ));

                if ($adapters->receive($Files['name'])) {
                //success
                }
            }
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                $this->getPostsTable()->savePosts($posts, $newName, $newNames);
				$ip = $request->getServer('REMOTE_ADDR'); 
				$this->getSavelogTable()->saveLogAction($this->username, 'Edit '.$post->slug,'EditPost',$ip);
                return $this->redirect()->toRoute('adminpost');
            }
        }

        return array(
            'id' => $id,
            'avatar' => $posts->avatar,
            'avatar_mobile' => $posts->avatar_mobile,
            'form' => $form,
        );
    }

     public function deleteAction()
     {
         $id = (int) $this->params()->fromRoute('id', 0);
         if (!$id) {
             return $this->redirect()->toRoute('adminpost');
         }

         $request = $this->getRequest();
         if ($request->isPost()) {
             $del = $request->getPost('del', 'No');

             if ($del == 'Yes') {
                 $id = (int) $request->getPost('id');
				 $slug = (int) $request->getPost('slug');
                 $posts = $this->getPostsTable()->getPosts($id);
                 if($posts->avatar!=null && $posts->avatar_mobile != 'no-image.png'){
                    if (file_exists(dirname(__DIR__).'/../../../../public/img/assets/'.$posts->avatar))  
                        unlink(dirname(__DIR__).'/../../../../public/img/assets/'.$posts->avatar);
                 }
                 if($posts->avatar_mobile!=null && $posts->avatar_mobile != 'no-image.png'){
                    if (file_exists(dirname(__DIR__).'/../../../../public/img/assets/'.$posts->avatar_mobile))  
                        unlink(dirname(__DIR__).'/../../../../public/img/assets/'.$posts->avatar_mobile);
                 }
                 $this->getPostsTable()->deletePosts($id);
				 $ip = $request->getServer('REMOTE_ADDR'); 
				$this->getSavelogTable()->saveLogAction($this->username, 'Delete '.$slug,'DeletePost',$ip);
             }

             return $this->redirect()->toRoute('adminpost');
         }

         return array(
             'id'    => $id,
             'posts' => $this->getPostsTable()->getPosts($id)
         );
     }

     public function sortAction() {
        $request = $this->getRequest();
        $response = $this->getResponse();
        if ($request->isPost()) {
            $post_data = $request->getPost();
            $desired_position = $post_data['desired_position'];
            $current_position = $post_data['current_position'];
            $id_current_position = $post_data['id_current_position'];
            $result = $this->getPostsTable()->saveSort($desired_position, $current_position, $id_current_position);
            if ($result==true)
                $response->setContent(\Zend\Json\Json::encode(array('response' => true)));
            else {
                $response->setContent(\Zend\Json\Json::encode(array('response' => false)));
            }
        }
        return $response;
    }

 }