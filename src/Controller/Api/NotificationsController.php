<?php
namespace App\Controller\Api;

use App\Controller\Api\AppController;
use Cake\Event\Event;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Mailer\Email;
use Cake\Routing\Router;
use Cake\Validation\Validation;

class NotificationsController extends AppController{

	public function initialize() {
        parent::initialize();  
        $this->loadComponent('RequestHandler');
    }

		/**
		* notification_list.
		* @access public
		* @param type currentUser
		* @return json object
		*/
		public function notificationList() {
			$current_user = $this->currentUser;
            $current_user_id = $this->currentUser['user']['id'];
            	if ($this->request->is('post')) {
            		$postData = array(); 
					$postData = $this->request->getData();
					extract($postData);
					$notificationsData = $this->Notifications->find()->where(['receiver' => $current_user_id, 'notificationStatus' => '0'])->order(['id' => 'DESC']);
					if(!$notificationsData->isEmpty()){
						$notificationsData = $notificationsData->toArray();
						foreach ($notificationsData as $key => $value) {
							$data = [];
							$data['notificationId'] = $value['id'];
							$data['notificationType'] = $value['notificationType'];
							$data['notificationStatus'] = $value['notificationStatus'];
							$data['message'] = $value['message'];
							$data['created'] = $value['created']->format('h:i A');
							$newData[] = $data;
						}
						return $this->_returnJson(true, 'Notifications list.', $newData);
					}else{return $this->_returnJson(false, 'No records found.');}
				}else{return $this->_returnJson(false, 'Invalid Request.');}
			}

		/**
		* read_notification.
		* @access public
		* @param type currentUser
		* @return json object
		*/

        public function notificationRead() {
        	$current_user = $this->currentUser;
        	$current_user_id = $this->currentUser['user']['id'];
        	if ($this->request->is('post')) {
				$postData = array(); 
				$postData = $this->request->getData();
				extract($postData);
				$id = isset($id) ? $id : '';
				if (empty($id)) {return $this->_returnJson(false, 'Please enter notification id.');}
				//update the notification status
				 $this->Notifications->updateAll(array("notificationStatus" => '1'), array("id" => $id));
        		return $this->_returnJson(true, 'Notification read successfully.');
        	}else{
        		return $this->_returnJson(false, 'Invalid Request.');
        	}
        }
    }