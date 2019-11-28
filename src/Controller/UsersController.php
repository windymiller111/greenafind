<?php
namespace App\Controller;

use App\Controller\AppController;
use Cake\Mailer\Email;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Event\Event;
use Cake\ORM\Query;
use Cake\I18n\Time;
use Cake\Auth\DefaultPasswordHasher;
class UsersController extends AppController
{
	public function initialize()
    {
    	parent::initialize();
        $this->Auth->allow(['login', 'getAjax', 'chkemailexist', 'chkphoneexist', 'getNewsLetter']); 
        $this->loadComponent('RequestHandler');
		$this->loadComponent('CommonMail');
	}
    
    /**
     * Index Method
     *
     * @access public
     */
     public function index(){
        return $this->redirect(['action' => 'login']);
    }

    /**
     * login Method
     *
     * @access public
     */
    public function login(){
    	$this->viewBuilder()->setLayout('adminlogin');
      if ($this->request->is('post')) {
	        $user = $this->Auth->identify();
	        if ($user) {
	            $this->Auth->setUser($user);
	            return $this->redirect($this->Auth->redirectUrl());
	        }
	        $this->Flash->error('Your username or password is incorrect.');
        }
      }

    /**
     * logout Method
     *
     * @access public
     */
    public function logout(){
        $this->Flash->success(__('You are now logged out.'));
        return $this->redirect($this->Auth->logout());
    }

     /**
     * dashboard Method
     *
     * @access public
     */
     public function dashboard(){
      $user_counts = $this->Users->find()->where(['Users.user_type' => 3, 'Users.id !=' => 1])->count();
      $this->set(compact('user_counts'));
     }

     /**
     * restautant List Method
     *
     * @access public
     */
     public function restautantList(){
       //$conditions['not']['Users.id'] = 1;
        $conditions = ['Users.id !=' => 1, 'Users.user_type !=' => 2];
            $this->paginate = [
                'limit' => 10,
                'conditions' => $conditions
            ];
        $users = $this->Users->find()->order(['Users.id' => 'DESC'])->contain(['UserProfiles']);
        $usersData = $this->paginate($users);
        $this->set(compact('usersData'));
      }

      /**
      * Change password Method
      *
      * @access public
      */ 
      public function changePassword(){
        if ($this->request->is(['post'])) {
          $postData = $this->request->getData();
          $adminId = $this->Auth->user('id');
          if(!empty($postData)){
            $userData = $this->Users->find()->where(['id' => $adminId])->first();
            if(!empty($userData)){
              $userData = $userData->toArray();
               $hasher = new DefaultPasswordHasher();
               if (!$hasher->check($postData['oldpassword'], $userData['password'])) {
                $this->Flash->success(__('old password is incorrect.'), ["clear" => 'true']);
                return $this->redirect(['action' => 'changePassword']);
              }else{
                $updatePassword = $this->Users->get($userData['id']);
                $updatePassword->password = $postData['password'];
                if($this->Users->save($updatePassword)){
                  $this->Flash->success(__('Your password is changed Successfully.'), ["clear" => 'true']);
                  return $this->redirect(['action' => 'changePassword']);
                }
              }
            }else{$this->Flash->success(__('Not a valid user.'), ["clear" => 'true']);}
          }
        }
      }  

