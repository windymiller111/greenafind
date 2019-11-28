<?php
namespace App\View\Helper;
use Cake\View\Helper;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
class CommonHelper extends Helper {
    /**
     * Get rest_ratings.
     *
     * @access public
     *
     * @return json object
     */
    public function getRatings($restaurant_id = null) {
        $ratings = TableRegistry::get('Ratings');
        $query = $ratings->find();
        $ratingAverage = $query->select(['averagerating' => $query->func()->avg('rating'), 'totalcount' => $query->func()->count('user_id'), 'to_id'])->where(['to_id' => $restaurant_id])
        ->group('to_id')->toArray();
        $newData  = [];
		if(!empty($ratingAverage)){
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
	  
	   public function checkOrder($to_id, $order_status) {
          $orders = TableRegistry::get('Orders');
          $ids = array($order_status, 2);
          $ordersData = $orders->find()
          ->where(['Orders.to_id' => $to_id, 'Orders.order_status NOT IN' => $ids])
          ->count();
			return $ordersData;
        }

      /*public function checkPayment($user_id = null) {
          $userpayments = TableRegistry::get('UserPayments');
          $usersPayment = $userpayments->find()->where(['UserPayments.user_id' => $user_id])
          ->order(['UserPayments.id' => 'DESC'])
          ->limit(1)
          ->toArray();
          return $usersPayment;
      }*/
  }