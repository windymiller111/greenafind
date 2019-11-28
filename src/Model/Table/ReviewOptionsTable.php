<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class ReviewOptionsTable extends Table
{
	public function initialize(array $config)
	{
		$this->addBehavior('Timestamp');
		
		$this->belongsTo('ReviewQuestions', [
            'foreignKey' => 'ques_id'
        ]);
		
		$this->hasOne('Reviews', [
            'foreignKey' => 'id',
        ]);
	}
}