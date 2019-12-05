<?php
namespace App\Controller\Api;

use App\Controller\Api\AppController;
use Cake\Event\Event;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Mailer\Email;
use Cake\Routing\Router;
use Cake\Validation\Validation;

class OrdersController extends AppController
{
	 public $usersTbl;
	 public $userprofilesTbl;
	 public $usersPaymentObj;

	 public function initialize() {
        parent::initialize();  
        $this->loadComponent('RequestHandler');
        $this->loadComponent('Notification');
        $this->usersTbl = TableRegistry::get('Users');
        $this->userprofilesTbl = TableRegistry::get('UserProfiles');
		$this->usersPaymentObj = TableRegistry::get('UserPayments');
    }

    /**
    * New Orders Method
    * @access public
	* @param type currentUser
    * @return json object
    */
      public function neworders() {
		$current_user = $this->currentUser;
     	$current_user_id = $this->currentUser['user']['id'];
		if ($this->request->is('post')) {
            $postData = array();
            $postData = $this->request->getData();
            extract($postData);
            $page   = (isset($page) && $page > 0) ? $page : 1;
            $limit = 10;
            $offset = (--$page) * $limit;
     	 //restaurant name
     	 $restname = $this->usersTbl->find()->select(['Users.id', 'Users.firstname', 'Users.lastname', 'UserProfiles.currency'])->contain(['UserProfiles'])->where(['Users.id' => $current_user_id])->first()
		 ->toArray();
		 //orders data
         $Orderdata = $this->Orders->find()->contain(['Users' => ['UserProfiles'], 'Carts' => ['CartItems' => ['MenuItems']]
            ])->where(['Orders.to_id' => $current_user_id, 'Orders.order_status' => 0])
         ->order(['Orders.id' => 'DESC'])
		 ->offset($offset)->limit($limit);
         $orderData = [];
         if(!$Orderdata->isEmpty()){
			 $Orderdata = $Orderdata->toArray();
            foreach($Orderdata as $key => $orderVal){
                $orderData[$key] = array(
                    'order_id' => $orderVal['id'],
					'rest_name' => $restname['firstname'] .' '. $restname['lastname'],
					'rest_id' => $restname['id'],
					'user_name' => $orderVal['user']['username'],
					'user_email' => $orderVal['user']['email'],
					'user_phone_number' => $orderVal['user']['user_profile']['phone_number'],
					'order_date' => $orderVal['order_date'],
					'total_amount' => $orderVal['amount'],
					'order_number' => $orderVal['order_number'],
					'currencyCode' => $this->checkCurrency($restname['user_profile']['currency']),
					'total_items' => count($orderVal['cart']['cart_items'])
                    );
                $cart_items = [];
                if(!empty($orderVal['cart'])){
					foreach($orderVal['cart']['cart_items'] as $cart_itemsVal){
						$orderData[$key]['cart_items'][] =  array(
							'menu_id' => $cart_itemsVal['menu_id'],
							'item_name' => $cart_itemsVal['menu_item']['item_name'],
							'size' => $cart_itemsVal['menu_size'],
							'selected_size_price' => $cart_itemsVal['single_item_price'],
							'item_price' => $this->checkCurrency($restname['user_profile']['currency']).$cart_itemsVal['final_item_price'],
							'quantity_in_cart' => $cart_itemsVal['quantity_in_cart'],
							'calculated_item_price' => $cart_itemsVal['final_item_price'],
							'custom_menu' => json_decode($cart_itemsVal['menu_options'], true)
							);
					}
            }
        }
            $finalArray['orders'] = $orderData;
				return $this->_returnJson(true, 'New Order Data List.', $finalArray);
			}else{return $this->_returnJson(false, 'No records found.');}
			}else{return $this->_returnJson(false, 'Invalid Request.');}
		}

