<?php
namespace App\Controller\Api;

use App\Controller\Api\AppController;
use Cake\Event\Event;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Mailer\Email;
use Cake\Routing\Router;
use Cake\Validation\Validation;

class MenuItemsController extends AppController{
	public $userprofileTbl;
	public $menucategories;
	
	public function initialize() {
        parent::initialize();  
        $this->loadComponent('RequestHandler');
		$this->loadComponent('ImageUpload');
        $this->userprofileTbl = TableRegistry::get('UserProfiles');
		$this->menucategories = TableRegistry::get('MenuCategories');
    }

	 /**
     * Get list of all categories.
     * @access public
     * @param int $restaurant_id
     * @return json object
     */
    public function getCategoryListing() {
		if ($this->request->is('POST')) {
        $postData = array();
        $postData = $this->request->getData();
        extract($postData);
        $restaurant_id = isset($restaurant_id) ? $restaurant_id : '';
        if (empty($restaurant_id)) {
          return $this->_returnJson(false, 'Please enter restaurant id.');
        }
        $menucategories =  $this->menucategories->find()
        ->where(['MenuCategories.rest_id' => $restaurant_id])
        ->order(['MenuCategories.id' => 'DESC']);
        if(!$menucategories->isEmpty()){
			$menucategories = $menucategories->toArray();
            $categoryData = array();
            foreach ($menucategories as $category) {
                $temp = array();
                $temp['id'] = $category['id'];
                $temp['categoryName'] = $category['title'];
                $categoryData[] = $temp;
              }
              return $this->_returnJson(true, 'Category List', $categoryData);
            } else {
              return $this->_returnJson(false, 'No records found.');
            }
          }else{
            return $this->_returnJson(false, 'Invalid Request.');
          }
        }
		
	 /**
     * addCategory List Method.
     * @access public
     * @param int $restaurant_id
     * @return json object
     */
    public function addCategoryList() {
		if ($this->request->is('POST')) {
          $postData = array();
          $postData = $this->request->getData();
          extract($postData);
          $restaurant_id = isset($restaurant_id) ? $restaurant_id : '';
          $title = isset($title) ? $title : '';
          if (empty($restaurant_id)) {
            return $this->_returnJson(false, 'Please enter restaurant id.');
          }
          if (empty($title)) {
            return $this->_returnJson(false, 'Please enter category title.');
          }
        $newData = [];
        $newData['rest_id'] = $restaurant_id;
        $newData['title'] = $title;
        $menucategories = $this->menucategories->newEntity();
        $menucategoriesData = $this->menucategories->patchEntity($menucategories, $newData);
        if ($this->menucategories->save($menucategoriesData)) {
          return $this->_returnJson(true, 'You\'ve added menu item category successfully.');
        }
      }else{
        return $this->_returnJson(false, 'Invalid Request.');
      }
    }
	/**
	* updateCategory List Method.
	*
	* @access public
	*
	* @return json object
	*/
    public function updateCategory() {
	  $current_user_id = $this->currentUser['user']['id'];
      if ($this->request->is('POST')) {
          $postData = array();
          $postData = $this->request->getData();
          extract($postData);
          $categoryId = isset($categoryId) ? $categoryId : '';
           $title = isset($title) ? $title : '';
          if (empty($categoryId)) {
            return $this->_returnJson(false, 'Please enter category id.');
          }
          if (empty($title)) {
            return $this->_returnJson(false, 'Please enter category title.');
          }
          $exists = $this->menucategories->exists(['id' => $categoryId]);
           if($exists){
          $categoryIdData = $this->menucategories->get($categoryId);
          $data['title'] = $title;
          $menucategoriesData = $this->menucategories->patchEntity($categoryIdData, $data);
          if ($this->menucategories->save($menucategoriesData)) {
            return $this->_returnJson(true, 'Category Updated Successfully.');
          }
         }else{return $this->_returnJson(false, 'No records found.');} 
        }else{
          return $this->_returnJson(false, 'Invalid Request.');}
        }
	/**
	* deleteCategory item Method.
	*
	* @access public
	*
	* @return json object
	*/
      public function deleteCategory() {
        $current_user_id = $this->currentUser['user']['id'];
        if ($this->request->is('post')) {
          $postData = array(); 
          $postData = $this->request->getData();
          extract($postData);
          $categoryId = isset($categoryId) ? $categoryId : '';
          if (empty($categoryId)) {
            return $this->_returnJson(false, 'Please enter category id.');
          }
          //check exist
          $exists = $this->menucategories->exists(['id' => $categoryId]);
          if($exists){
          //delete category items table
            $entity = $this->menucategories->get($categoryId);
            $result = $this->menucategories->delete($entity);
            return $this->_returnJson(true, 'Category Deleted Successfully.');
          }else{return $this->_returnJson(false, 'No records found.');}
        }else{return $this->_returnJson(false, 'Invalid Request.');}
      }

