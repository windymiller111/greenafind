<?php
namespace App\Controller\Api;

use App\Controller\Api\AppController;
use Cake\Event\Event;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Mailer\Email;
use Cake\Auth\DefaultPasswordHasher;
use Cake\Routing\Router;
use Cake\Validation\Validation;

class UsersController extends AppController
{
    //defining class properties
    public $userstokensTbl;
    public $userprofileTbl;
    public $restauranttimesTbl;
	public $userfavoritesTbl;
    public $ratingsTbl;
    public $reviewsTbl;
	public $subsObj;
	public $payDetailObj;


    public function initialize() {
        parent::initialize();  
        $this->loadComponent('RequestHandler');
		$this->loadComponent('ImageUpload');
        $this->loadComponent('Notification');
		$this->loadComponent('CommonMail');
		$this->loadComponent('CommonToken');
        // table object for respective tables
        $this->userstokensTbl = TableRegistry::get('UserTokens');
        $this->userprofileTbl = TableRegistry::get('UserProfiles');
        $this->restauranttimesTbl = TableRegistry::get('RestaurantTimes');
        $this->userfavoritesTbl = TableRegistry::get('UserFavorites');
        $this->ratingsTbl = TableRegistry::get('Ratings');
		$this->reviewsTbl = TableRegistry::get('Reviews');
		$this->subsObj = TableRegistry::get('Subscription');
		$this->payDetailObj = TableRegistry::get('RestaurantPayments');
      }
	  
	  public function paypalTokenData(){
          //echo 'Hello'; die;
		  echo '<pre>'; print_r($_REQUEST); die;
		  //echo '<pre>'; print_r(explode("?", $_REQUEST['code']));
         $check  = explode("?", $_REQUEST['state']);		  
		  //echo $check[1];die;
		  $check1 = explode("=", $check[1]);
		  
		  print_r($check1[1]); die;
       }

     /**
     * ping  Method
     * @access public
     * @return json object
     */
     public function ping() {
        $result = array();
        $result['status'] = true;
        $result['message'] = 'api version v1.0';
        return $this->_returnJson(true, 'ping', $result);
      }
	  
	  /**
     * token  Method
     * @access public
     * @return json object
     */
	 public function jwtTokenData(){
		$current_user_id = $this->currentUser['user']['id'];
        $data = [];
        $jwttoken = $this->CommonToken->oauth2accessToken();
		//print_r($jwttoken); die;
        $data['jwt_access_token']  = $jwttoken;
        return $this->_returnJson(true, 'Token Data successfully', $data);
      }
	  /**
     * savePayment  Method
     * @access public
     * @return json object
     */
	 public function savePaymentDetails() {
          $current_user_id = $this->currentUser['user']['id'];
		  if($this->request->is('post')) {
          $params = $this->request->data;
          $transaction_id = $params['transaction_id'];
          if(!empty($current_user_id) && !empty($transaction_id)) {
            //Subscription fee
            $subsFee = $this->subsObj->find()->select(['amount'])->enableHydration(false)->first();
            $payment = $this->payDetailObj->newEntity();
            $payment->restaurant_id = $current_user_id;
            $payment->amount = $subsFee['amount'];
            $payment = $this->payDetailObj->patchEntity($payment, $params);
            if($this->payDetailObj->save($payment)) {
              //Verify payment
              $data = [];
              $data['device_type'] = $params['device_type'];
              $data['transaction_id'] = $params['transaction_id'];
              $data['receipt'] = $params['receipt'];
              //$data['product_id'] = $params['product_id'];
              if($data['device_type'] == 'android') {
                $data['signature'] = $params['signature'];
              }
              $payResult = $this->verifyPayment($data);
              $paymentUpdate = $this->payDetailObj->get($payment->id);
              $paymentUpdate = $this->payDetailObj->patchEntity($paymentUpdate, $payResult);
              $this->payDetailObj->save($paymentUpdate);
              return $this->_returnJson(true, 'Payment details saved successfully.');
            }else {
              $errors = $payment->errors();
              $erorMessage = array(); 
              $i = 0; 
              $keys = array_keys($errors); 
                foreach ($errors as $errors) { 
                $key = key($errors); 
                  foreach($errors as $error){ 
                  $erorMessage = ucfirst($keys[$i]) . " :- " . $error;
                  }
                $i++;
                }
                return $this->_returnJson(false, $erorMessage);
            }
          }else{
            return $this->_returnJson(false, 'Something went wrong.');
          }
		 }else{

        }
      }

    public function verifyPayment($params){
		if(!empty($params['device_type']) && $params['device_type']=='android') {
			$public_key_base64 = PUBLIC_KEY;
			$publicKey = "-----BEGIN PUBLIC KEY-----\n" . chunk_split($public_key_base64, 64, "\n") . '-----END PUBLIC KEY-----';
			$signature = $params['signature'];
			$receipt = $params['receipt'];
			$key = openssl_get_publickey( $publicKey );
			// signature has to be base64 decoded before passing it to the OpenSSL function
			$result = openssl_verify( $receipt, base64_decode( $signature ), $key, OPENSSL_ALGO_SHA1 );
			if($result == 1) {
			$data['receipt'] = $receipt;
			$receiptData = json_decode($receipt, true);
			$data['orderId'] = $receiptData['orderId'];
			$data['package_name'] = $receiptData['packageName'];
			$data['product_id'] = $receiptData['productId'];
			$data['purchase_time'] = $receiptData['purchaseTime'];
			//$data['auto_renew_status'] = $receiptData['autoRenewing'];
			$data['isPayment'] = 1;
			$data['device_type'] = $params['device_type'];
			return $data;
			} else {
				return $this->_returnJson(false, 'Something went wrong.');
			}
		}
		  //for ios case
		  elseif (!empty($params['device_type']) && $params['device_type']=='ios' && !empty($params['receipt'])){
			$receipt['receipt'] = $params['receipt'];
			$receipt['device_type'] = $params['device_type'];
			$receipt['isPayment'] = 1;
			return $receipt;
		  }
		}

    /**
    * Resaturant/User Login Method
    * @access public
	* @param type email
	* @param type password
	* @param type deviceType
	* @param type deviceToken
	* @param type userType
    * @return json object
    */
    public function login() {
      if (!$this->request->is('post')) {
            return $this->_returnJson(false, 'Invalid Request.');
        } else {
            // extract request data
            $postData = array(); 
            $postData = $this->request->getData();
            extract($postData);
			$email = isset($email) ? $email : '';
            $password = isset($password) ? $password : '';
            $deviceType = isset($deviceType) ? $deviceType : '';
            $deviceToken = isset($deviceToken) ? $deviceToken : '';
            $userType = isset($userType) ? $userType : '';
            if (empty($email)) {
                return $this->_returnJson(false, 'Please enter email.');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->_returnJson(false, 'Please enter a valid email address.');
            }
            if (empty($password)) {
                return $this->_returnJson(false, 'Please enter password');
            }
            if (empty($deviceType)) {
                return $this->_returnJson(false, 'Please provide device type.');
            }
            if (empty($deviceToken)) {
                return $this->_returnJson(false, 'Please provide device token.');
            }
            if (empty($userType)) {
                return $this->_returnJson(false, 'Please provide user type.');
            }
            //Get user details by email
            $userData = $this->Users->find()->select(['Users.id', 'Users.username', 'Users.password', 'Users.firstname', 'Users.lastname', 'Users.email', 'Users.status', 'Users.user_type', 'UserProfiles.profile_image', 'UserProfiles.latitude', 'UserProfiles.longitude'])->contain(['UserProfiles'])->where(['email' => $email, 'user_type' => $userType])->first();
			if (!empty($userData)) {
				  $userData = $userData->toArray();
				if(!empty($userData['user_profile']['latitude']) && !empty($userData['user_profile']['longitude'])){
					$isUpdate = 1;
				}else{
					$isUpdate = 0;
				}
                $hasher = new DefaultPasswordHasher();
				
                if($userData['status'] == 0 && $userType == 3){
                    return $this->_returnJson(false, 'Currently you are Inactive. Please contact to admin via Email: support@greenafind.com to activate your Restaurant.');
                }
				//check password
                if ($hasher->check($password, $userData['password'])) {
                      $user = $userDetails = array();
                      $user['userId'] = $userData['id'];
                      $user['username'] = ucwords(strtolower($userData['username']));
                      $user['firstname'] = ucwords(strtolower($userData['firstname']));
                      $user['lastname'] = ucwords(strtolower($userData['lastname'])); 
                      $user['email'] = $userData['email'];
                      $user['deviceType'] = $deviceType;
                      $user['deviceToken'] = $deviceToken;
                      $user['userType'] = $userType;
                      if(!empty($userData['user_profile']['profile_image'])){
                        $user['image'] = base_url . 'img/' . 'customer_uploads/'. $userData['user_profile']['profile_image'];
                        $user['thumbimage'] = base_url . 'img/' . 'customer_uploads/thumbnail/'. $userData['user_profile']['profile_image'];
					}else{
                        $user['thumbimage'] = "";
                        $user['image'] = "";
                      }
                      //create user tokens and accesstoken
                      $user['accessToken'] = $this->_createOrUpdateAccessToken($userData['id'], $deviceType, $deviceToken);
                      $user['isUpdated'] = $isUpdate;
					  $jwttoken = $this->CommonToken->oauth2accessToken();
                      $user['jwt_access_token']  = $jwttoken;
                      return $this->_returnJson(true, 'Login successfully', $user);
                  
                  }else{return $this->_returnJson(false, 'Incorrect password. Please try again.');}
                }else{return $this->_returnJson(false, 'Incorrect username. Please try again.');}
              }
            }