	/**
	* order Accept Reject Method
	* @access public
	* @param type currentUser
	* @return json object
	*/
      public function orderAcceptReject() {
		  $current_user = $this->currentUser;
		  $current_user_id = $this->currentUser['user']['id']; //accept/reject
	   if ($this->request->is('post')) {
            $postData = array(); 
            $postData = $this->request->getData();
            extract($postData);
            $orderId = isset($orderId) ? $orderId : '';
            $orderStatus = isset($orderStatus) ? $orderStatus : '';
            //$reason = isset($reason) ? $reason : '';
			$processTime = isset($processTime) ? $processTime : '';
             if(empty($orderId)){
                return $this->_returnJson(false, 'Please enter order Id');
             }
             if(empty($orderStatus)){
                return $this->_returnJson(false, 'Please enter order status');
             }
             /*if($orderStatus == 2){
                if(empty($reason)){
                    return $this->_returnJson(false, 'Please enter reason');
                }
             }*/
             //this query notification sent and save
            $ordersTblData = $this->Orders->find()->select(['Orders.id', 'Orders.user_id', 'Orders.to_id', 'Users.id', 'Users.user_type'])->contain(['Users' => ['UserTokens' => 
                    ['fields' => 
                        ['UserTokens.user_id', 'UserTokens.device_token', 'UserTokens.access_token']
                    ]
                ]
            ])->where(["Orders.id" => $orderId, "Orders.to_id" => $current_user_id])->first();
			//check table not empty
             if(!empty($ordersTblData)){
               $ordersTblData = $ordersTblData->toArray();
			   foreach ($ordersTblData['user']['user_tokens'] as $key => $Tokenval) {
                    //check device token not empty
                    if(!empty($Tokenval['device_token']) && !empty($Tokenval['access_token'])){
                        $deviceToken[] = $Tokenval['device_token'];
                    }
                }
            }
            //update the order table 
            $this->Orders->updateAll(array("order_status" => $orderStatus), array("id" => $orderId, "to_id" => $current_user_id));
            if($orderStatus == 1){
            //check notifications
                if(!empty($deviceToken)){
                    $message = "Order Accepted. Pick up in".' '.$processTime.' '."minutes";
                    //function to save notification component
                    $this->Notification->saveNotification($ordersTblData['to_id'], $ordersTblData['user_id'], $ordersTblData['id'], 'Accepted', $message);
                    //function to sent pushnotification
                    $this->Notification->pushNotification($deviceToken, $message, $ordersTblData['user']['user_type']);
                }
                return $this->_returnJson(true, 'Order Accepted Successfully.');
            }
			/*else if($orderStatus == 2){
                //reason reject
                //check notifications
                if(!empty($deviceToken)){
                    $message = "Your Order has Rejected - ".' '.$reason;
                    //function to save notification
                    $this->Notification->saveNotification($ordersTblData['to_id'], $ordersTblData['user_id'], 'Rejected', $message);
                    //function to sent pushnotification
                    $this->Notification->pushNotification($deviceToken, $message, $ordersTblData['user']['user_type']);
                }
                 return $this->_returnJson(true, 'Order Rejected Successfully.');
                }*/
            }else{return $this->_returnJson(false, 'Invalid Request.');}
        }

