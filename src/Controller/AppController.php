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
namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Event\Event;

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
        $this->loadComponent('Auth', [
        // Added this line
        'authorize'=> 'Controller',
        'authenticate' => [
            'Form' => [
                'fields' => [
                    'username' => 'email',
                    'password' => 'password'
                ]
            ]
        ],
        'loginRedirect' => [
            'controller' => 'Users',
            'action' => 'dashboard'
        ],
        'loginAction' => [
            'controller' => 'Users',
            'action' => 'login'
        ],
         // If unauthorized, return them to page they were just on
        'unauthorizedRedirect' => $this->referer()
    ]);

    // Allow the display action so our pages controller
    // continues to work. Also enable the read only actions.
    //$this->Auth->allow(['display', 'view', 'index']);

        /*
         * Enable the following component for recommended CakePHP security settings.
         * see https://book.cakephp.org/3.0/en/controllers/components/security.html
         */
        //$this->loadComponent('Security');
    }

     public function isAuthorized($user)
     {
        // Admin can access every action
        if (isset($user['user_type']) && $user['user_type'] === 1) {
            $this->viewBuilder()->setLayout('admindefault');
            return true;
        }
        // Default deny
        return false;
    }

     public function beforeFilter(Event $event){
        parent::beforeFilter($event);
        $this->Auth->allow(['login', 'pay', 'payment', 'cancel', 'success', 'paymentRequest', 'InactiveUserByCron', 'userPayment', 'deleteOrdersByCron']);
    }

      /**
      * resize Image.
      *
      * @access public
      */
        //function to resize image
      public function resize($newHeight, $newWidth, $targetFile, $originalFile) {
        //echo file_get_contents($originalFile); die;
        $info = getimagesize($originalFile);
        $mime = $info['mime'];

          switch ($mime) {
          case 'image/jpeg':
          $image_create_func = 'imagecreatefromjpeg';
          $image_save_func = 'imagejpeg';
          $new_image_ext = 'jpeg';
          break;

          case 'image/jpg':
          $image_create_func = 'imagecreatefromjpg';
          $image_save_func = 'imagejpg';
          $new_image_ext = 'jpg';
          break;

          case 'image/png':
          $image_create_func = 'imagecreatefrompng';
          $image_save_func = 'imagepng';
          $new_image_ext = 'png';
          break;

          case 'image/gif':
          $image_create_func = 'imagecreatefromgif';
          $image_save_func = 'imagegif';
          $new_image_ext = 'gif';
          break;

          default: 
          throw Exception('Unknown image type.');
        }
        $img = $image_create_func($originalFile);
        //$size = getimagesize($originalFile);
        list($orig_width, $orig_height) = getimagesize($originalFile);
        
        $width = $orig_width;
        $height = $orig_height;
        $max_height = $newHeight;
        $max_width = $newWidth;
        
        # taller
        if ($height > $max_height) {
          $width = ($max_height / $height) * $width;
        //echo '<br>';
          $height = $max_height;
        }

        # wider
        if ($width > $max_width) {
          $height = ($max_width / $width) * $height;
          $width = $max_width;
        }

        //$newHeight = ($height / $width) * $newWidth;
        $tmp = imagecreatetruecolor($width, $height);
        imagealphablending($tmp, false);
        imagesavealpha($tmp,true);
        imagecopyresampled($tmp, $img, 0, 0, 0, 0, $width, $height, $orig_width, $orig_height);

        if (file_exists($targetFile)) {
        unlink($targetFile);
        }
        $image_save_func($tmp, "$targetFile");
      }
    }