     /**
     * @access protected
     * @param type $userId
	 * @param type $device_type
	 * @param type $device_token
     * @return object
     */
	 //_createOrUpdateAccessToken update access token protected function
    protected function _createOrUpdateAccessToken($userId, $device_type, $device_token) {
        //usertoken table
        $data = $this->userstokensTbl->find()->where(['user_id' => $userId, 'device_type' => $device_type, 'device_token' => $device_token ])->first();
        $newData = [];
        if(!empty($data)){
            $data = $data->toArray();
            $accessToken = $data['access_token'];
                if(empty($accessToken)){
                    $accessToken = $this->_getAccessToken($userId);
                    $this->userstokensTbl->updateAll(array("access_token" => $accessToken), array("device_type" => $device_type, "device_token" => $device_token, "user_id" => $userId));
                }
            } else {
              $accessToken = $this->_getAccessToken($userId);

              $newData['UserTokens']['user_id'] = $userId;
              $newData['UserTokens']['access_token'] = $accessToken;
              $newData['UserTokens']['device_type'] = $device_type;
              $newData['UserTokens']['device_token'] = $device_token;
              //create entity and patch entity
              $tokensData = $this->userstokensTbl->newEntity();
              $user = $this->userstokensTbl->patchEntity($tokensData, $newData);
              $this->userstokensTbl->Save($user);
          }
          return $accessToken;
      }

     /**
     * This function is used to save user details
     * @access public
	 * @param type $username
	 * @param type $email
	 * @param type $password
	 * @param type $deviceType
	 * @param type $deviceToken
	 * @param type $userType
     * @return json object
     */
      public function register() {
        if ($this->request->is('post')) {
            $postData = array();
            $postData = $this->request->getData();
            extract($postData);
            $username = isset($username) ? $username : '';
            $email = isset($email) ? $email : '';
            $password = isset($password) ? $password : '';
            $deviceType = isset($deviceType) ? $deviceType : '';
            $deviceToken = isset($deviceToken) ? $deviceToken : '';
            $userType = isset($userType) ? $userType : '';
             if (empty($username)) {
                return $this->_returnJson(false, 'Please enter username.');
            }
            if (empty($email)) {
                return $this->_returnJson(false, 'Please enter email.');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->_returnJson(false, 'Please enter a valid email address.');
            }
            if (empty($password)) {
                return $this->_returnJson(false, 'Please enter password');
            }
            
            if (empty($deviceType)) {
                return $this->_returnJson(false, 'Please provide device type.');
            }
            if (empty($deviceToken)) {
                return $this->_returnJson(false, 'Please provide device token.');
            }
            if (empty($userType)) {
                return $this->_returnJson(false, 'Please provide user type.');
            }
            if ($this->_checkUserExist($email, $userType)) {
                return $this->_returnJson(false, "The provided email is already registered.");
            }
            $newData = [];
            $newData['username'] = ucwords(strtolower($username));
            $newData['email'] = $email;
            $newData['password'] = trim($password);
            $newData['user_type'] = $userType;
            $newData['deviceToken'] = $deviceToken;
            $newData['deviceType'] = $deviceType;
			$newData['status'] = 1;
      
            $userentity = $this->Users->newEntity();
            $userData = $this->Users->patchEntity($userentity, $newData, ['validate' => 'OnlyCheck']);
            if ($this->Users->save($userData)) {
            //last insert id
              $userId = $userData->id;
              $newData['userId'] = $userId;
              unset($newData['password']);
              //create user tokens and accesstoken
              $this->_createOrUpdateDeviceToken($userId, $deviceType, $deviceToken);
			  $userId = $userData->id;
                    $newData['userId'] = $userId;
                    $newData['accessToken'] = $this->_createOrUpdateAccessToken($userData->id, $deviceType, $deviceToken);
                    unset($newData['password']);
                    //create user tokens and accesstoken
                    $this->_createOrUpdateDeviceToken($userId, $deviceType, $deviceToken);
					return $this->_returnJson(true, 'You\'ve sign up successfully.', $newData);
				}
          }else{return $this->_returnJson(false, 'Invalid Request.');}
        }

    /**
    * Check whether email already registered.
    * @access protected
    * @param type $email
    * @param type $userType
    * @return boolean (true if record exist)
    */
    protected function _checkUserExist($email, $userType) {
        $exists = $this->Users->exists(['email' => $email, 'user_type' => $userType]);
        return $exists;
    }

    /**
    * Check whether email already registered.
    * @access protected
    * @param type $userId
    * @param type $device_type
	* @param type $device_token
    * @return boolean (true if record exist)
    */
	//_createOrUpdateDeviceToken protected function
    protected function _createOrUpdateDeviceToken($userId, $device_type, $device_token) {
        //usertoken table
        $data = $this->userstokensTbl->find()->where(['user_id' => $userId, 'device_type' => $device_type, 'device_token' => $device_token ])->first();
        $newData = [];
        if(empty($data)){
            $newData['UserTokens']['user_id'] = $userId;
            $newData['UserTokens']['device_type'] = $device_type;
            $newData['UserTokens']['device_token'] = $device_token;
              //create entity and patch entity
            $tokensData = $this->userstokensTbl->newEntity();
            $user = $this->userstokensTbl->patchEntity($tokensData, $newData);
            $this->userstokensTbl->Save($user);
          }
        }

    /**
    * social Login Method
    * @access public
	* @param type $username
	* @param type $email
	* @param type $userType
	* @param type $socialid
	* @param type $provider
	* @param type $deviceType
	* @param type $deviceToken
	* @param type $profileImage
    * @return json object
    */
     public function sociallogin(){
        if ($this->request->is('post')) {
            $postData = array();
            $postData = $this->request->getData();
            extract($postData);
            $username = isset($username) ? $username : '';
            $email = isset($email) ? $email : '';
            $userType = isset($userType) ? $userType : '';//2 customer
            $socialid = isset($socialid) ? $socialid : '';
            $provider = isset($provider) ? $provider : '';//facebook/googlePlus
            $deviceType = isset($deviceType) ? $deviceType : '';
            $deviceToken = isset($deviceToken) ? $deviceToken : '';
			$profileImage = isset($profileImage) ? $profileImage : '';
            if (empty($username)) {
                return $this->_returnJson(false, 'Please enter username.');
            }
            if (empty($email)) {
                return $this->_returnJson(false, 'Please enter email.');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return $this->_returnJson(false, 'Please enter a valid email address.');
            }
            if (empty($userType)) {
                return $this->_returnJson(false, 'Please enter user type.');
            }
             if (empty($socialid)) {
                return $this->_returnJson(false, 'Please enter social id.');
            }
             if (empty($provider)) {
                return $this->_returnJson(false, 'Please enter provider.');
            }
             if (empty($deviceType)) {
                return $this->_returnJson(false, 'Please enter device type.');
            }
            if (empty($deviceToken)) {
                return $this->_returnJson(false, 'Please enter device token.');
            }
			//check email exist first empty
            $emailData = $this->Users->find()->where(['email' => $email, 'user_type' => $userType])->first();
			$isUpdate = 0;
            if(empty($emailData)){
                $newData = $data = [];
                $newData['username'] = ucwords(strtolower($username));
                $newData['email'] = $email;
                $newData['user_type'] = $userType;
				$newData['status'] = 1;
                //userprofile data
                $newData['user_profile']['social_id'] = $socialid;
				if($provider == "GOOGLE"){
					$newData['user_profile']['profile_image'] = $profileImage;
					$newData['user_profile']['profile_thumb_image'] = $profileImage;
				}else{
					$newData['user_profile']['profile_image'] = "https://graph.facebook.com/". $socialid ."/picture?type=large";
					$newData['user_profile']['profile_thumb_image'] = "https://graph.facebook.com/". $socialid ."/picture?type=small";
				}
				$newData['user_profile']['provider'] = $provider;
                $newData['user_tokens'][] = ['device_type' => $deviceType, 'device_token' => $deviceToken];
                $userentity = $this->Users->newEntity();
                $userData = $this->Users->patchEntity($userentity, $newData, ['associated' => ['UserProfiles', 'UserTokens']]);
                $this->Users->save($userData);
                 $data['userId'] = $userData->id;
                 $data['username'] = ucwords(strtolower($username));
                 $data['email'] = $email;
                 $data['userType'] = $userType;
                 $data['socialId'] = $socialid;
                 $data['provider'] = $provider;
				 $data['isUpdated'] = $isUpdate;
				 if($provider == "GOOGLE"){
					$data['image'] = $profileImage;
					$data['thumbimage'] = $profileImage; 
				 }else{
					$data['image'] = "https://graph.facebook.com/". $socialid ."/picture?type=large";
					$data['thumbimage'] = "https://graph.facebook.com/". $socialid ."/picture?type=small";
				 }
				$data['accessToken'] = $this->_createOrUpdateAccessToken($userData->id, $deviceType, $deviceToken);
                return $this->_returnJson(true, 'Login successfully', $data);
            }else{
				$emailData = $emailData->toArray();
				//check user profile update
				$userProfile = $this->userprofileTbl->find()->select(['longitude', 'latitude'])->where(['user_id' => $emailData['id']])->first();
				if(!empty($userProfile)){
					$userProfile = $userProfile->toArray();
				if(!empty($userProfile['latitude']) && !empty($userProfile['longitude'])){
					$isUpdate = 1;
				}else{
					$isUpdate = 0;
				}
				}else{
					$isUpdate = 0;
				}
				//if email exist use user id
                //user profile query
                $userId = $emailData['id'];
                $checkSocialData = $this->userprofileTbl->find()->where(['user_id' => $userId, 'provider' => $provider, 'social_id' => $socialid])->first();
                if(!empty($checkSocialData)){
                   //get data
                 $data['userId'] = $userId;
                 $data['username'] = ucwords(strtolower($username));
                 $data['email'] = $email;
                 $data['user_type'] = $userType;
                 $data['socialId'] = $socialid;
                 $data['provider'] = $provider;
				 $data['isUpdated'] = $isUpdate;
				 //check google provider
				 if($provider == "GOOGLE"){
					 $this->userprofileTbl->updateAll(array("profile_image" => $profileImage, "profile_thumb_image" => $profileImage), array("user_id" => $userId));
                  $data['image'] = $profileImage;
				  $data['thumbimage'] = $profileImage;
				 }else{
					$file_name = "https://graph.facebook.com/". $socialid ."/picture?type=large";
					$file_name_small = "https://graph.facebook.com/". $socialid ."/picture?type=small";
					if(!empty($file_name)){
					$this->userprofileTbl->updateAll(array("profile_image" => $file_name, "profile_thumb_image" => $file_name_small), array("user_id" => $userId));
					$data['image'] = "https://graph.facebook.com/". $socialid ."/picture?type=large";
					$data['thumbimage'] = "https://graph.facebook.com/". $socialid ."/picture?type=small";
					}
				}
                 $data['accessToken'] = $this->_createOrUpdateAccessToken($userId, $deviceType, $deviceToken);
                    return $this->_returnJson(true, 'Login successfully', $data);
                }else{
                    //insert data into userprofile
                    $newProfileData = [];
                    $newProfileData['user_id'] = $userId;
                    $newProfileData['provider'] = $provider;
                    $newProfileData['social_id'] = $socialid;
					$newProfileData['profile_image'] = "https://graph.facebook.com/". $socialid ."/picture?type=large";
					$newProfileData['profile_thumb_image'] = "https://graph.facebook.com/". $socialid ."/picture?type=small";
                    $profileentity = $this->userprofileTbl->newEntity();
                    $profileData = $this->userprofileTbl->patchEntity($profileentity, $newProfileData);
                    $this->userprofileTbl->save($profileData);
                    //data
                    $data['userId'] = $userId;
                    $data['username'] = ucwords(strtolower($username));
                    $data['email'] = $email;
                    $data['userType'] = $userType;
                    $data['socialId'] = $socialid;
                    $data['provider'] = $provider;
					$data['isUpdated'] = $isUpdate;
					if($provider == "GOOGLE"){
						$data['image'] = $profileImage;
						$data['thumbimage'] = $profileImage;
					}else{
						$data['image'] = "https://graph.facebook.com/". $socialid ."/picture?type=large";
						$data['thumbimage'] = "https://graph.facebook.com/". $socialid ."/picture?type=small";
					}
					$data['accessToken'] = $this->_createOrUpdateAccessToken($userId, $deviceType, $deviceToken);
                    return $this->_returnJson(true, 'Login successfully', $data);
                }
              }
            }else{return $this->_returnJson(false, 'Invalid Request.');}
          }

