<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class ReviewQuestionsTable extends Table
{
	public function initialize(array $config)
	{
		$this->addBehavior('Timestamp');
		
		$this->hasMany('ReviewOptions', [
            'foreignKey' => 'ques_id',
             'joinType' => 'INNER'
        ]);


	}

}