     /**
     * Menu Item Method.
     *
     * @access public
     * @param string $item_name
	 * @param enum $menu_choice
	 * @param text $description
	 * @param string $size
	 * @return json object
     */
	 
	 public function addMenuItem() {
     	$current_user_id = $this->currentUser['user']['id'];
		if ($this->request->is('post')) {
     	 	$postData = array();
            $postData = $this->request->getData();
            extract($postData);
            $item_name = isset($item_name) ? $item_name : '';
            $category_id = isset($category_id) ? $category_id : '';
			$menu_choice = isset($menu_choice) ? $menu_choice : '';
            $description = isset($description) ? $description : '';
			$size = isset($size) ? $size : '';
			//$optionsArr = isset($optionsArr) ? $optionsArr : '';
            if (empty($item_name)) {
                return $this->_returnJson(false, 'Please enter menu item name.');
            }
            if (empty($category_id)) {
                return $this->_returnJson(false, 'Please enter menu category.');
            }
            if (empty($menu_choice)) {
                return $this->_returnJson(false, 'Please enter menu choice.');
            }
            if (empty($description)) {
                return $this->_returnJson(false, 'Please enter menu description.');
            }
            //check file and save
			if(!empty($postData['menuImage'])){
              $path = WWW_ROOT . '' . 'img/menu_item_uploads/';
              //thumbnailpath
              $thumbpath = WWW_ROOT . '' . 'img/menu_item_uploads/thumbnail/';
			  //create the image component
              $upload = $this->ImageUpload->uploadImage($postData['menuImage'], $path, $thumbpath);
              if($upload['status']){
                $file_name = $upload['imageName'];
              }else{
                return $this->_returnJson(false, $upload['message']);  
              }
            }
				//save data
				$optionsArr = json_decode($optionsArr, true);
        			$newData = [];
        			$newData['user_id'] = $current_user_id;
        			$newData['category_id'] = $category_id;
        			$newData['item_name'] = $item_name;
					$newData['menu_choice'] = $menu_choice;
        			$newData['description'] = $description;
              if(!empty($postData['menuImage'])){
                $newData['item_image'] = $file_name;
              }
			  //save sizes as it is array of json
			  $newData['menu_sizes']= $size;
			  $newData['menu_options']= $optionsArr;
			  $menuitems = $this->MenuItems->newEntity();
			  //$menuData = $this->MenuItems->patchEntity($menuitems, $newData);
			  $menuData = $this->MenuItems->patchEntity($menuitems, $newData, ['associated' => ['MenuOptions']]);
			 if ($this->MenuItems->save($menuData)) {
             return $this->_returnJson(true, 'You\'ve added menu item successfully.');
            }
          }else{return $this->_returnJson(false, 'Invalid Request.');}
        }
		
