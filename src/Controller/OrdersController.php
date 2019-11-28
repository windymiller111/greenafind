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
use Cake\Mailer\Email;
use Cake\Event\Event;
use Cake\ORM\Query;
use Cake\I18n\Time;
/**
 * Static content controller
 *
 * This controller will render views from Template/Pages/
 *
 * @link https://book.cakephp.org/3.0/en/controllers/pages-controller.html
 */
class OrdersController extends AppController
{
        public $carts;
        public $cartitems;
      public function initialize(){
         parent::initialize();
         $this->Auth->allow('deleteOrdersByCron');
         $this->viewBuilder()->setLayout('');
         $this->carts = TableRegistry::get('Carts');
         $this->cartitems = TableRegistry::get('CartItems');
       }
	   public function deleteOrdersByCron(){
        $this->viewBuilder()->setLayout('');
         $orders = $this->Orders->find()->select(['Orders.id', 'Orders.user_id', 'Orders.cart_id', 'Orders.order_status','Orders.order_date'])
          ->where(['Orders.order_status IN (2,3)'])
          ->order(['Orders.id' => 'DESC']);
          //check past orders data
          if(!$orders->isEmpty()){
			  $orders = $orders->toArray();
            foreach ($orders as $key => $ordersData) {
             $orderdateTime = $ordersData['order_date']->format('Y-m-d h:i:s A');
             $Orderdate = date('Y-m-d', strtotime($orderdateTime));
              $date = date("Y-m-d", strtotime('-2 weeks'));
              if($date > $Orderdate){
                $orderId[] = $ordersData['id'];
                $cartId[] = $ordersData['cart_id'];
              }
            }
          }
		  //check the order Id
            if(!empty($orderId)){
              //delete the orders
              $delete = $this->Orders->deleteAll(['Orders.id IN' => $orderId]);
            }
            if(!empty($cartId)){
              //delete the cartItems as well as carts
              $delete = $this->carts->deleteAll(['Carts.id IN' => $cartId]);
              $delete = $this->cartitems->deleteAll(['CartItems.cart_id IN' => $cartId]);
            }
          }
        }