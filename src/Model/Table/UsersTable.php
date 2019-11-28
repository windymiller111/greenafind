<?php

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class UsersTable extends Table
{
	public function initialize(array $config)
	{
		$this->addBehavior('Timestamp');
		
		$this->hasMany('UserTokens', [
            'foreignKey' => 'user_id'
        ]);

       /* $this->hasMany('Orders', [
            'foreignKey' => 'id'
        ]);*/
        
        $this->hasMany('Orders', [
            'foreignKey' => 'user_id',
            'joinType' => 'LEFT'
        ]);

        //check kese use krna hai double id ke sath
        /* $this->hasMany('Orders', [
            'foreignKey' => 'to_id'
        ]);*/
        
        $this->hasOne('UserProfiles', [
            'foreignKey' => 'user_id',
             'joinType' => 'LEFT'
        ]);

        $this->hasMany('RestaurantTimes', [
            'foreignKey' => 'user_id',
             'joinType' => 'LEFT'
        ]);

         $this->hasMany('MenuItems', [
            'foreignKey' => 'user_id',
             'joinType' => 'INNER'
        ]);

         $this->hasMany('Ratings', [
            'foreignKey' => 'to_id',
             'joinType' => 'LEFT'
        ]);
		
		$this->hasMany('UserPayments', [
            'foreignKey' => 'user_id'
        ]);
		
		 $this->hasMany('Reviews', [
            'foreignKey' => 'user_id',
        ]);
		
		$this->hasMany('RestaurantPayments', [
            'foreignKey' => 'restaurant_id'
        ]);


	}

	
        /*public function validationDefault(Validator $validator){
            $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

            $validator
            ->scalar('firstname')
            ->maxLength('firstname', 255)
            ->requirePresence('firstname', 'create')
            ->notEmpty('firstname');

            $validator
            ->scalar('lastname')
            ->maxLength('lastname', 255)
            ->requirePresence('lastname', 'create')
            ->notEmpty('lastname');

          $validator
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmpty('email');

            return $validator;
        }*/

        public function validationOnlyCheck(Validator $validator) {
            $validator = $this->validationDefault($validator);
            $validator->remove('firstname');
            $validator->remove('lastname');
            $validator->remove('email');
            return $validator;
        }

}