     /**
     * Accept Orders Method
     * @access public
	 * @param type currentUser
     * @return json object
     */
     public function acceptedorders() {
     	$current_user = $this->currentUser;
     	$current_user_id = $this->currentUser['user']['id'];
		if ($this->request->is('post')) {
            $postData = array();
            $postData = $this->request->getData();
            extract($postData);
            $page   = (isset($page) && $page > 0) ? $page : 1;
            $limit = 10;
            $offset = (--$page) * $limit;
     	 //restaurant name
     	 $restname = $this->usersTbl->find()->select(['Users.id', 'Users.firstname', 'Users.lastname', 'UserProfiles.currency'])->contain(['UserProfiles'])->where(['Users.id' => $current_user_id])->first()
		 ->toArray();
         $Orderdata = $this->Orders->find()->contain(['Users' => ['UserProfiles'], 'Carts' => ['CartItems' => ['MenuItems']]
        ])->where(['Orders.to_id' => $current_user_id, 'Orders.order_status' => 1])->order(['Orders.id' => 'DESC'])
		->offset($offset)->limit($limit);
         $orderData = [];
          if(!$Orderdata->isEmpty()){
			  $Orderdata = $Orderdata->toArray();
            foreach($Orderdata as $key => $orderVal){
                $orderData[$key] = array(
                    'order_id' => $orderVal['id'],
					'rest_id' => $restname['id'],
					'rest_name' => $restname['firstname'] .' '. $restname['lastname'],
					'user_name' => $orderVal['user']['username'],
					'user_email' => $orderVal['user']['email'],
					'user_phone_number' => $orderVal['user']['user_profile']['phone_number'],
					'user_address' => $orderVal['user']['user_profile']['address'],
					'order_date' => $orderVal['order_date'],
					'total_amount' => $orderVal['amount'],
					'currencyCode' => $this->checkCurrency($restname['user_profile']['currency']),
					'total_items' => count($orderVal['cart']['cart_items'])
                    );
                $cart_items = [];
                if(!empty($orderVal['cart'])){
					foreach($orderVal['cart']['cart_items'] as $cart_itemsVal){
						$orderData[$key]['cart_items'][] =  array(
							'menu_id' => $cart_itemsVal['menu_id'],
							'item_name' => $cart_itemsVal['menu_item']['item_name'],
							'selected_size_price' => $cart_itemsVal['single_item_price'],
							'item_price' => $this->checkCurrency($restname['user_profile']['currency']).$cart_itemsVal['final_item_price'],
							'quantity_in_cart' => $cart_itemsVal['quantity_in_cart'],
							'calculated_item_price' => $cart_itemsVal['final_item_price'],
							'size' => $cart_itemsVal['menu_size'],
							'custom_menu' => json_decode($cart_itemsVal['menu_options'], true)
							);
					}
            }
        }
            $finalArray['orders'] = $orderData;
				return $this->_returnJson(true, 'Order Accepted Successfully.', $finalArray);
			}else{return $this->_returnJson(false, 'No records found.');}
			}else{return $this->_returnJson(false, 'Invalid Request.');}
		}

     /**
     * Accept Orders Method
     * @access public
	 * @param type currentUser
     * @return json object
     */
     public function pastorders() {
        $current_user = $this->currentUser;
        $current_user_id = $this->currentUser['user']['id'];
		if ($this->request->is('post')) {
            $postData = array();
            $postData = $this->request->getData();
            extract($postData);
            $page   = (isset($page) && $page > 0) ? $page : 1;
            $limit = 10;
            $offset = (--$page) * $limit;
         //restaurant name
         $restname = $this->usersTbl->find()->select(['Users.id', 'Users.firstname', 'Users.lastname', 'UserProfiles.currency'])->contain(['UserProfiles'])->where(['Users.id' => $current_user_id])->first()
		 ->toArray();
         $Orderdata = $this->Orders->find()->contain(['Users' => ['UserProfiles'], 'Carts' => ['CartItems' => ['MenuItems']]
        ])->where(['Orders.to_id' => $current_user_id, 'Orders.order_status IN (2,3)'])
		->order(['Orders.id' => 'DESC'])
		->offset($offset)->limit($limit);
		$orderData = [];
         if(!$Orderdata->isEmpty()){
			 $Orderdata = $Orderdata->toArray();
            foreach($Orderdata as $key => $orderVal){
                $orderData[$key] = array(
                    'order_id' => $orderVal['id'],
					'rest_id' => $restname['id'],
                    'restaurant_name' => $restname['firstname'] .' '. $restname['lastname'],
					'user_name' => $orderVal['user']['username'],
                    'user_email' => $orderVal['user']['email'],
                    'address' => $orderVal['user']['user_profile']['address'],
                    'order_date' => $orderVal['order_date'],
                    'total_amount' => $orderVal['amount'],
					'order_status' => ($orderVal['order_status'] == 3) ? 'Completed' : 'Rejected',
					'currencyCode' => $this->checkCurrency($restname['user_profile']['currency']),
                    'total_items' => count($orderVal['cart']['cart_items'])
                    );
                $cart_items = [];
                if(!empty($orderVal['cart'])){
					foreach($orderVal['cart']['cart_items'] as $cart_itemsVal){
						$orderData[$key]['cart_items'][] =  array(
							'item_id' => $cart_itemsVal['menu_id'],
							'item_name' => $cart_itemsVal['menu_item']['item_name'],
							'selected_size_price' => $cart_itemsVal['single_item_price'],
							 'item_price' => $this->checkCurrency($restname['user_profile']['currency']).$cart_itemsVal['final_item_price'],
							'quantity_in_cart' => $cart_itemsVal['quantity_in_cart'],
							'size' => $cart_itemsVal['menu_size'],
							'calculated_item_price' => $cart_itemsVal['final_item_price'],
							'custom_menu' => json_decode($cart_itemsVal['menu_options'], true)
							);
					}
            }
        }
            $finalArray['orders'] = $orderData;
            return $this->_returnJson(true, 'Past orders list.', $finalArray);
        }else{return $this->_returnJson(false, 'No records found.');}
		}else{return $this->_returnJson(false, 'Invalid Request.');}
	}

