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
 use Admin\Model\Category;
 use Admin\Model\Savelog;
 use Admin\Form\CategoryForm;       
 use Zend\Validator\File\Size;

 class CategoryController extends AbstractActionController
 {
    protected $categoryTable;
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
    public function getCategoryTable()
    {
        if (!$this->categoryTable) {
            $sm = $this->getServiceLocator();
            $this->categoryTable = $sm->get('Admin\Model\CategoryTable');
        }
        return $this->categoryTable;
    }
	public function getSavelogTable()
    {
        if (!$this->savelogTable) {
            $sm = $this->getServiceLocator();
            $this->savelogTable = $sm->get('Admin\Model\SavelogTable');
        }
        return $this->savelogTable;
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

    public function indexAction()
    {
        $data = array();
        $data = $this->object_to_array($this->getCategoryTable()->fetchAll());
        $arr_tree = array();
        $arr_tmp = array();
        foreach ($data as $item) {
            $parentid = $item['id_parent'];
            $id = $item['id'];
        
            if ($parentid  == 0)
            {
                $arr_tree[$id] = $item;
                $arr_tmp[$id] = &$arr_tree[$id];
            }
            else 
            {
                if (!empty($arr_tmp[$parentid])) 
                {
                    $arr_tmp[$parentid]['children'][$id] = $item;
                    $arr_tmp[$id] = &$arr_tmp[$parentid]['children'][$id];
                }
            }
        }
        unset($arr_tmp);
        return new ViewModel(array(
            'categorys' => $arr_tree,
        ));
    }
    
    public function addAction()
    {		
	
        $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
        $form = new CategoryForm ($dbAdapter);
        $form->get('submit')->setValue('Thêm');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $category = new Category();
            $form->setInputFilter($category->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $category->exchangeArray($form->getData());
                $this->getCategoryTable()->saveCategory($category);
				$ip = $request->getServer('REMOTE_ADDR'); 
				$this->getSavelogTable()->saveLogAction($this->username, 'Add '.$category->tendanhmuc,'AddCategory',$ip);
                return $this->redirect()->toRoute('admincategory');
            }
        }
        return array('form' => $form);
    }

     public function editAction()
     {
         $id = (int) $this->params()->fromRoute('id', 0);
         $newName="";
         if (!$id) {
             return $this->redirect()->toRoute('admincategory', array(
                 'action' => 'add'
             ));
         }
         try {
             $category = $this->getCategoryTable()->getCategory($id);
         }
         catch (\Exception $ex) {
             return $this->redirect()->toRoute('admincategory', array(
                 'action' => 'index'
             ));
         }
         $dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
         $form = new CategoryForm ($dbAdapter);
         $form->bind($category);
         $form->get('submit')->setAttribute('value', 'Chỉnh sửa');

         $request = $this->getRequest();
         if ($request->isPost()) {
             $form->setInputFilter($category->getInputFilter());
             $form->setData($request->getPost());
             if ($form->isValid()) {
                 $this->getCategoryTable()->saveCategory($category);
				 $ip = $request->getServer('REMOTE_ADDR'); 
				$this->getSavelogTable()->saveLogAction($this->username, 'Edit '.$category->tendanhmuc,'EditCategory',$ip);
                 return $this->redirect()->toRoute('admincategory');
             }
         }
         return array(
             'id' => $id,
             'form' => $form,
         );
     }

     public function deleteAction()
     {
         $id = (int) $this->params()->fromRoute('id', 0);
         if (!$id) {
             return $this->redirect()->toRoute('admincategory');
         }

         $request = $this->getRequest();
         if ($request->isPost()) {
             $del = $request->getPost('del', 'No');

             if ($del == 'Yes') {
                 $id = (int) $request->getPost('id');
                 $this->getCategoryTable()->deleteCategory($id);
				 $ip = $request->getServer('REMOTE_ADDR'); 
				$this->getSavelogTable()->saveLogAction($this->username, 'Delete '.$category->tendanhmuc,'DeleteCategory',$ip);
             }
             return $this->redirect()->toRoute('admincategory');
         }

         return array(
             'id'    => $id,
             'category' => $this->getCategoryTable()->getCategory($id)
         );
     }
 }