    /**
    * forget Password Method
	* @access public
	* @param type $email
	* @param type $userType
    * @return json object
    */
     public function forgotPassword(){
        if ($this->request->is(['post'])) {
            $postData = array();
            $postData = $this->request->getData();
            extract($postData);
			$email = isset($email) ? $email : '';
            $userType = isset($userType) ? $userType : '';//2= customer/3 = cafe
            if (empty($email)) {
                return $this->_returnJson(false, 'Please enter email.');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                  return $this->_returnJson(false, 'Please enter a valid email.');
            }
            if (empty($userType)) {
                return $this->_returnJson(false, 'Please enter user Type.');
            }
            $emailData = $this->Users->find()->where(['Users.email' => $email, 'Users.user_type' => $userType])->first();
            $six_digit_random_number = mt_rand(100000, 999999);
            //$otp_expiration = date('Y-m-d H:i:s', strtotime('+24 hours'));
            if(!empty($emailData)){
                $emailData = $emailData->toArray();
                $newData = [];
                if($userType == 2){$newData['name'] = $emailData['username'];
                  }else if($userType == 3){$newData['name'] = $emailData['firstname'];}
                //$newData['username']        = $emailData['username'];
                $newData['username'] = $newData['name'];
                $newData['email']        = $emailData['email'];
                $newData['password']   = $six_digit_random_number;
                //updte otp users table
                $updateUser = $this->Users->get($emailData['id']);
                $updateUser->password = $six_digit_random_number;
                if($this->Users->save($updateUser)){
					try{
						$this->CommonMail->sendMail('forgotpassword', 'Reset password', $newData, $emailData['email']);
					}catch (Exception $ex) {echo $error = $e->getMessage();}
                  return $this->_returnJson(true, 'Password sent to your registered email address successfully');
                  }
                }else{return $this->_returnJson(false, 'No any account exists for this email');}
              }else{return $this->_returnJson(false, 'Invalid Request.');}
            }

	/**
	* Cafe/Restaurant List Method
	* @access public
	* @param type $user_id
	* @param type $restName
	* @param type $phoneNumber
	* @param type $address
	* @param type $latitude
	* @param type $longitude
	* @param type $place_id
	* @param type $cafeImage
	* @param type $currencyCode
	* @param type $timing
	* @return json object
	*/
     public function retaurantProfile(){
      //get current user restaurant
      $current_user_id = $this->currentUser['user']['id'];
      if ($this->request->is('post')) {
        $postData = array();
        $postData = $this->request->getData();
		extract($postData);
        $user_id = isset($user_id) ? $user_id : '';
        $restName = isset($restName) ? $restName : '';
        $phoneNumber = isset($phoneNumber) ? $phoneNumber : '';
        $address = isset($address) ? $address : '';
        $latitude = isset($latitude) ? $latitude : '';
        $longitude = isset($longitude) ? $longitude : '';
		$place_id = isset($place_id) ? $place_id : '';
        $cafeImage = isset($cafeImage) ? $cafeImage : '';
		$currencyCode = isset($currencyCode) ? $currencyCode : '';
		$client_id = isset($client_id) ? $client_id : '';
        $timing = isset($timing) ? $timing : '';
		if (empty($restName)) {
          return $this->_returnJson(false, 'Please enter restaurant name.');
        }
        if (empty($phoneNumber)) {
          return $this->_returnJson(false, 'Please enter phone number.');
        }
        if (empty($address)) {
          return $this->_returnJson(false, 'Please enter address.');
        }
        if (empty($latitude)) {
          return $this->_returnJson(false, 'Please enter restaurant latitude.');
        }
        if (empty($longitude)) {
          return $this->_returnJson(false, 'Please enter restaurant longitude.');
        }
		if (empty($currencyCode)) {
          return $this->_returnJson(false, 'Please enter currency code.');
        }
        if (empty($timing)) {
          return $this->_returnJson(false, 'Please enter timing.');
        }
        $timing = json_decode($timing, true);
		//check rating table data
		$ratingData = $this->ratingsTbl->find()->where(['place_id' => $place_id]);
        if(!$ratingData->isEmpty()){
			$ratingData = $ratingData->toArray();
			foreach ($ratingData as $key => $value) {
            $place_id = $value['place_id'];
          }
          $this->ratingsTbl->updateAll(array("to_id" => $current_user_id), array("place_id" => $place_id));
        }
		//review update
        $reviewData =  $this->reviewsTbl->find()->where(['place_id' => $place_id]);
		if(!$reviewData->isEmpty()){
			$reviewData = $reviewData->toArray();
          foreach ($reviewData as $key => $value) {
            $place_id = $value['place_id'];
          }
          $this->reviewsTbl->updateAll(array("to_id" => $current_user_id), array("place_id" => $place_id));
        }
        //get curent usser data
        $userData = $this->userprofileTbl->find()->where(['user_id' => $current_user_id])->first();
        //check file
		if(!empty($postData['cafeImage'])){
          $path = WWW_ROOT . '' . 'img/restaurant_uploads/';
          //thumbnailpath
          $thumbpath = WWW_ROOT . '' . 'img/restaurant_uploads/thumbnail/';
		   //create the image component
              $upload = $this->ImageUpload->uploadImage($postData['cafeImage'], $path, $thumbpath);
              if($upload['status']){
                $file_name = $upload['imageName'];
                if(!empty($userData['profile_image'])){
                //unlink file
                  @unlink($path.$userData['profile_image']);
                  @unlink($thumbpath.$userData['profile_image']);
                }
              }else{
                return $this->_returnJson(false, $upload['message']);  
              }
            }
            
          //check user profile data
            if(!empty($userData)){
              $userData = $userData->toArray();
              $data = $this->userprofileTbl->get($userData['id']);
              $data->restaurant_name = $restName;
              $data->phone_number = $phoneNumber;
              $data->address = $address;
              $data->latitude = $latitude;
              $data->longitude = $longitude;
			  $data->currency = !empty($currencyCode) ? $currencyCode : '';
			  $data->client_id = !empty($client_id) ? $client_id : '';
              if(!empty($postData['cafeImage'])){
                $data->profile_image = $file_name;
              }
                $this->userprofileTbl->save($data);
                //check restaurant timing data
              $restData = $this->restauranttimesTbl->find()->where(['user_id' => $current_user_id])->first();
              if(empty($restData)){
                foreach ($timing as $key => $Timevalue) {
                  $timingArr = [];
                  $timingArr['user_id'] = $Timevalue['user_id'];
                  $timingArr['weekday'] = ucfirst(strtolower($Timevalue['weekday']));
                  if($Timevalue['restaurant_status'] == 0){
                    $timingArr['start_time'] = '00:00';
                    $timingArr['end_time'] = '00:00';
                  }else{
                    $timingArr['start_time'] = date("H:i", strtotime($Timevalue['start_time']));
                    $timingArr['end_time'] = date("H:i", strtotime($Timevalue['end_time']));
                  }
                  $timingArr['restaurant_status'] = $Timevalue['restaurant_status'];
                  $entity = $this->restauranttimesTbl->newEntity();
                  $Timedata = $this->restauranttimesTbl->patchEntity($entity, $timingArr);
                  $this->restauranttimesTbl->save($Timedata);
                }
              }else{
                //check update
                foreach ($timing as $key => $timing_value) {
                  if($timing_value['restaurant_status'] == 0){
                      $st = $timing_value['start_time'] = '00:00';
                      $et = $timing_value['end_time'] = '00:00';
                    }else{
                      $st = date("H:i", strtotime($timing_value['start_time']));
                      $et = date("H:i", strtotime($timing_value['end_time']));
                    }
                    $this->restauranttimesTbl->updateAll(array("start_time" => $st, "end_time" => $et, "restaurant_status" => $timing_value['restaurant_status']), array("user_id" => $current_user_id, "weekday" => ucfirst(strtolower($timing_value['weekday']))));
                  }
                }
                //get response
                return $this->_returnJson(true, 'Update Information Successfully.');
              }else{return $this->_returnJson(false, 'No record found.');}
            }else{return $this->_returnJson(false, 'Invalid Request.');}
          }