      /**
      * edit menu item Method.
      * @access public
      * @param int $menu_id
	  * @param string $item_name
	  * @param int $category_id
	  * @param enum $menu_choice
	  * @param text $description
	  * @param string $size
      * @return json object
      */
      public function editMenuItem() {
        $current_user_id = $this->currentUser['user']['id'];
          if ($this->request->is('post')) {
            $postData = array();
            $postData = $this->request->getData();
            extract($postData);
            $menu_id = isset($menu_id) ? $menu_id : '';
            $item_name = isset($item_name) ? $item_name : '';
            $category_id = isset($category_id) ? $category_id : '';
			$menu_choice = isset($menu_choice) ? $menu_choice : '';
            $description = isset($description) ? $description : '';
			$size = isset($size) ? $size : '';
            if (empty($menu_id)) {
                return $this->_returnJson(false, 'Please enter menu item id.');
            }
            if (empty($item_name)) {
                return $this->_returnJson(false, 'Please enter menu item name.');
            }
            if (empty($category_id)) {
                return $this->_returnJson(false, 'Please enter menu category.');
            }
            if (empty($menu_choice)) {
                return $this->_returnJson(false, 'Please enter menu choice.');
            }
            if (empty($description)) {
                return $this->_returnJson(false, 'Please enter menu description.');
            }
            $menuData = $this->MenuItems->find()->where(['id' => $menu_id])->first();
            //check file and save
			 if(!empty($postData['menuImage'])){
				$path = WWW_ROOT . '' . 'img/menu_item_uploads/';
              //thumbnailpath
              $thumbpath = WWW_ROOT . '' . 'img/menu_item_uploads/thumbnail/';
			  //create the image component
              $upload = $this->ImageUpload->uploadImage($postData['menuImage'], $path, $thumbpath);
			  if($upload['status']){
                $file_name = $upload['imageName'];
                if(!empty($menuData['item_image'])){
                //unlink file
                 @unlink($path.$menuData['item_image']);
                  @unlink($thumbpath.$menuData['item_image']);
                }
              }else{
                return $this->_returnJson(false, $upload['message']);  
              }
            }
            $optionsArr = json_decode($optionsArr, true);
			if(!empty($menuData)){
            $menuData = $menuData->toArray();
			$newData['category_id'] = $category_id;
            $newData['item_name'] = $item_name;
            $newData['menu_choice'] = $menu_choice;
            $newData['description'] = $description;
            $newData['menu_sizes']= $size;
			$newData['menu_options']= $optionsArr;
            if(!empty($postData['menuImage'])){
              $newData['item_image']= $file_name;
            }
			//update patch entity
            $data = $this->MenuItems->get($menuData['id'],[
              'contain' => ['MenuOptions']
            ]);
			$data = $this->MenuItems->patchEntity($data, $newData, ['associated' => ['MenuOptions']]);
            if($this->MenuItems->save($data)){return $this->_returnJson(true, 'Update Menu Item Successfully.');}
          }else{ return $this->_returnJson(false, 'No records found.');}
            //upadte data
        }else{return $this->_returnJson(false, 'Invalid Request.');}
      }

      /**
      * view menu item Method.
      * @access public
      * @param type $menu_id
      * @return json object
      */
       public function viewMenuData() {
		   $current_user_id = $this->currentUser['user']['id'];
          if ($this->request->is('post')) {
             $postData = array(); 
            $postData = $this->request->getData();
            extract($postData);
            $menu_id = isset($menu_id) ? $menu_id : '';
            if (empty($menu_id)) {
                return $this->_returnJson(false, 'Please enter menu id.');
            }
			$newData = [];
			//check for currency
          $curencyData = $this->userprofileTbl->find()->select(['user_id', 'currency'])->where(['user_id' => $current_user_id])->first();
          if(!empty($curencyData)){
            $curencyData = $curencyData->toArray();
            $currency  = $curencyData['currency'];
            //call checkCurrency function
            $codekey = $this->checkCurrency($currency);
          }
            $menuData = $this->MenuItems->find()->select(['MenuItems.id', 'MenuItems.category_id', 'MenuCategories.title', 'MenuItems.item_name', 'MenuItems.menu_choice', 'MenuItems.description', 'MenuItems.item_image', 'MenuItems.menu_sizes'])->contain(['MenuCategories', 'MenuOptions'])->where(['MenuItems.id' => $menu_id])->first();
            
            if(!empty($menuData)){
              $menuData = $menuData->toArray();
              $newData['menuId'] = $menuData['id'];
              $newData['category_id'] = $menuData['category_id'];
              $newData['category_name'] = $menuData['menu_category']['title'];
              $newData['item_name'] = $menuData['item_name'];
              $newData['currency'] = $codekey;
              $newData['menu_choice'] = $menuData['menu_choice'];
              $newData['description'] = $menuData['description'];
              if(!empty($menuData['item_image'])){
                $newData['image'] = base_url . 'img/' . 'menu_item_uploads/' . $menuData['item_image'];
                $newData['thumbimage'] = base_url . 'img/' . 'menu_item_uploads/thumbnail/' . $menuData['item_image'];
              }else{
                $newData['image'] = "";
                $newData['thumbimage'] = "";
              }
			  $newData['sizes'] = json_decode($menuData['menu_sizes'], true);
			  $newData['custom_menu'] = $menuData['menu_options'];
			  return $this->_returnJson(true, 'View Menu Item Information Successfully.', $newData);
            }else{
              return $this->_returnJson(false, 'No records found.');
            }
          }else{
           return $this->_returnJson(false, 'Invalid Request.');
         }
       }

