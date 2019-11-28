<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class RatingsTable extends Table
{
	public function initialize(array $config)
	{
		$this->addBehavior('Timestamp');
		
	/*	$this->belongsTo('Users', [
            'foreignKey' => 'to_id'
        ]);

        $this->belongsTo('UserProfiles', [
            'foreignKey' => 'to_id'
        ]);*/

        


	}



}