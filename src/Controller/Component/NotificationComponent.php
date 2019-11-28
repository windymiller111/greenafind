<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;

class NotificationComponent extends Component{
	//fcm tokens pushNotification
	public function pushNotification($data = array(), $message = null, $userType = null){
		//echo '<pre>'; print_r($data); die();
		$url = 'https://fcm.googleapis.com/fcm/send';
		//$data= "c9_6jEPIbQc:APA91bG0OO80cyJV_DkCanS1gNM_S83YQ26jlOoFsHC6DK_M4nUtZitPDDBkSdOJenH2NHQES30DEAuC4Ji-nPD0-8G39kkfCz4WPOscsvSYTU8E7YBkUmjRq8elASgG-a_FI-K69fQB";
		//check single and double deviec token
		if(count($data) == 1){
			$to = "to";
			$mydata =$data[0];
		}else{
			$to = "registration_ids";
			$mydata = $data;
		}
		$fields = array (
			$to => $mydata,
			'notification' => array (
            "body" => $message,
            'vibrate' => 1,
            'sound' => 1,
             'delay_while_idle' => 1,
             'cf' => '')
            );
		$fields = json_encode ( $fields );
		if($userType == 2){
			//restaurant side server key
			$headers = array (
			'Authorization: key=' . "AAAA6Z7qoME:APA91bELDPchHb7h3BFFQDkkXK_VNtuACf52MAbstDKH0YUZMzKZovGODaAtMsVDkfS3ir5y9-wJ4Y1G-eUUJZyRltgmI7Kyd8i41yk_AFEA0xmdzm4PasmNXx4PqZidHq0ZKF7zrSC5",
            'Content-Type: application/json'
            );
		}

		if($userType == 3){
			//user side server key
			$headers = array (
			'Authorization: key=' . "AAAAs5I_RlM:APA91bFyyU5djEFMvPZRtuLEWG5MgXmRrLgM0vDO-5-RkTJ1rs5jWfVriTwABk8LU2kgz5xv3mY3AiHNsRpNXWq00DnS62AtaViaXeDZnTsqu1pU1M7eDURoDpjZ6BIFRFdqLO9yi0md",
            'Content-Type: application/json'
            );
		}

		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_POST, true );
		curl_setopt ( $ch, CURLOPT_HTTPHEADER, $headers );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $fields );
		$result = curl_exec ( $ch );
			if ($result === FALSE) {
				$status = 'Curl failed: ' . curl_error($ch);
			} else {
				$status = 'success';
			}
			curl_close ( $ch );
			//echo "<pre>";
            //print_r($result); die;
		}

		/**
		* saveNotification.
		*
		* @access public
		*
		* @param user_payment_id, user_id, notificationType, message
		* @return json object
		*/
        public function saveNotification($sender = null, $receiver = null, $order_id = null, $notificationType = null, $message = null) {
        	$notifications = TableRegistry::get('notifications');
        	$newData = [];
            $newData['sender'] = $sender;
            $newData['receiver'] = $receiver;
			$newData['order_id'] = $order_id;
            $newData['notificationType'] = trim($notificationType);
            $newData['message'] = $message;
            $newentity = $notifications->newEntity();
            $notifyData = $notifications->patchEntity($newentity, $newData);
            $notifications->save($notifyData);
        }
    }