      /**
      * @description Function to change the Status of the User i.e approved or disapproved..
      *
      * @param type $userId
      * @param type $status
      */
      public function changeStatus($userId = NULL, $status = NULL) {
        if (!$userId) {
           $this->redirect(array('controller' => 'users', 'action' => 'restautant-list'));
        }
        $userdata = $this->Users->find()->where(['Users.id' => $userId, 'Users.user_type' => 3])->first();
            $userEmail = $userdata->email;
            $newData['firstname'] = $userdata->firstname;
            $newData['email'] = $userdata->email;
            $newData['password'] = $this->getToken(8);
                if ($status == 0) {
                //mail send
                    $this->Users->updateAll(array("status" => 0), array("Users.id" => $userId));
                    $this->Flash->success(__(USER_DEACTIVATED));
					try{
						$this->CommonMail->sendMail('restaurantinactive', 'Greenafind registration information', $newData, $userEmail);
                    } catch (Exception $ex) {echo $error = $e->getMessage();}
                }else{
                    try{
						$this->CommonMail->sendMail('restaurantactive', 'Welcome to Greenafind', $newData, $userEmail);
					} catch (Exception $ex) {echo $error = $e->getMessage();}
                    //$this->Users->updateAll(array("status" => 1), array("Users.id" => $userId, 'Users.user_type' => 3));
                    $updatePassword = $this->Users->get($userdata->id);
                     $updatePassword->status = 1;
                      $updatePassword->password = $newData['password'];
                      $this->Users->save($updatePassword);
                    $this->Flash->success(__(USER_ACTIVATED));
                }
                $this->redirect(array('controller' => 'users', 'action' => 'restautant-list'));
            }
		/**
		* @description Function to change the Approval/Unapproval of the User i.e approved or disapproved..
		*
		* @param type $userId
		* @param type $is_approved
		*/
		public function changeApproval($userId = NULL, $is_approved = NULL) {
			if (!$userId) {
           $this->redirect(array('controller' => 'users', 'action' => 'restautant-list'));
		   }
		   $userdata = $this->Users->find()->where(['Users.id' => $userId, 'Users.user_type' => 3])->first();
            $userEmail = $userdata->email;
            $newData['firstname'] = $userdata->firstname;
            $newData['email'] = $userdata->email;
            $newData['password'] = $this->getToken(8);
            if ($is_approved == '0') {
              $this->Users->updateAll(array("is_approved" => '0'), array("Users.id" => $userId));
              $this->Flash->success(__(USER_DISAPPROVED));
               try{
                 $this->CommonMail->sendMail('restaurantdisapproved', 'Greenafind registration information', $newData, $userEmail);
               } catch (Exception $ex) {echo $error = $e->getMessage();}
             }else{
             try{
                $this->CommonMail->sendMail('restaurantactive', 'Welcome to Greenafind', $newData, $userEmail);
               } catch (Exception $ex) {echo $error = $e->getMessage();}
                $updatePassword = $this->Users->get($userdata->id);
                 $updatePassword->is_approved = 1;
                  $updatePassword->password = $newData['password'];
                  $this->Users->save($updatePassword);
                  $this->Flash->success(__(USER_APPROVED));
                }
                $this->redirect(array('controller' => 'users', 'action' => 'restautant-list'));
              }

          /**
          * view restautant List Method
          *
          * @access public
          */
          public function viewRestaurant($id=null){
            $users = $this->Users->get($id, ['contain' => ['UserProfiles', 'RestaurantTimes']]);
            $this->set('users', $users);
          }

          /**
          * User Rating to Restaurant Method
          *
          * @access public
          */
          public function userRating2($id = null){
            $ratings = TableRegistry::get('Ratings');
            $limit = 1;
            $RatingList = $ratings->find()->select(['Users.username', 'UserProfiles.profile_image', 'Ratings.rating', 'Ratings.created'])
            ->join([
              'users' => [
              'table' => 'users',
              'type' => 'INNER',
              'conditions' => ['Users.id = Ratings.user_id'],
              'alias' => 'Users'],
              'userprofiles' => [
              'table' => 'user_profiles',
              'type' => 'INNER',
              'conditions' => ['UserProfiles.user_id = Ratings.user_id'],
              'alias' => 'UserProfiles'],
              ])->where(['Ratings.to_id' => $id]);
            $this->paginate = ['limit' => 10,];
            $RatingList = $this->paginate($RatingList);
            //echo '<pre>'; print_r($RatingList); die;
            $this->set(compact('RatingList'));
          }
	/**
	* User Rating to Restaurant Method
	*
	* @access public
	*/
	 public function userRating($id = null){
		 $ratings = TableRegistry::get('Ratings');
		 //question data
		 $userques = TableRegistry::get('ReviewQuestions');
		 $Quesdata = $userques->find()->select(['id', 'question'])->toArray();
		 //reviewew Id condition
		 $condition['Reviews.to_id'] = $id;
		 $RatingList = $this->Users->find()->select(['Users.id', 'Users.username', 'UserProfiles.profile_image', 'Ratings.rating', 'Ratings.created'])->contain(['UserProfiles', 'Reviews' => function(\Cake\ORM\Query $q) use ($condition) {return $q->where($condition)->contain(['ReviewOptions']);}
		 ])
		 ->join([
		 'ratings' => [
		'table' => 'ratings',
		'type' => 'LEFT',
		'conditions' => ['Ratings.user_id = Users.id'],
		'alias' => 'Ratings']
		])
		->where(['Ratings.to_id' => $id])
		->order(['Ratings.rating' => 'DESC']);
		$this->paginate = ['limit' => 10];
		$RatingList = $this->paginate($RatingList);
		//echo '<pre>'; print_r($RatingList); die;
		$this->set(compact('RatingList', 'Quesdata', 'userdata'));
		}

		/**
		* News letter Method
		*
		* @access public
		*/
        public function getNewsLetter(){
		$this->viewBuilder()->setLayout('ajax');
          if ($this->request->is(array('ajax', 'post'))) {
            $postData = $this->request->getData();
            $newData = [];
            $newData['email'] = $postData['newsEmail'];
            $newsletter = TableRegistry::get('NewLetters');
            $news = $newsletter->newEntity();
            $newsletterData = $newsletter->patchEntity($news, $newData);
            if($newsletter->save($newsletterData)){
                echo 1;
            }
            exit();
          }
        }