      /**
      * logout Method
      * @access public
	  * @param type $accessToken
      * @return json object
      */
      public function logout() {
		  $current_user_id = $this->currentUser['user']['id'];
          if ($this->request->is('post')) {
              $postData = array();
              $postData = $this->request->getData();
               extract($postData);
               $accessToken = isset($accessToken) ? $accessToken : '';
              if (empty($accessToken)) {
                return $this->_returnJson(false, 'Please enter access Token.');
              }
               $tokenData = $this->userstokensTbl->find()->where(['access_token' => $accessToken])->first();
               if(!empty($tokenData)){
                 $this->userstokensTbl->updateAll(array("access_token" => NULL), array("access_token" => $accessToken));
                  return $this->_returnJson(true, 'You have been successfully Logged out.');
               }else{
                  return $this->_returnJson(false, 'Invalid access Token');
               }
             }else{return $this->_returnJson(false, 'Invalid Request.');}
           }

      /**
      * user rating list Method
	  * @access public
	  * @param type $currentUser
      * @return json object
      */
      public function restRatingList() {
        $current_user = $this->currentUser;
        $ratings = TableRegistry::get('Ratings');
        $RatingList = $ratings->find()->select(['Users.username', 'UserProfiles.profile_image', 'UserProfiles.profile_thumb_image', 'UserProfiles.social_id', 'Ratings.rating', 'Ratings.comment', 'Ratings.created'])
        ->join([
            'users' => [
            'table' => 'users',
            'type' => 'INNER',
             'conditions' => ['Users.id = Ratings.user_id'],
              'alias' => 'Users'],
              'userprofiles' => [
              'table' => 'user_profiles',
              'type' => 'LEFT',
              'conditions' => ['UserProfiles.user_id = Ratings.user_id'],
              'alias' => 'UserProfiles'
              ],
            ])->where(['Ratings.to_id' => $current_user['user']['id']]);
               $newData = [];
               if(!$RatingList->isEmpty()){
				   $RatingList = $RatingList->toArray();
                foreach ($RatingList as $key => $ratingVal) {
                  $data = [];
                  $data['username'] = $ratingVal['Users']['username'];
				  
				if(!empty($ratingVal['UserProfiles']['social_id'])){
					$data['image'] = $ratingVal['UserProfiles']['profile_image'];
					$data['thumbimage'] = $ratingVal['UserProfiles']['profile_thumb_image'];
				}else{
				if(!empty($ratingVal['UserProfiles']['profile_image'])){
					$data['image'] = base_url . 'img/' . 'customer_uploads/'. $ratingVal['UserProfiles']['profile_image'];
					$data['thumbimage'] = base_url . 'img/' . 'customer_uploads/thumbnail/'. $ratingVal['UserProfiles']['profile_image'];
				}else{
					$data['image'] = "";
					$data['thumbimage'] = "";
				}
			}
                  $data['rating'] = round($ratingVal['rating'], 1);
				  $data['comment'] = $ratingVal['comment'];
                  $data['date'] = $ratingVal['created'];
                  $newData[] = $data;
               }
               return $this->_returnJson(true, 'restaurant rating list.', $newData);
             }else{
                return $this->_returnJson(false, 'No restaurant rating yet.');
              }
            }
	 /**
      * view Resaturant Profile Method
      * @access public
	  * @param type $rest_id
	  * @param type $place_id
      * @return json object
      */
       public function viewResaturantProfile() {
          if ($this->request->is('post')) {
             $postData = array(); 
            $postData = $this->request->getData();
            extract($postData);
            $rest_id = isset($rest_id) ? $rest_id : '';
            $place_id = isset($place_id) ? $place_id : '';
            $data = $this->Users->find()->contain(['UserProfiles', 'RestaurantTimes'])->where(['Users.id' => $rest_id])->first();
            $newData = [];
          if(!empty($data)){
            $data = $data->toArray();
            if(!empty($place_id)){
              $newData['place_id'] = $place_id;
            }else{
              $newData['place_id'] = 0;
            }
            $newData['restaurantName'] =$data['user_profile']['restaurant_name'];
            $newData['email'] =$data['email'];
            $newData['phoneNumber'] =$data['user_profile']['phone_number'];
            $newData['latitude'] =$data['user_profile']['latitude'];
            $newData['longitude'] =$data['user_profile']['longitude'];
            $newData['address'] =$data['user_profile']['address'];
			$newData['client_id'] =!empty($data['user_profile']['client_id']) ? $data['user_profile']['client_id'] : '';
			$newData['available_online'] =!empty($data['user_profile']['available_online']) ? $data['user_profile']['available_online'] : 'Yes';
            if(!empty($data['user_profile']['profile_image'])){
               $newData['image'] = base_url . 'img/' . 'restaurant_uploads/' . $data['user_profile']['profile_image'];
                $newData['thumbimage'] = base_url . 'img/' . 'restaurant_uploads/thumbnail/' . $data['user_profile']['profile_image'];
            }else{
              $newData['image'] = "";
              $newData['thumbimage'] = "";
            }
            $i = 1;
            if(!empty($data['restaurant_times'])){
            foreach ($data['restaurant_times'] as $key => $timingvalue) {
              $timedata['start_time'] = $timingvalue['start_time']->format('h:i A');
              $timedata['end_time'] = $timingvalue['end_time']->format('h:i A');
              $timedata['weekday'] = $timingvalue['weekday'];
              $timedata['restaurant_status'] = $timingvalue['restaurant_status'];
              $timedata['index'] = $i;
              $newtime[] = $timedata;
              $i++;
            }
            $newData['timing'] = $newtime;
          }
            return $this->_returnJson(true, 'Restaurant Information Successfully.', $newData);
          }else{return $this->_returnJson(false, 'No records found.');}
        }else{
            return $this->_returnJson(false, 'Invalid Request.');
          }
        }

      /**
     * This function is used to get the list of all restaurant inside radius of 5 km from user's location
     * @access public
     *@param type $userLat
	 * @param type $userLong
	 * @param type $Keyword
	 * @return json object
     */
      public function getRestaurantList() {
        $current_user = $this->currentUser['user']['id'];
          if ($this->request->is('post')) {
            //extract request data
            $postData = array(); 
            $postData = $this->request->getData();
			extract($postData);
			$userLat = isset($userLat) ? $userLat : '';
            $userLong = isset($userLong) ? $userLong : '';
            $Keyword = isset($Keyword) ? $Keyword : '';
            // check for user coordinates
            if (empty($userLat) || empty($userLong)) {
                return $this->_returnJson(false, 'Please provide coordinates of user\'s location.');
            }
            // set query parameters
            $radiusDistance = 1;
            //updae the lat/long user side
            $userData = $this->userprofileTbl->find()->where(['user_id' => $current_user])->first();
              if(!empty($userData)){
                $userData = $userData->toArray();
                $data = $this->userprofileTbl->get($userData['id']);
                $data->latitude = $userLat;
                $data->longitude = $userLong;
                $this->userprofileTbl->save($data);
              }
              //check non registerd restaurant
              $cateName = $Keyword;
			  //call function curlExec
			 $googleDetails = $this->curlExec($userLat,$userLong,$radiusDistance,$cateName);
            //get count of total provider records
                    $providersList = $finalArr = array();
                    $providerDetails = array();
                    // get list of all providers with inradius of 5km from users location
                    $restaurants = $this->getRestaurantData($radiusDistance, $userLat, $userLong);
                  if(!$restaurants->isEmpty()){
					  $restaurants = $restaurants->toArray();
                    foreach ($restaurants as $restaurant) {
                      //check is favoutite reastaurant function
                      $favdata = $this->isFavourite($current_user, $restaurant['id']);
                        if(!empty($favdata)){
                          if($favdata['status'] == 0){
                            $providerDetails['isfavourite'] = $favdata['status'];
                          }else if($favdata['status'] == 1){
                            $providerDetails['isfavourite'] = $favdata['status'];
                          }  
                        }else{
                          $providerDetails['isfavourite'] = 0;
                        }
                      //call getRatings list function
                      $ratingdata = $this->getRatings($restaurant['id']);
                      if(!empty($ratingdata)){
                           $providerDetails['total_user'] = $ratingdata['total_user'];
                           $providerDetails['averagerating'] = round($ratingdata['rating'], 1);
						   $providerDetails['isReviewed'] = 1 ;
                        }else{
                          $providerDetails['total_user'] = 0;
                          $providerDetails['averagerating'] = 0;
						  $providerDetails['isReviewed'] = 0 ;
                        }
                      //restuarant part
                      $providerDetails['rest_id'] = $restaurant['user_profile']['user_id'];
                      $providerDetails['restaurant_name'] = $restaurant['user_profile']['restaurant_name'];
                      if(!empty($restaurant['user_profile']['profile_image'])){
                        $providerDetails['image'] = base_url . 'img/' . 'restaurant_uploads/' . $restaurant['user_profile']['profile_image'];
                        $providerDetails['thumbimage'] = base_url . 'img/' . 'restaurant_uploads/thumbnail/' . $restaurant['user_profile']['profile_image'];
                      }else{
                        $providerDetails['image'] = "";
                        $providerDetails['thumbimage'] = "";
                      }
                      $providerDetails['distance'] = $restaurant['distance'];
                      $providerDetails['latitude'] = $restaurant['user_profile']['latitude'];
                      $providerDetails['longitude'] = $restaurant['user_profile']['longitude'];
					  $providerDetails['address'] = $restaurant['user_profile']['address'];
					  $providerDetails['type'] = "register";
                      $providersList[] = $providerDetails;
                      }
                      $providersMergeList = array_merge($providersList, $googleDetails);
					  //Comparision function 
					  usort($providersMergeList, 'App\Controller\Api\cmp');
                      $finalArr['restaurants'] = array_values($providersMergeList);
                      return $this->_returnJson(true, 'Restaurant List Successfully', $finalArr);
                    }else{ 
                      $providersMergeList = array_merge($providerDetails,$googleDetails);
					  usort($providersMergeList, 'App\Controller\Api\cmp');
                        $finalArr['restaurants'] = array_values($providersMergeList);
                        if(!empty($finalArr['restaurants'])){
                          return $this->_returnJson(true, 'Restaurant List Successfully', $finalArr);
                        }
                        else {
                          return $this->_returnJson(false, 'There are no establishment in the area you have searched. Try selecting a different filter.');
                        }
                      }
              }else{return $this->_returnJson(false, 'Invalid Request.');}
            }
			