		/**
		* place Order Method
		* @access public
		* @param type currentUser
		* @return json object
		*/
        public function placeOrder() {
			$current_user_id = $this->currentUser['user']['id'];
            if ($this->request->is('post')) {
                $postData = array(); 
                $postData = $this->request->getData();
                extract($postData);
                $user_id = isset($user_id) ? $user_id : '';
                $rest_id = isset($rest_id) ? $rest_id : '';
                $cart_items = isset($cart_items) ? $cart_items : '';
				$total_amount = isset($total_amount) ? $total_amount : '';
				$payment_data = isset($payment_data) ? $payment_data : '';
                if (empty($user_id)) {
                    return $this->_returnJson(false, 'Please enter user id.');
                }
                if (empty($rest_id)) {
                    return $this->_returnJson(false, 'Please enter rest id.');
                }
                if (empty($cart_items)) {
                    return $this->_returnJson(false, 'Please enter cart items.');
                }
                if (empty($total_amount)) {
                    return $this->_returnJson(false, 'Please enter total amount.');
                }
				//generate order number OD114968145484516000
                $order_number = $this->getNextJobNumber();
                $cart_items = json_decode($cart_items, true);
				//payment data
				$payment_data = json_decode($payment_data, true);
                $carts = TableRegistry::get('Carts');
                $cartitems = TableRegistry::get('CartItems');
				//check tokens
				$checkToken = $this->usersTbl->find()->select(['Users.id', 'Users.email', 'Users.user_type'])->contain(['UserTokens' =>
                    ['fields' => [
                            'UserTokens.user_id', 'UserTokens.device_token', 'UserTokens.access_token'
                        ]
                    ]
                ])->where(['Users.id' => $rest_id])->first();
                if(!empty($checkToken)){
					$checkToken = $checkToken->toArray();
					foreach ($checkToken['user_tokens'] as $key => $Tokenval){
						//check device token not empty
						if(!empty($Tokenval['device_token']) && !empty($Tokenval['access_token'])){
							$deviceToken[] = $Tokenval['device_token'];
							}
                        }
                    }
                $newData['user_id'] = $user_id;
                $centity = $carts->newEntity();
                $cData = $carts->patchEntity($centity, $newData);
                $carts->save($cData);
                $cartId = $cData->id;
					if(!empty($cart_items)){
						foreach ($cart_items as $key => $cvalue) {
						$data['menu_id'] = $cvalue['menu_id'];
						$data['quantity_in_cart'] = $cvalue['quantity_in_cart'];
						$data['final_item_price'] = $cvalue['calculated_item_price'];
						$data['single_item_price'] = $cvalue['selected_size_price'];
						$data['cart_id'] = $cartId;
						$data['menu_size']= $cvalue['size'];
						$sizeArr = json_encode($cvalue['sizes'], true);
						$data['sizes'] = $sizeArr;
						$optionsArr = json_encode($cvalue['custom_menu'], true);
						$data['menu_options'] = $optionsArr;
						$ctentity = $cartitems->newEntity();
						$ctdata = $cartitems->patchEntity($ctentity, $data);
						$cartitems->save($ctdata);
						}
					}
                    $orderData['user_id'] = $user_id;
                    $orderData['cart_id'] = $cartId;
                    $orderData['to_id'] = $rest_id;
                    $orderData['order_number'] = 'OID'.$order_number.$user_id;
					$orderData['amount'] = $total_amount;
                    $orderData['order_date'] = date('Y-m-d H:i:s');
                    $orderentity = $this->Orders->newEntity();
					$orderdata = $this->Orders->patchEntity($orderentity, $orderData);
					//$this->Orders->save($orderdata);
					if($this->Orders->save($orderdata)){
                        $order_id = $orderdata->id;
                        //user payment function to save user payment
                        $this->userSavePayment($user_id, $rest_id, $order_id, $payment_data['response']['id'], $payment_data['response']['intent'], $total_amount, $payment_data['client']['platform'], $order_number);
                    }
                  if(!empty($deviceToken)){
                        $message = "Someone Placed Order";
                        //function to save notification component
                        $this->Notification->saveNotification($user_id, $rest_id, $order_id, 'Someone Placed Order', $message);
                        //function to sent pushnotification
                       $this->Notification->pushNotification($deviceToken, $message, $checkToken['user_type']);
                    }
                return $this->_returnJson(true, 'Order placed Successfully.');
            }else{return $this->_returnJson(false, 'Invalid Request.');}
        }
		//userpayment function
        public function userSavePayment($user_id = null, $rest_id = null, $order_id = null, $transaction_id = null, $transaction_type = null, $total_amount = null, $device_type = null, $order_number = null){
            //records inside array
            $newData = [];
            $newData['user_id'] = $user_id;
            $newData['rest_id'] = $rest_id;
            $newData['order_id'] = $order_id;
            $newData['transaction_id'] = $transaction_id;
            $newData['transaction_type'] = $transaction_type;
            $newData['total_amount'] = $total_amount;
            $newData['device_type'] = $device_type;
            //create entity
            $paymententity = $this->usersPaymentObj->newEntity();
            $paymentData = $this->usersPaymentObj->patchEntity($paymententity, $newData);
            //save payment
            $this->usersPaymentObj->save($paymentData);
            //send notification to user placed order
            $checkToken = $this->usersTbl->find()->select(['Users.id', 'Users.user_type'])->contain(['UserTokens' =>
                    ['fields' => [
                            'UserTokens.user_id', 'UserTokens.device_token', 'UserTokens.access_token'
                        ]
                    ]
                ])->where(['Users.id' => $user_id])->first();
                    if(!empty($checkToken)){
                        $checkToken = $checkToken->toArray();
                        foreach ($checkToken['user_tokens'] as $key => $Tokenval){
                        //check device token not empty
                            if(!empty($Tokenval['device_token']) && !empty($Tokenval['access_token'])){
                                $deviceToken[] = $Tokenval['device_token'];
                            }
                        }
                    }

                    if(!empty($deviceToken)){
                         $message = "Your order has been placed. Your order id is: ". 'OID'.$order_number.$user_id;
                        //function to save notification component
                        $this->Notification->saveNotification($rest_id, $user_id, $order_id, 'Order Placed', $message);
                        //function to sent pushnotification
                        $this->Notification->pushNotification($deviceToken, $message, $checkToken['user_type']);
                    }
                }