      /**
      * get Ajax Method
      *
      * @access public
      */
      public function getAjax(){
        $this->viewBuilder()->setLayout('ajax');
        if ($this->request->is(array('ajax', 'post'))) {
             $postData = $this->request->getData();
             //check email exist
             if ($this->Users->exists(['email' => $postData['email'], 'Users.user_type' => 3])) {echo '2'; exit;}
                
                $newData = [];
                $newData['firstname'] = $postData['firstname'];
                $newData['lastname'] = $postData['lastname'];
                $newData['email'] = $postData['email'];
                $newData['user_type'] = 3;

                $newData['user_profile']['phone_number'] = $postData['phone_number'];
				$newData['user_profile']['restaurant_name'] = $postData['restaurant_name'];
                $newData['user_profile']['address'] = $postData['address'];
                $getLatLong = $this->findCityLatLong($postData['address']);
                $myLatLong = explode(',', $getLatLong);
                $newData['user_profile']['latitude'] = $myLatLong[0];
                $newData['user_profile']['longitude'] = $myLatLong[1];
                
				$data['firstname'] = $postData['firstname'];
                $data['email'] = $postData['email'];
                $data['password'] = $this->getToken(8);
                
                $userentity = $this->Users->newEntity();
                $userData = $this->Users->patchEntity($userentity, $newData, ['associated' => ['UserProfiles']]);
                //echo '<pre>'; print_r($userData->errors()); die;
                //email sent mail to email and password
                if($this->Users->save($userData)){
					//last insert id
                  $userId = $userData->id;
                  //active user automatically
                  $updatePassword = $this->Users->get($userId);
                  $updatePassword->status = 1;
                  $updatePassword->password = $data['password'];
                  $this->Users->save($updatePassword);
					echo 1;
                  }
                  exit;
                }
              }

        /**
        * getToken Method
        *
        * @access protected
        */
        //function to getToken
        protected function getToken($length) {
          $token = "";
          $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
          $codeAlphabet .= "abcdefghijklmnopqrstuvwxyz";
          $codeAlphabet .= "0123456789";
          $max = strlen($codeAlphabet); // edited
          for ($i = 0; $i < $length; $i++) {
            $token .= $codeAlphabet[$this->crypto_rand_secure(0, $max - 1)];
          }
          return $token;
        }

         //function to crypto_rand_secure
        protected function crypto_rand_secure($min, $max) {
          $range = $max - $min;
          if ($range < 1)
          return $min; // not so random...
          $log = ceil(log($range, 2));
          $bytes = (int) ($log / 8) + 1; // length in bytes
          $bits = (int) $log + 1; // length in bits
          $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
          do {
          $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
          $rnd = $rnd & $filter; // discard irrelevant bits
          } while ($rnd > $range);
          return $min + $rnd;
        }

      /**
      * check email exist Method via remote
      *
      * @access public
      */
      public function chkemailexist(){
       $email =  $this->request->getQuery('email');
        $users = $this->Users->find()->where(['Users.email' => $email, 'Users.user_type' => 3])->first();
        if(!empty($users)){
            echo 'false';
        }else{
            echo 'true';
        }
        exit();
      }

      /**
      * check phone number exist Method
      *
      * @access public
      */
      public function chkphoneexist(){
        //echo '<pre>'; print_r($_REQUEST); die;
        $userprofiles = TableRegistry::get('UserProfiles');
       $phone_number =  $this->request->getQuery('phone_number');
        $userprofiles = $userprofiles->find()->where(['UserProfiles.phone_number' => $phone_number])->first();
        if(!empty($userprofiles)){
            echo 'false';
        }else{
            echo 'true';
        }
        exit();
      }

      /**
      * check phone number exist Method
      *
      * @access public
      */
      public function adminchkphoneexist(){
        $userprofiles = TableRegistry::get('UserProfiles');
        $phone_number =  $this->request->getQuery('user_profile.phone_number');
        $userprofiles = $userprofiles->find()->where(['UserProfiles.phone_number' => $phone_number])->first();
        if(!empty($userprofiles)){
            echo 'false';
        }else{
            echo 'true';
        }
        exit();
      }
	   protected function findCityLatLong($address){
          $latLong = '';
          $url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($address).'&key=AIzaSyBVH6kKYp1xlkbchYDUDvdTDziCJKIi6Sg&sensor=false';
          $response = @file_get_contents($this->myUrlEncode($url));
          $data = json_decode($response);
          //echo '<pre>'; print_r($data); die;
          if($data->status == 'OK'){
            $lat = $data->results[0]->geometry->location->lat ;
            $long = $data->results[0]->geometry->location->lng;
            $latLong = $lat." , ".$long;
          }
            return $latLong;
        }
		protected function myUrlEncode($string) {
          $entities = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');
          $replacements = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");
          return str_replace($entities, $replacements, urlencode($string));
        }
    }