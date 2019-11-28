<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class CartItemsTable extends Table
{
	public function initialize(array $config)
	{
		$this->addBehavior('Timestamp');

        $this->belongsTo('Carts ', [
            'foreignKey' => 'cart_id'
        ]);
		
		/*$this->belongsTo('Carts	', [
            //'foreignKey' => 'id'
        ]);*/

       /* $this->hasOne('MenuItems', [
            'foreignKey' => 'id',
            //'joinType' => 'LEFT'
        ]);*/

         $this->belongsTo('MenuItems', [
            'foreignKey' => 'menu_id'
        ]);

	}

}