     /**
     * Output given data in JSON format.
     * @access protected
     * @param type $length
     * @return object
     */
	 protected function getNextJobNumber($length = 5) {
            $number = '1234567890';
            $numberLength = strlen($number);
            $randomNumber = '';
            for ($i = 0; $i < $length; $i++) {
                $randomNumber .= $number[rand(0, $numberLength - 1)];
            }
                return $randomNumber;
            }

     /**
     * upcoming Order Method
     * @access public
	 * @param type currentUser
     * @return json object
     */
     public function upcomingorders() {
        $current_user = $this->currentUser;
        $current_user_id = $this->currentUser['user']['id'];
		if ($this->request->is('post')) {
        $postData = array();
            $postData = $this->request->getData();
            extract($postData);
            $page   = (isset($page) && $page > 0) ? $page : 1;
            $limit = 10;
            $offset = (--$page) * $limit;
        $Orderdata = $this->Orders->find()->contain(['Users' => ['UserProfiles'], 'Carts' => ['CartItems' => ['MenuItems']]
        ])->where(['Orders.user_id' => $current_user_id, 'Orders.order_status' => 0])
		->order(['Orders.id' => 'DESC'])
		->offset($offset)->limit($limit);
		$orderData = [];
        if(!$Orderdata->isEmpty()){
           $Orderdata = $Orderdata->toArray();
        foreach($Orderdata as $key => $orderVal){
          $restname = $this->userprofilesTbl->find()->select(['UserProfiles.user_id', 'UserProfiles.restaurant_name', 'UserProfiles.address', 'UserProfiles.currency'])->where(['UserProfiles.user_id' => $orderVal['to_id']])->first();
		  if(!empty($restname)){
                $orderData[$key] = array(
                    'order_id' => $orderVal['id'],
					'rest_id' => $restname['user_id'],
                    'restaurant_name' => $restname['restaurant_name'],
					'user_name' => $orderVal['user']['username'],
                    'user_email' => $orderVal['user']['email'],
                    'address' => $restname['address'],
                    'order_date' => $orderVal['order_date'],
					'total_amount' => $this->checkCurrency($restname['currency']).$orderVal['amount'],
                    'total_items' => count($orderVal['cart']['cart_items']));

					$cart_items = [];
				if(!empty($orderVal['cart'])){
					foreach($orderVal['cart']['cart_items'] as $cart_itemsVal){
						$orderData[$key]['cart_items'][] =  array(
						'menu_id' => $cart_itemsVal['menu_id'],
						'item_name' => $cart_itemsVal['menu_item']['item_name'],
						'selected_size_price' => $cart_itemsVal['single_item_price'],
						'item_price' => $this->checkCurrency($restname['currency']).$cart_itemsVal['final_item_price'],
						'quantity_in_cart' => $cart_itemsVal['quantity_in_cart'],
						'calculated_item_price' => $cart_itemsVal['final_item_price'],
						'size' => $cart_itemsVal['menu_size'],
						'custom_menu' => json_decode($cart_itemsVal['menu_options'], true)
						);
					}
				}
		  }
		}
            $finalArray['orders'] = $orderData;
            return $this->_returnJson(true, 'upcoming order List.', $finalArray);
        }else{return $this->_returnJson(false, 'No records found.');}
		}else{return $this->_returnJson(false, 'Invalid Request.');}
      }

