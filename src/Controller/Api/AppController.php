<?php
/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */

namespace App\Controller\Api;

use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\ORM\TableRegistry;
use Cake\Core\Configure;
/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/3.0/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{

      public $autoRender = false; // Do not render any view in any action of this controller
      public $layout = null; // Set layout to null to every action of this controller
      public $autoLayout = false; // Set to false to disable automatically rendering the layout around views.
      public $current_user = array(); // Store user data after validating the access token
      public $jsonArray = array('status' => false, 'message' => 'Something went wrong'); // Set status & message.
      public $tokensTbl;
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('Security');`
     *
     * @return void
     */

    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('RequestHandler', [
            'enableBeforeRedirect' => false,
        ]);
        $this->loadComponent('Flash');
        // table object for respective tables
        $this->tokensTbl = TableRegistry::get('UserTokens');

  
    }

    /**
     * check API's request and access token to authenticate users
     *
     * @access public
    */
    public function beforeFilter(Event $event) {
        parent::beforeFilter($event);
        // Allow user to access all action without CakePHP Auth
        
        //header
        header("Access-Control-Allow-Origin: *");
        header('Content-Type: application/json'); 
        header("Access-Control-Allow-Methods: PUT, GET, POST, OPTIONS"); 
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, authorization, Authorization");
        // Allow user to access these function without 'Authorization' header
        $allowedFunctions = array('paypalTokenData', 'oauth2accessToken', 'jsonWebToken', 'ping', 'login', 'register', 'sociallogin', 'forgotPassword', 'resetPassword', 'getCategoryList');
        // If a method doesn't exists in a class
          // If a method doesn't exists in a class
        if (!method_exists($this, $this->request->getParam('action'))) {
            header('HTTP/1.0 404 Not Found');
            exit(json_encode(array('status' => false, 'message' => 'The requested api endpoint doesn\'t exist.')));
        }
        // Process all requests
        if (!in_array($this->request->getParam('action'), $allowedFunctions)) {
        // Fetch all HTTP request headers from the current request.
            $requestHeaders = apache_request_headers();
			if (((isset($requestHeaders['Authorization']) && !empty($requestHeaders['Authorization'])) ||
                (isset($requestHeaders['authorization']) && !empty($requestHeaders['authorization'])))) {
                if (isset($requestHeaders["Authorization"]) && !empty($requestHeaders["Authorization"])) {
                    $authorizationHeader = $requestHeaders['Authorization'];
                } else if (isset($requestHeaders["authorization"]) && !empty($requestHeaders["authorization"])){
                    $authorizationHeader = $requestHeaders['authorization'];
                }

                if ($this->_authenticate($authorizationHeader) === FALSE) {
                    header('HTTP/1.0 401 Unauthorized');
                    exit(json_encode(array('status' => false, 'message' => 'No valid access token provided.')));
                }
                // check here token time expires or not ...
            } else {
                header('HTTP/1.0 401 Unauthorized');
                exit(json_encode(array('status' => false, 'message' => 'No authorization header sent.')));
            }
        }
        if ($this->request->is('post') || $this->request->is('put')) {
            array_walk_recursive($this->request->data, array($this, '_trimElementOfArray'));
        }
    }

    // Remove whitespaces from start and end of a string from element
    protected function _trimElementOfArray(&$item, &$key) {
        $item = trim($item);
    }

     /**
     * Output given data in JSON format.
     *
     * @access protected
     *
     * @param bool $status true|false
     * @param string $message
     * @param array $data The data to output in JSON format
     * @return object
     */
    protected function _returnJson($status = false, $message = null, $data = array()) {
        // Set status
        $this->jsonArray['status'] = $status ? 'success' : 'failure';
        // Set message
        $this->jsonArray['message'] = $message;
        //Set data (if any)
        if ($data) {
            // Replace all the 'null' values with blank string
            array_walk_recursive($data, array($this, '_replaceNullWithEmptyString'));
            // Remove whitespaces from start and end of a string from element
            array_walk_recursive($data, array($this, '_trimElementOfArray'));
            
            $this->jsonArray['data'] = $data;
        }
        // Set the json-encoded data to be the body
        $this->response->body(html_entity_decode(json_encode($this->jsonArray), ENT_QUOTES, 'UTF-8'));
        $this->response->statusCode(200);
        $this->response->type('application/json');
        return $this->response;
    }

     /**
     * Replace null value to blank string from all elements of associative array.
     * This function call recursively
     *
     * @access protected
     *
     * @param string|int|null $item
     * @param string $key
     * @return void
     */
    protected function _replaceNullWithEmptyString(&$item, $key) {
        if ($item === null) {
            $item = '';
        }
    }

    /**
     * Return long-lived access token.
     *
     * @access protected
     *
     * @param int $id
     * @return string $token
     */
    protected function _getAccessToken($id) {
        try {
            // Make call to "_generateAccessToken" to get long-lived access token
            $access_token = $this->_generateAccessToken($id);

            // Return the newly created access token
            return $access_token;
        }
        catch(Exception $e) {
            return $this->_returnJson(false, $e->getMessage());
        }
    }

     /**
     * Generate unique access token to access APIs
     * It uses openssl_random_pseudo_bytes & SHA1 for generating access token
     *
     * @access protected
     *
     * @param int $id
     * @return string $token
     */
    protected function _generateAccessToken($id) {
        try {
            // Generate a random token
            $token = bin2hex(openssl_random_pseudo_bytes(16)) . SHA1(($id*time()));
            return $token;
            //return $id;
        }
        catch(Exception $e) {
            return $this->_returnJson(false, $e->getMessage());
        }
    }

    /**
     * Check against the database table if the access token is valid
     *
     * @access public
     *
     * @param string $access_token
     * @return bool true|false
     */
    protected function _authenticate($access_token) {
        return $this->_validateToken($access_token);
    }

    /**
     * This function validate token send by user.
     *
     * @access protected
     *
     * @param string $access_token
     * @return bool true|false
     */
    protected function _validateToken($access_token){
        // Check if the access token exists
        //$data = $this->tokensTbl->find('all', ['conditions' => ['UserTokens.access_token =' => $access_token]])->first();
		$data = $this->tokensTbl->find()->select(['UserTokens.user_id', 'UserTokens.access_token'])->contain(['Users' =>[
                    'fields' => ['Users.id']
                ]
            ])->where(['UserTokens.access_token' => $access_token, 'Users.status' => 1])->first();
        if (!empty($data)) {
            $this->currentUser = $data->toArray();
            return true;
        }
        return false;
    }

      public function checkCurrency($currency = null){
        $curencyArr = array("USD" => "$", "POUND" => "£", "EURO" => "€", "Swiss Franc" => "CHF", "Krona" => "kr");
        if(array_key_exists($currency, $curencyArr)){
            $codekey = $curencyArr[$currency];
          }
          return $codekey;
      }
	}
