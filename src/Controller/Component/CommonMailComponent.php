<?php
namespace App\Controller\Component;

use Cake\Controller\Component;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Mailer\Email;

class CommonMailComponent extends Component{
/**
* This function is used to upload image into server
*
* @access public
*
* @param array $imageArr
* @return array
*/

	public function sendMail($template = null, $subject = null, $newData = array(), $emailData = null) {
		$email = new Email();
		$email
		->template($template)
		->subject($subject)
		->emailFormat('both')
		->viewVars($newData)
		->to($emailData)
		->from('support@greenafind.com')
		->send();
		return true;
	}
}