			public function GetDrivingDistance($lat1, $lat2, $long1, $long2){
            $url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$lat1.",".$long1."&destinations=".$lat2.",".$long2."&key=AIzaSyBVH6kKYp1xlkbchYDUDvdTDziCJKIi6Sg";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            $response = curl_exec($ch);
            curl_close($ch);
            $response_a = json_decode($response, true);
            $dist = $response_a['rows'][0]['elements'][0]['distance']['text'];
            //$unit = preg_replace('/[0-9]+./', '', $dist);
            //$dist = preg_replace("/[^0-9,.]/", "", $dist);
            /*if($unit == "m") {
            $dist = $dist*0.000621371;
            }
            else {
            $dist = $dist*0.621371;
            }*/
            // return array('distance' => $dist);
            return $dist;
          }

            //call function curlExec
            public function curlExec($latitude,$longitude,$distance,$cateName) {
              //$distance = $distance*1.60934*1000;
			  //$distance = $distance*1.60934;
			  $distance = 50;
              $newar = array();
              $ch = curl_init();
              if(!empty($cateName)) {
                $cateName = str_replace(' ', '', $cateName);
				$url = 'https://maps.googleapis.com/maps/api/place/search/json?location='.$latitude.','.$longitude.'&radius='.$distance.'&keyword='.$cateName.'&key=AIzaSyBVH6kKYp1xlkbchYDUDvdTDziCJKIi6Sg';
              }else{
                return $newar;
              }
              curl_setopt($ch, CURLOPT_URL, $url);
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
              $contents = curl_exec($ch);
              $json = json_decode($contents, true);
              if(!empty($json['results'])) {
                foreach($json['results'] as $key => $item) {
				//GetDrivingDistance
				//$drivingDistance = $this->GetDrivingDistance($latitude, $item['geometry']['location']['lat'], $longitude, $item['geometry']['location']['lng']);
				//if($drivingDistance <= 1.6){	
                $newar[$key]['isfavourite'] = 0;
                $unratingdata = $this->getPlaceIdRatings($item['place_id']);
                if(!empty($unratingdata)){
                  $newar[$key]['total_user'] = $unratingdata['total_user'];
                  $newar[$key]['averagerating'] = round($unratingdata['rating'], 1);
				  $newar[$key]['isReviewed'] = 1;
                }else{
                  $newar[$key]['total_user'] = 0;
                  $newar[$key]['averagerating'] = 0;
				  $newar[$key]['isReviewed'] = 0;
                }
                $newar[$key]['rest_id'] = 0;
                $newar[$key]['restaurant_name'] = $item['name'];
                if(!empty($item['photos'][0]['photo_reference'])) {
                  $newar[$key]['image'] = $item['photos'][0]['photo_reference'];
                  $newar[$key]['thumbimage'] = $item['photos'][0]['photo_reference'];
                }
                else {
                  $newar[$key]['image'] = "";
                  $newar[$key]['thumbimage'] = "";
                }
                $newar[$key]['latitude'] = $item['geometry']['location']['lat'];
                $newar[$key]['longitude'] = $item['geometry']['location']['lng'];
                $newar[$key]['address'] = $item['vicinity'];
                $newar[$key]['place_id'] = $item['place_id'];
                $newar[$key]['type'] = "unregister";
				
				//$newar[$key]['distance'] = $drivingDistance;}
              }
            }
            return $newar;
          }

        
	/**
     * Output given data in JSON format.
     * @access protected
     * @param type $place_id
     * @return object
     */
	 protected function getPlaceIdRatings($place_id = null) {
          $query = $this->ratingsTbl->find();
          $ratingAverage = $query->select(['averagerating' => $query->func()->avg('rating'), 'totalcount' => $query->func()->count('user_id'), 'to_id', 'place_id'])->where(['place_id' => $place_id])
          ->group('to_id');
          $newData  = [];
		  if(!$ratingAverage->isEmpty()){
			  $ratingAverage = $ratingAverage->toArray();
          foreach ($ratingAverage as $key => $ratingValue) {
            $data = [];
            $data['total_user'] = $ratingValue->totalcount;
            $data['restaurant_id'] = $ratingValue->to_id;
            $data['rating'] = $ratingValue->averagerating;
            $data['placeId'] = $ratingValue->placeId;
            $newData = $data;
          }
          return $newData;
		  }
        }