        /**
        * get menu item list Method.
        * @access public
        * @param type $currentUser
        * @return json object
        */
      public function getMenuItemList() {
      $current_user_id = $this->currentUser['user']['id'];
      $menuData = $this->MenuItems->find()->select(['MenuItems.id', 'MenuItems.category_id', 'MenuCategories.title', 'MenuItems.item_name', 'MenuItems.menu_choice', 'MenuItems.description', 'MenuItems.item_image', 'MenuItems.menu_sizes'])->contain(['MenuCategories', 'MenuOptions'])->where(['MenuItems.user_id' => $current_user_id])->order(['MenuItems.id' => 'DESC']);
	  //check for currency
      $curencyData = $this->userprofileTbl->find()->select(['user_id', 'currency'])->where(['user_id' => $current_user_id])->first();
      if(!empty($curencyData)){
        $curencyData = $curencyData->toArray();
        $currency  = $curencyData['currency'];
        //call function
        $codekey = $this->checkCurrency($currency);
      }
	  //check for menu
       if(!$menuData->isEmpty()){
          $menuData = $menuData->toArray();
          $newArr = array();
          foreach ($menuData as $menuVal) {
                $temp = array();
                $temp['menu_id'] = $menuVal['id'];
                $temp['category_id'] = $menuVal['category_id'];
				$temp['category_name'] = $menuVal['menu_category']['title'];
                $temp['item_name'] = $menuVal['item_name'];
				$temp['menu_choice'] = $menuVal['menu_choice'];
                $temp['description'] = $menuVal['description'];
				$temp['currency'] = $codekey;
				$temp['size'] = "";
				$temp['selected_size_price'] = "";
				$temp['calculated_item_price'] = "";
                if(!empty($menuVal['item_image'])){
                $temp['image'] = base_url . 'img/' . 'menu_item_uploads/' . $menuVal['item_image'];
                $temp['thumbimage'] = base_url . 'img/' . 'menu_item_uploads/thumbnail/' . $menuVal['item_image'];
              }else{
                $temp['image'] = "";
                $temp['thumbimage'] = ""; 
              }
			  $sizesList = json_decode($menuVal['menu_sizes'], true);
			  $temp['sizes'] = $sizesList;
                //$newArr[] = $temp;
				$optionArr = [];
                 if(!empty($menuVal['menu_options'])){
                   foreach ($menuVal['menu_options'] as $key => $menuVal) {
                     $option['id'] = $menuVal['id'];
                     $option['menu_option'] = $menuVal['menu_option'];
                     $option['menu_option_price'] = $menuVal['menu_option_price'];
                     $optionArr[] = $option;
                   }
                   $temp['custom_menu'] = $optionArr;
                 }
                 $newArr[] = $temp;
            }
              return $this->_returnJson(true, 'Menu List', $newArr);
            }else{return $this->_returnJson(false, 'No records found.');}
        }
		
		/**
		* delete item Method.
		* @access public
		* @param type $menu_id
		* @return json object
		*/
      public function deleteMenuItem() {
		$current_user_id = $this->currentUser['user']['id'];
        if ($this->request->is('post')) {
          $postData = array(); 
          $postData = $this->request->getData();
          extract($postData);
          $menu_id = isset($menu_id) ? $menu_id : '';
          if (empty($menu_id)) {
            return $this->_returnJson(false, 'Please enter menu id.');
          }
          //check exist
          $exists = $this->MenuItems->exists(['id' => $menu_id]);
          if($exists){
          //delete menu items table first
          $entity = $this->MenuItems->get($menu_id);
          $result = $this->MenuItems->delete($entity);
          //delete menu sizes table second
          return $this->_returnJson(true, 'You have been successfully deleted the Menu Item.');
         }else{return $this->_returnJson(false, 'No records found.');}
        }else{return $this->_returnJson(false, 'Invalid Request.');}
      }
    }