	/**
	* order history Method
	* @access public
	* @param type currentUser
	* @return json object
	*/
      public function ordersHistory() {
        $current_user = $this->currentUser;
        $current_user_id = $this->currentUser['user']['id'];
		if ($this->request->is('post')) {
        $postData = array();
            $postData = $this->request->getData();
            extract($postData);
            $page   = (isset($page) && $page > 0) ? $page : 1;
            $limit = 10;
            $offset = (--$page) * $limit;
        $Orderdata = $this->Orders->find()->contain(['Users' => ['UserProfiles'], 'Carts' => ['CartItems' => ['MenuItems']]
        ])->where(['Orders.user_id' => $current_user_id, 'Orders.order_status !=' => 0])
		->order(['Orders.id' => 'DESC'])
		->offset($offset)->limit($limit);
		if(!$Orderdata->isEmpty()){
			$Orderdata = $Orderdata->toArray();
			foreach($Orderdata as $key => $orderVal){
				$restname = $this->userprofilesTbl->find()->select(['UserProfiles.user_id', 'UserProfiles.restaurant_name', 'UserProfiles.address', 'UserProfiles.currency'])->where(['UserProfiles.user_id' => $orderVal['to_id']])->first();
                if(!empty($restname)){
					$orderData[$key] = array(
                    'order_id' => $orderVal['id'],
					'rest_id' => $restname['user_id'],
					'restaurant_name' => $restname['restaurant_name'],
					'user_name' => $orderVal['user']['username'],
                    'user_email' => $orderVal['user']['email'],
                    'address' => $restname['address'],
                    'order_date' => $orderVal['order_date'],
					//'order_status' => ($orderVal['order_status'] == 3) ? 'Completed' : 'Rejected',
					'order_status' => ($orderVal['order_status'] == 3 ? 'Completed' : ($orderVal['order_status'] == 2 ? 'Rejected' : 'Accepted')),
                    'total_amount' => $this->checkCurrency($restname['currency']).$orderVal['amount'],
					'total_items' => count($orderVal['cart']['cart_items']));
					$cart_items = [];
					if(!empty($orderVal['cart'])){
						foreach($orderVal['cart']['cart_items'] as $cart_itemsVal){
						$orderData[$key]['cart_items'][] =  array(
						'menu_id' => $cart_itemsVal['menu_id'],
						'item_name' => $cart_itemsVal['menu_item']['item_name'],
						'item_price' => $this->checkCurrency($restname['currency']).$cart_itemsVal['final_item_price'],
						'quantity_in_cart' => $cart_itemsVal['quantity_in_cart'],
						'category_id' => "",
						'category_name' => "",
						'currencyVal' => "",
						'image' => "",
						'thumbimage' => "",
						'size' => $cart_itemsVal['menu_size'],
						'selected_size_price' => $cart_itemsVal['single_item_price'],
						'calculated_item_price' => $cart_itemsVal['final_item_price'],
						'sizes' => json_decode($cart_itemsVal['sizes'], true),
						'custom_menu' => json_decode($cart_itemsVal['menu_options'], true)
							);
						}
				}
			}
          }
            $finalArray['orders'] = $orderData;
            return $this->_returnJson(true, 'Order History List.', $finalArray);
        }else{return $this->_returnJson(false, 'No records found.');}
		}else{return $this->_returnJson(false, 'Invalid Request.');}
    }
	
	/**
	* order Complete Method
	* @access public
	* @param type currentUser
	* @return json object
	*/
       public function orderComplete() {
        $current_user = $this->currentUser;
         $current_user_id = $this->currentUser['user']['id'];
         if ($this->request->is('post')) {
            $postData = array();
            $postData = $this->request->getData();
			extract($postData);
            $orderId = isset($orderId) ? $orderId : '';
            $orderStatus = isset($orderStatus) ? $orderStatus : '';//3 past orders
            if(empty($orderId)){
                return $this->_returnJson(false, 'Please enter order Id');
             }
             if(empty($orderStatus)){
                return $this->_returnJson(false, 'Please enter order status');
             }
             //update the order table 
            $this->Orders->updateAll(array("order_status" => $orderStatus), array("id" => $orderId, "to_id" => $current_user_id));
            return $this->_returnJson(true, 'Order Completed Successfully.');
         }else{
            return $this->_returnJson(false, 'Invalid Request.');
         }
      }
  }