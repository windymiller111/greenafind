<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class CartsTable extends Table
{
	public function initialize(array $config)
	{
		$this->addBehavior('Timestamp');
		
		/*$this->belongsTo('MenuItems', [
            'foreignKey' => 'id'
        ]);*/

        $this->hasMany('CartItems', [
            'foreignKey' => 'cart_id'
        ]);


	}
}