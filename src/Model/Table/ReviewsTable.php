<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class ReviewsTable extends Table
{
	public function initialize(array $config)
	{
		$this->addBehavior('Timestamp');
		
		$this->belongsTo('ReviewOptions', [
		'foreignKey' => 'option_id'
		]);
	}
		
}