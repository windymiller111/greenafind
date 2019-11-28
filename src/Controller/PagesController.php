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

use Cake\Core\Configure;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Exception\NotFoundException;
use Cake\View\Exception\MissingTemplateException;


use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
/**
 * Static content controller
 *
 * This controller will render views from Template/Pages/
 *
 * @link https://book.cakephp.org/3.0/en/controllers/pages-controller.html
 */
class PagesController extends AppController
{

public function initialize()
    {
         parent::initialize();
         $this->Auth->allow();
       $this->viewBuilder()->setLayout('front');
        
    }
    /**
     * Displays a view
     *
     * @param array ...$path Path segments.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Http\Exception\ForbiddenException When a directory traversal attempt.
     * @throws \Cake\Http\Exception\NotFoundException When the view file could not
     *   be found or \Cake\View\Exception\MissingTemplateException in debug mode.
     */
    public function display(...$path)
    {
        $count = count($path);
        if (!$count) {
            return $this->redirect('/');
        }
        if (in_array('..', $path, true) || in_array('.', $path, true)) {
            throw new ForbiddenException();
        }
        $page = $subpage = null;

        if (!empty($path[0])) {
            $page = $path[0];
        }
        if (!empty($path[1])) {
            $subpage = $path[1];
        }
        $this->set(compact('page', 'subpage'));

        try {
            $this->render(implode('/', $path));
        } catch (MissingTemplateException $exception) {
            if (Configure::read('debug')) {
                throw $exception;
            }
            throw new NotFoundException();
        }
    }

    public function termsOfServices(){$this->viewBuilder()->setLayout('');}

   /* public function payment($id = null){
        $user_id = base64_decode($id);
        //echo $user_id; die;
        $this->viewBuilder()->setLayout('');
        $payment_subscriptions = TableRegistry::get('PaymentSubscriptions');
        $subscriptions = $payment_subscriptions->find()->toArray();
        //echo '<pre>'; print_r($subscriptions); die;
        $this->set(compact('subscriptions', 'user_id'));

    }*/

   /* public function pay($id = null){
        $this->viewBuilder()->setLayout('');
        //echo 'check'; die;
        //$abc = ['ram', 'shaym'];
         //file_put_contents('/var/www/html/greenwarrior/webroot/check.txt', print_r($_REQUEST, true)); die;
        //echo '<pre>'; print_r($_POST); die;
         file_put_contents('/var/www/html/greenwarrior/webroot/check.txt', print_r($_REQUEST, true), FILE_APPEND);
         die;
        //ile_put_contents('/var/www/html/greenwarrior/webroot/check.txt', '--------------------1----------------', FILE_APPEND); die;
    }*/

     /*public function cancel($id = null){$this->viewBuilder()->setLayout('');}*/
     /* public function success($id = null){
        $this->viewBuilder()->setLayout('');
        echo "====="; 
        echo '<pre>'; print_r($_POST);
        file_put_contents('/var/www/html/greenwarrior/webroot/check.txt', print_r($_REQUEST, true), FILE_APPEND);
        file_put_contents('/var/www/html/greenwarrior/webroot/check.txt', '--------------------1----------------', FILE_APPEND); die;
    }*/

}