      /**
      * To get count of total records with in specified radius from user's location
      * @access public
      * @param type $radius
      * @param type $lat
      * @param type $long
      * @return integer 
      */
      public function getTotalRecordsCount($radius, $lat, $long) {
		  $distanceField = '(6371 * acos (cos ( radians(:latitude) )
                            * cos( radians( latitude ) )
                            * cos( radians( longitude )
                            - radians(:longitude) )
                            + sin ( radians(:latitude) )
                            * sin( radians( latitude ) )))';

            $Reports = $this->Users->find()
            ->select(['Users.id', 'UserProfiles.user_id', 'distance' => $distanceField])
            ->join([
                'userprofiles' => [
                'table' => 'user_profiles',
                'type' => 'INNER',
                'conditions' => ['UserProfiles.user_id = Users.id'],
                'alias' => 'UserProfiles']])
            ->where(['Users.user_type' => 3])->having(["distance < " => $radius])  
            ->order(['distance'])
            ->bind(':latitude', $lat, 'float')
            ->bind(':longitude', $long, 'float');
            $totalCount = $Reports->count();
            return $totalCount;
          }

      /**
     * To get list of providers upto specified limit within specified radius from user's location
     * @access public
     * @param type $radius
     * @param type $lat
     * @param type $long
     * @return array
     */
      public function getRestaurantData($radius, $lat, $long) {
        $providers = array();
		//radius km
		$radius = $radius * 1.60934;
        // formuala to calculate to get radius distance between latitudes and longitudes with provided lat,long values 
        $distanceField = '(6371 * acos (cos ( radians(:latitude) )
                            * cos( radians( latitude ) )
                            * cos( radians( longitude )
                            - radians(:longitude) )
                            + sin ( radians(:latitude) )
                            * sin( radians( latitude ) )))';
         $condition = ['Users.user_type' => 3];
		 $providers = $this->Users->find()->select(['Users.id', 'Users.email', 'Users.user_type', 'UserProfiles.user_id', 'UserProfiles.latitude', 'UserProfiles.longitude', 'UserProfiles.restaurant_name', 'UserProfiles.profile_image', 'distance' => $distanceField])->contain(['UserProfiles'])
            ->where($condition)->having(["distance < " => $radius]) 
            ->order(['UserProfiles.user_id' => 'DESC'])
            ->bind(':latitude', $lat, 'float')
            ->bind(':longitude', $long, 'float')->distinct();
            return $providers;
		}

    /**
    * Get rest_ratings.
    * @access protected
    *@param type restaurant_id
    * @return json object
    */
    protected function getRatings($restaurant_id = null) {
     $query = $this->ratingsTbl->find();
      $ratingAverage = $query->select(['averagerating' => $query->func()->avg('rating'), 'totalcount' => $query->func()->count('user_id'), 'to_id'])->where(['to_id' => $restaurant_id])->group('to_id');
        $newData  = [];
		if(!$ratingAverage->isEmpty()){
			$ratingAverage = $ratingAverage->toArray();
        foreach ($ratingAverage as $key => $ratingValue) {
            $data = [];
            $data['total_user'] = $ratingValue->totalcount;
            $data['restaurant_id'] = $ratingValue->to_id;
            $data['rating'] = $ratingValue->averagerating;
			$newData = $data;
        }
        return $newData;
		}
      }
      
      /**
      * top Resaturant list Method
      * @access public
	  * @param type currentUser
      * @return json object
      */
	
     /**
      * Get view Restaurant.
      * @access public
      * @param type rest_id
	  * @param type userLat
	  * @param type userLong
      * @return json object
      */
      public function getViewRestaurant() {
        $current_user = $this->currentUser['user']['id'];
        if ($this->request->is('post')) {
          $postData = array();
          $postData = $this->request->getData();
           extract($postData);
           $rest_id = isset($rest_id) ? $rest_id : '';
           $userLat = isset($userLat) ? $userLat : '';
            $userLong = isset($userLong) ? $userLong : '';
            if (empty($rest_id)) {
              return $this->_returnJson(false, 'Please enter rest id.');
            }
            if (empty($userLat) || empty($userLong)) {
                return $this->_returnJson(false, 'Please provide coordinates of user\'s location.');
            }
            $radiusDistance = 1;
			$radiusDistance = $radiusDistance * 1.60934;
            $distanceField = '(6371 * acos (cos ( radians(:latitude) )
              * cos( radians( latitude ) )
              * cos( radians( longitude )
              - radians(:longitude) )
              + sin ( radians(:latitude) )
              * sin( radians( latitude ) )))';
            $data = $this->Users->find()->select(['distance' => $distanceField, 'UserProfiles.restaurant_name', 'UserProfiles.address', 'UserProfiles.profile_image', 'UserProfiles.client_id', 'UserProfiles.available_online'])->contain(['UserProfiles', 'RestaurantTimes'])
           ->where(['Users.id' => $rest_id])
           ->having(["distance < " => $radiusDistance])
           ->bind(':latitude', $userLat, 'float')
           ->bind(':longitude', $userLong, 'float')->distinct()
           ->first();
           
           $newData =  [];
           if(!empty($data)){
            $data = $data->toArray();
            $newData['restaurantName'] = $data['user_profile']['restaurant_name'];
            $newData['address'] =$data['user_profile']['address'];
			$newData['client_id'] = !empty($data['user_profile']['client_id']) ? $data['user_profile']['client_id'] : '';
            $newData['available_online'] = !empty($data['user_profile']['available_online']) ? $data['user_profile']['available_online'] : 'Yes';
            $newData['distance'] =$data['distance'];
            if(!empty($data['user_profile']['profile_image'])){
               $newData['image'] = base_url . 'img/' . 'restaurant_uploads/' . $data['user_profile']['profile_image'];
               $newData['thumbimage'] = base_url . 'img/' . 'restaurant_uploads/thumbnail/' . $data['user_profile']['profile_image'];
            }else{
              $newData['image'] = "";
              $newData['thumbimage'] = "";
            }
            $RTdata = $this->restauranttimesTbl->find()->where(['RestaurantTimes.user_id' => $rest_id, 'RestaurantTimes.weekday' => date('l')])->first();
             if(!empty($RTdata)){
              $newData['start_time'] = $RTdata['start_time']->format('h:i A');
              $newData['end_time'] = $RTdata['end_time']->format('h:i A');
               $newData['restaurant_status'] = $RTdata['restaurant_status'];
            }
            $ratingdata = $this->getRatings($rest_id);
              if(!empty($ratingdata)){
                $newData['total_user'] = $ratingdata['total_user'];
                $newData['avgrating'] = round($ratingdata['rating'], 1);
              }else{
                $newData['total_user'] = 0;
                $newData['avgrating'] = 0;
              }
			  //call isFavourite function
              $favdata = $this->isFavourite($current_user, $rest_id);
                if(!empty($favdata)){
                  if($favdata['status'] == 0){
                    $newData['isfavourite'] = $favdata['status'];
                  }else if($favdata['status'] == 1){
                    $newData['isfavourite'] = $favdata['status'];
                  }
                }else{
                  $newData['isfavourite'] = 0;
                }
            return $this->_returnJson(true, 'Restaurant Data Successfully.', $newData);
          }else{return $this->_returnJson(false, 'No records found.');}
        }else{return $this->_returnJson(false, 'Invalid Request.');}
       }

			/**
			* user rating list Method
			* @access public
			* @param type restaurant_id
			* @param type place_id
			* @return json object
			*/
             public function userRestRating() {
			  $current_user_id = $this->currentUser['user']['id'];
              if ($this->request->is('post')) {
                $postData = array(); 
                $postData = $this->request->getData();
                extract($postData);
                 $restaurant_id = isset($restaurant_id) ? $restaurant_id : '';
                 $place_id = isset($place_id) ? $place_id : '';
                 if(!empty($restaurant_id)){
                  $condition = array('Ratings.to_id' => $restaurant_id);
                 }else if(!empty($place_id)){
                  $condition = array('Ratings.place_id' => $place_id);
                 }
                 $RatingList = $this->ratingsTbl->find()->select(['Users.username', 'UserProfiles.profile_image', 'Ratings.rating', 'Ratings.comment', 'Ratings.created'])
                 ->join([
                  'users' => [
                  'table' => 'users',
                  'type' => 'INNER',
                  'conditions' => ['Users.id = Ratings.user_id'],
                  'alias' => 'Users'],
                  'userprofiles' => [
                  'table' => 'user_profiles',
                  'type' => 'LEFT',
                  'conditions' => ['UserProfiles.user_id = Ratings.user_id'],
                  'alias' => 'UserProfiles'
                  ],
                ])->where($condition);

                 if(!$RatingList->isEmpty()){
                  $RatingList = $RatingList->toArray();
				  //echo '<pre>'; print_r($RatingList); die;
				  foreach ($RatingList as $key => $ratingVal) {
                    $data = [];
                    $data['username'] = $ratingVal['Users']['username'];
                    if(!empty($ratingVal['UserProfiles']['profile_image'])){
                      $data['image'] = base_url . 'img/' . 'customer_uploads/'. $ratingVal['UserProfiles']['profile_image'];
                      $data['thumbimage'] = base_url . 'img/' . 'customer_uploads/thumbnail/'. $ratingVal['UserProfiles']['profile_image'];
                    }else{
                      $data['image'] = "";
                      $data['thumbimage'] = "";
                    }
                    $data['rating'] = $ratingVal['rating'];
					$data['comment'] = $ratingVal['comment'];
                    $data['date'] = $ratingVal['created'];
                    $newData[] = $data;
                  }
                  return $this->_returnJson(true, 'user restaurant rating list.', $newData);
                }else{return $this->_returnJson(false, 'No restaurant rating yet.');}
              }else{return $this->_returnJson(false, 'Invalid Request.');}
            }

		/**
		* Add to favoutite .
		* @access public
		* @param type user_id
		* @param type restaurant_id
		* @param type status
		* @return json object
		*/
		public function addToFavourite() {
			$current_user_id = $this->currentUser['user']['id'];
			if ($this->request->is('post')) {
            //extract request data
            $postData = array(); 
            $postData = $this->request->getData();
            extract($postData);
			$user_id = isset($user_id) ? $user_id : '';
            $restaurant_id = isset($restaurant_id) ? $restaurant_id : '';
            $status = isset($status) ? $status : '';
            if (empty($user_id)) {
                return $this->_returnJson(false, 'Please enter user id.');
            }
            if (empty($restaurant_id)) {
                return $this->_returnJson(false, 'Please enter restaurant id.');
            }
            if ($status == '') {
                return $this->_returnJson(false, 'Please enter status.');
            }
            $checkfavorites = $this->userfavoritesTbl->find()->where(['user_id' => $user_id, 'to_id' => $restaurant_id])->first();
            if(!empty($checkfavorites)){
              $this->userfavoritesTbl->updateAll(array("status" => $status), array("user_id" => $user_id, "to_id" => $restaurant_id));
              if($status == 0){return $this->_returnJson(true, 'You have successfully unfavorite the restaurant.');}else if($status == 1){return $this->_returnJson(true, 'You have successfully add to favorite list.');}
            }else{
            $newData = [];
            $newData['user_id'] = $user_id;
            $newData['to_id'] = $restaurant_id;
            $newData['status'] = $status;
            $newData['created'] = date('Y-m-d H:i:s');
            $favoritesentity = $this->userfavoritesTbl->newEntity();
            $favoriteData = $this->userfavoritesTbl->patchEntity($favoritesentity, $newData);
            if ($this->userfavoritesTbl->save($favoriteData)) {return $this->_returnJson(true, 'You have successfully add to favorite list.');}
        }
      }else{return $this->_returnJson(false, 'Invalid Request.');}
    }

		/**
		* favoutite list.
		* @access public
		*@param type currentUser
		* @return json object
		*/
		public function favouriteList() {
			$current_user = $this->currentUser;
			$current_user_id = $this->currentUser['user']['id'];
			if ($this->request->is('post')) {
            //extract request data
            $postData = array(); 
            $postData = $this->request->getData();
			extract($postData);
            $page   = (isset($page) && $page > 0) ? $page : 1;
            $limit = 10;
            $offset = (--$page) * $limit;
			$favoriteData = $this->userfavoritesTbl->find()->select(['UserFavorites.user_id', 'UserFavorites.to_id', 'UserProfiles.restaurant_name', 'UserProfiles.profile_image'])
			->join([
                'userprofiles' => [
                'table' => 'user_profiles',
                'type' => 'LEFT',
                'conditions' => ['UserProfiles.user_id = UserFavorites.to_id'],
                'alias' => 'UserProfiles'
                ],
              ])->where(['UserFavorites.user_id' => $current_user_id, 'UserFavorites.status' => 1])
              ->offset($offset)->limit($limit)
              ->order(['UserProfiles.user_id' => 'DESC']);
              $providersList = $finalArr = array();
				if(!$favoriteData->isEmpty()){
					$favoriteData = $favoriteData->toArray();
					foreach ($favoriteData as $key => $FavVal) {
                      $data = [];
                      $ratingdata = $this->getRatings($FavVal['to_id']);
                      if(!empty($ratingdata)){
                        $data['total_user'] = $ratingdata['total_user'];
                        $data['averagerating'] = round($ratingdata['rating'], 1);
                      }else{
                        $data['total_user'] = 0;
                        $data['averagerating'] = 0;
                      }
                      $data['restaurant_id'] = $FavVal['to_id'];
                      $data['restaurant_name'] = $FavVal['UserProfiles']['restaurant_name'];
                      if(!empty($FavVal['UserProfiles']['profile_image'])){
                        $data['image'] = base_url . 'img/' . 'restaurant_uploads/'. $FavVal['UserProfiles']['profile_image'];
                        $data['thumbimage'] = base_url . 'img/' . 'restaurant_uploads/thumbnail/'. $FavVal['UserProfiles']['profile_image'];
                      }else{
                        $data['image'] = "";
                        $data['thumbimage'] = "";
                      }
                      $newData[] = $data;
                    }
                    $finalArr = array_values($newData);
                    return $this->_returnJson(true, 'Favorite Retaurants.', $finalArr);
                  }else{return $this->_returnJson(false, 'No records found');}
            }else{ return $this->_returnJson(false, 'Invalid Request.');}
          }

		/**
		* favoutite restaurat delete.
		* @access public
		*@param type user_id
		*@param type restaurant_id
		* @return json object
		*/
       public function deleteFavRestaurant() {
		   $current_user_id = $this->currentUser['user']['id'];
        if ($this->request->is('post')) {
            $postData = array(); 
            $postData = $this->request->getData();
            extract($postData);
            $user_id = isset($user_id) ? $user_id : '';
            $restaurant_id = isset($restaurant_id) ? $restaurant_id : '';
            if (empty($user_id)) {
                return $this->_returnJson(false, 'Please enter user id.');
            }
            if (empty($restaurant_id)) {
                return $this->_returnJson(false, 'Please enter restaurant id.');
            }
            $this->userfavoritesTbl->deleteAll(array('UserFavorites.user_id' => $user_id, 'UserFavorites.to_id' => $restaurant_id), false);
            return $this->_returnJson(true, 'You have been successfully deleted the favorite restaurant.');
        }else{
          return $this->_returnJson(false, 'Invalid Request.');
        }
      }

		/**
		* Reviews Ques Answer
		* @access public
		*@param type currentUser
		* @return json object
		*/
       public function getReviewsQuesAns() {
          $current_user_id = $this->currentUser['user']['id'];
           $userques = TableRegistry::get('ReviewQuestions');
           $Quesdata = $userques->find()->contain(['ReviewOptions']);
         if(!$Quesdata->isEmpty()){
			 $Quesdata = $Quesdata->toArray();
           foreach ($Quesdata as $key => $quesvalue) {
            $data = [];
            $data['question_id'] = $quesvalue['id'];
            $data['question'] = $quesvalue['question'];
            $newOptionData = [];
            foreach ($quesvalue['review_options'] as $key => $optionvalue) {
              $optiondata = [];
              $optiondata['option_id'] = $optionvalue['id'];
              $optiondata['option'] = $optionvalue['option'];
              $optiondata['rate'] = $optionvalue['rate'];
              $newOptionData[] = $optiondata;
            }
            $data['options'] = $newOptionData;
            $newData[] = $data;
          }
          return $this->_returnJson(true, 'Rating question answer list.', $newData);
        }else{return $this->_returnJson(false, 'No records found.');}
      }
	  
	  /**
		* Reviews Percentage
		* @access public
		*@param type restaurant_id
		*@param type place_id
		* @return json object
		*/
	   public function getReviewsPercentage() {
		$current_user_id = $this->currentUser['user']['id'];
          if ($this->request->is('post')) {
            $postData = array();
            $postData = $this->request->getData();
            extract($postData);
            $restaurant_id = isset($restaurant_id) ? $restaurant_id : '';
            $place_id = isset($place_id) ? $place_id : '';
            $restId = '';
            $condition = [];
            if(!empty($restaurant_id)){
              $restId = array('Reviews.to_id' => $restaurant_id);
            }else if(!empty($place_id)){
              $restId = array('Reviews.place_id' => $place_id);
            }
			//first check given restid/placeid exist
			$exists = $this->reviewsTbl->exists([$restId]);
			if($exists){
            //review questions table data
            $reviewQues = TableRegistry::get('ReviewQuestions');
            $reviewQues = $reviewQues->find()->select(['id'])->contain(['ReviewOptions']);
            if(!$reviewQues->isEmpty()){
              $reviewQues = $reviewQues->toArray();
              $final = array();
              foreach ($reviewQues as $key => $reviewsVal) {
                $ques_id = $reviewsVal['id'];
                //total count of reviews
                $reviewsCount = $this->reviewsTbl->find()->where([$restId, 'Reviews.ques_id' => $ques_id])
                ->count();
                $newData = [];
                foreach ($reviewsVal['review_options'] as $key => $rvalue) {
                  $reviewOptionId = $rvalue['id'];
                  $reviewOption = $rvalue['option'];
                  //condition array
                  $condition = array($restId, 'Reviews.ques_id' => $ques_id, 'Reviews.option_id' => $reviewOptionId);
                  //review table query with count
                  $query = $this->reviewsTbl->find();
                  $reviewsQuery = $query->select(['totalcount' => $query->func()->count('Reviews.id')])
                  ->where($condition)->toArray();
                  //check percentage
                  if(!empty($reviewsQuery)){$percentage = ($reviewsQuery[0]['totalcount']/$reviewsCount) * 100;}else{$percentage = "";}
                  $newData[$reviewOption] = round($percentage, 2);
                }
                //final result
                //$final[] = array_merge(array("label" => $ques_id), $newData);
				$final[] = $newData;
              }
              return $this->_returnJson(true, 'options percentage list.', $final);
            }else{}
			}else{return $this->_returnJson(false, 'No reviews yet.');}
          }else{return $this->_returnJson(false, 'Invalid Request.');}
        }

		/**
		* submit Reviews Ques Answer
		* @access public
		* @param type user_id
		* @param type restaurant_id
		* @param type place_id
		* @param type comment
		* @return json object
		*/
		public function submitReviews() {
			$current_user_id = $this->currentUser['user']['id'];
          if ($this->request->is('post')) {
            $postData = array(); 
            $postData = $this->request->getData();
			extract($postData);
            $user_id = isset($user_id) ? $user_id : '';
            $restaurant_id = isset($restaurant_id) ? $restaurant_id : '';
            $place_id = isset($place_id) ? $place_id : '';
            $comment = isset($comment) ? $comment : '';
            if (empty($user_id)) {
                return $this->_returnJson(false, 'Please enter user id.');
            }
            if (empty($quesansData)) {
                return $this->_returnJson(false, 'Please enter quesansData.');
            }
			 $quesansData = json_decode($quesansData, true);
             $sum = 0;
             //device token
             $checkToken = $this->Users->find()->contain(['UserTokens' => 
                [
                  'fields' => ['UserTokens.user_id', 'UserTokens.device_token', 'UserTokens.access_token']
                ]
              ])->where(['Users.id' => $restaurant_id])->first();
             if(!empty($checkToken)){
              $checkToken = $checkToken->toArray();
			  foreach ($checkToken['user_tokens'] as $key => $Tokenval){
                  //check device token not empty
                  if(!empty($Tokenval['device_token']) && !empty($Tokenval['access_token'])){
                    $deviceToken[] = $Tokenval['device_token'];
                  }
				}
			}
			//check first
             $condition = [];
             if(!empty($restaurant_id)){
              $condition = array('Ratings.user_id' => $user_id, 'Ratings.to_id' => $restaurant_id);
            }else if(!empty($place_id)){
              $condition = array('Ratings.user_id' => $user_id, 'Ratings.place_id' => $place_id);
            }          
            $checkratings = $this->ratingsTbl->find()->where($condition)->first();
             if(empty($checkratings )){
              if(!empty($quesansData)){
                foreach ($quesansData as $key => $value) {
                $data['user_id'] = $user_id;
                if(!empty($restaurant_id)){
                  $data['to_id'] = $restaurant_id;
                }else{
                  $data['place_id'] = $place_id;
                }
                $data['ques_id'] = $value['ques_id'];
                $data['option_id'] = $value['option_id'];
                $entity = $this->reviewsTbl->newEntity();
                $Timedata = $this->reviewsTbl->patchEntity($entity, $data);
                $this->reviewsTbl->save($Timedata);
                $sum += $value['rate'];
              }
            }
              $data1['rating'] = $sum;
              $data1['user_id'] = $user_id;
              if(!empty($restaurant_id)){
                $data1['to_id'] = $restaurant_id;
              }else{
                $data1['place_id'] = $place_id;
              }
              $data1['comment'] = $comment;
              $userratings = $this->ratingsTbl->newEntity();
              $Rstedata = $this->ratingsTbl->patchEntity($userratings, $data1);
              $this->ratingsTbl->save($Rstedata);
              if(!empty($deviceToken)){
                $message = "A review has been submitted";
                //function to save notification component
                $this->Notification->saveNotification($user_id, $restaurant_id, 'submitReviews', $message);
                //function to sent pushnotification
                $this->Notification->pushNotification($deviceToken, $message, $checkToken['user_type']);
              }
              return $this->_returnJson(true, 'You have Successfully Submit the reviews.');
          }else{
            if(!empty($restaurant_id)){
              $condition = array('Reviews.user_id' => $user_id, 'Reviews.to_id' => $restaurant_id);
            }else if(!empty($place_id)){
             $condition = array('Reviews.user_id' => $user_id, 'Reviews.place_id' => $place_id);
            }
            $checkreviews = $this->reviewsTbl->find()->where($condition);
            if(!$checkreviews->isEmpty()){
              foreach ($quesansData as $key => $value) {
                $data['user_id'] = $user_id;
                if(!empty($restaurant_id)){
                  $data['to_id'] = $restaurant_id;
                }else{
                  $data['place_id'] = $place_id;
                }
                $data['ques_id'] = $value['ques_id'];
                $data['option_id'] = $value['option_id'];
                $option_id[] = $value['option_id'];
               //update
                if(!empty($restaurant_id)){
                   $condition = array("user_id" => $user_id, "to_id" => $restaurant_id, "ques_id" => $value['ques_id']);
                 }else if(!empty($place_id)){
                   $condition = array("user_id" => $user_id, "place_id" => $place_id, "ques_id" => $value['ques_id']);
                 }
                 $this->reviewsTbl->updateAll(array("option_id" => $value['option_id']), array($condition));
                 $sum += $value['rate'];
              }
			 }
            if(!empty($restaurant_id)){
              $this->ratingsTbl->updateAll(array("rating" => $sum, "comment" => $comment), array("user_id" => $user_id, "to_id" => $restaurant_id));
            }else if(!empty($place_id)){
				$this->ratingsTbl->updateAll(array("rating" => $sum, "comment" => $comment), array("user_id" => $user_id, "place_id" => $place_id));
			}
           return $this->_returnJson(true, 'You have Successfully Update the reviews.');
          }
        }else{return $this->_returnJson(false, 'Invalid Request.');}
      }

	/**
     * Get get rest with Menu.
     * @access public
     * @param type restaurant_id
	 * @param type category_id
     * @return json object
     */
    public function getRestwithMenu() {
      $current_user = $this->currentUser;
      $current_user_id = $this->currentUser['user']['id'];
      if ($this->request->is('post')) {
        $postData = array(); 
        $postData = $this->request->getData();
        extract($postData);
        $restaurant_id = isset($restaurant_id) ? $restaurant_id : '';
        $category_id = isset($category_id) ? $category_id : '';
        if (empty($restaurant_id)){
            return $this->_returnJson(false, 'Please enter restaurant id.');
          }
          //category data
          if(!empty($category_id)){
            $condition = array('MenuItems.category_id' => $category_id, 'MenuItems.user_id' => $restaurant_id);
          }else {
            $condition = array('MenuItems.user_id' => $restaurant_id);
          }
          //menu data
          $menuitems = TableRegistry::get('MenuItems');
          $menuData = $menuitems->find()->select(['MenuItems.id', 'MenuItems.category_id', 'MenuCategories.title', 'MenuItems.item_name', 'MenuItems.description', 'MenuItems.item_image', 'MenuItems.menu_sizes'])
          ->contain(['MenuCategories', 'MenuOptions'])
          ->where($condition)
          ->order(['MenuItems.id' => 'DESC']);
          //user currency
          $curencyData = $this->userprofileTbl->find()->select(['user_id', 'currency'])->where(['user_id' => $restaurant_id])->first();
            if(!empty($curencyData)){
              $curencyData = $curencyData->toArray();
              $currency  = $curencyData['currency'];
              //call function
              $codekey = !empty($this->checkCurrency($currency)) ? $this->checkCurrency($currency) : '';
            }
            if(!$menuData->isEmpty()){
              $menuData = $menuData->toArray();
              $newArr = array();
              foreach ($menuData as $menuVal) {
                $temp = array();
                 $temp['menu_id'] = $menuVal['id'];
                 $temp['category_id'] = $menuVal['category_id'];
                 $temp['category_name'] = $menuVal['menu_category']['title'];
                 $temp['item_name'] = $menuVal['item_name'];
				 $temp['description'] = $menuVal['description'];
                 $temp['quantity_in_cart'] = 0;
                 $temp['currencyVal'] = $codekey;
				 $temp['currency'] = $currency;
                 $temp['selected_size_price'] = "";
                 $temp['size'] = "";
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
          }else{return $this->_returnJson(false, 'Invalid Request.');}
        }

    /**
     * Output given data in JSON format.
     * @access protected
     * @param type $user_id
	 * @param type $restaurant_id
     * @return object
     */
	 //function to check isfavurite 
    protected function isFavourite($user_id = null, $restaurant_id = nul) {
		$checkfavorites = $this->userfavoritesTbl->find()->where(['user_id' => $user_id, 'to_id' => $restaurant_id])->first();
		if(!empty($checkfavorites)){return $checkfavorites;}
      }

		/**
		*view customer profile.
		* @access public
		* @param type $currentUser
		* @return json object
		*/
       public function viewCustomerProfile() {
          $current_user = $this->currentUser;
          $current_user_id = $this->currentUser['user']['id'];
          $data = $this->Users->find()->select(['Users.username', 'Users.email', 'UserProfiles.user_id', 'UserProfiles.phone_number', 'UserProfiles.address', 'UserProfiles.profile_image', 'UserProfiles.profile_thumb_image', 'UserProfiles.social_id'])->contain(['UserProfiles'])->where(['Users.id' => $current_user_id])->first();
        if(!empty($data)){
          $data = $data->toArray();
          $newData['username'] =$data['username'];
          $newData['email'] =$data['email'];
          $newData['userId'] =$data['user_profile']['user_id']; 
          $newData['phone_number'] =$data['user_profile']['phone_number'];
          $newData['address'] =$data['user_profile']['address'];
		  if(!empty($data['user_profile']['social_id'])){
			  $newData['image'] = $data['user_profile']['profile_image'];
			  $newData['thumbimage'] = $data['user_profile']['profile_thumb_image'];
		  }else{
			if(!empty($data['user_profile']['profile_image'])){
				$newData['image'] =base_url . 'img/' . 'customer_uploads/' . $data['user_profile']['profile_image'];
				$newData['thumbimage'] =base_url . 'img/' . 'customer_uploads/thumbnail/' . $data['user_profile']['profile_image'];
			}else{
				$newData['image'] ="";
				$newData['thumbimage'] ="";
			}
		  }
         
         return $this->_returnJson(true, 'User Information Successfully.', $newData);
        }else{return $this->_returnJson(false, 'No records found.');}
      }

		/**
		*edit customer profile.
		* @access public
		* @param type $currentUser
		* @return json object
		*/
     public function editCustomerProfile() {
        $current_user = $this->currentUser;
        $current_user_id = $this->currentUser['user']['id'];
        if ($this->request->is('post')) {
          $postData = array(); 
          $postData = $this->request->getData();
          extract($postData);
          $username = isset($username) ? $username : '';
          $phoneNumber = isset($phoneNumber) ? $phoneNumber : '';
          $userData = $this->userprofileTbl->find()->where(['user_id' => $current_user_id])->first();
		  if(!empty($postData['profileImage'])){
             //file path
            $path = WWW_ROOT . '' . 'img/customer_uploads/';
            //thumbnailpath
            $thumbpath = WWW_ROOT . '' . 'img/customer_uploads/thumbnail/';
              $upload = $this->ImageUpload->uploadImage($postData['profileImage'], $path, $thumbpath);
              if($upload['status']){
                $file_name = $upload['imageName'];
                if(!empty($userData['profile_image'])){
                //unlink file
                  @unlink($path.$userData['profile_image']);
                  @unlink($thumbpath.$userData['profile_image']);
                }
              }else{
                return $this->_returnJson(false, $upload['message']);  
              }
            }
			$user = $this->Users->get($current_user_id);
            $user->username = ucwords(strtolower($username));
            $this->Users->save($user);

          $data['phone_number'] = $phoneNumber;
          //$data['address'] = $address;
          $data['user_id'] = $current_user_id;
          if(!empty($postData['profileImage'])){
              $data['profile_image'] = $file_name;
            }
          //$userData = $this->userprofileTbl->find()->where(['user_id' => $current_user_id])->first();
		if(empty($userData)){
            $userprofileTblentity = $this->userprofileTbl->newEntity();
          }else{
            $userprofileTblentity = $this->userprofileTbl->get($userData['id']);
          }
          $userData = $this->userprofileTbl->patchEntity($userprofileTblentity, $data);
          $this->userprofileTbl->save($userData);
          $data['username'] = ucwords(strtolower($username));
          if(!empty($_FILES['profileImage'])){
              $data['image'] = base_url . 'img/' . 'customer_uploads/' . $file_name;
              $data['thumbimage'] = base_url . 'img/' . 'customer_uploads/thumbnail/' . $file_name;
            }else{
              $data['image'] = "";
              $data['thumbimage'] = "";
            }
          return $this->_returnJson(true, 'Edit Information Successfully.', $data);
          
        }else{return $this->_returnJson(false, 'Invalid Request.');}
      }
	  
	   /**
		* change Password Method
		* @access public
		* @param type $oldpassword
		* @param type $password
		* @return json object
		*/
          public function changePassword() {
            $current_user = $this->currentUser;
             if ($this->request->is('post')) {
              $postData = array();
               $postData = $this->request->getData();
                extract($postData);
                $oldpassword = isset($oldpassword) ? $oldpassword : '';
                $password = isset($password) ? $password : '';
                if (empty($oldpassword)) {
                  return $this->_returnJson(false, 'Please enter your old password.');
                }
                if (empty($password)) {
                  return $this->_returnJson(false, 'Please enter your new password.');
                }
              $userData = $this->Users->find()->where(['id' => $current_user['user']['id']])->first();
              if(!empty($userData)){
                $userData = $userData->toArray();
                $hasher = new DefaultPasswordHasher();
                if ($hasher->check($oldpassword, $userData['password'])) {
                  $updatePassword = $this->Users->get($userData['id']);
                  $updatePassword->password = $password;
                  if($this->Users->save($updatePassword)){
                    return $this->_returnJson(true, 'Your password has been successfully changed.');
                  }
                }else{return $this->_returnJson(false, 'Invalid old password. Please enter a correct password.');}
               
              }else{return $this->_returnJson(false, 'Not a valid User.');}

             }else{return $this->_returnJson(false, 'Invalid Request.');}
           }
	}
		
	 /**
     * @access public
	 * @param type a
	 * @param type b
     * @return json object
     */
	 //averagerating function used inside getRestaurantList
	  function cmp($a, $b) {
        if($a["averagerating"] == $b["averagerating"]){
          return 0;
        }
        return ($a["averagerating"] > $b["averagerating"]) ? -1 : 1;
      }