<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class OrdersTable extends Table
{
	public function initialize(array $config)
	{
		$this->addBehavior('Timestamp');
		
		/*$this->hasOne('Carts', [
            'foreignKey' => 'id'
        ]);*/

        $this->belongsTo('Users', [
            'foreignKey' => 'user_id'
        ]);

        $this->belongsTo('Carts', [
            'foreignKey' => 'cart_id'
        ]);


	}

}