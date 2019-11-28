<?php
namespace App\Model\Table;


use Cake\ORM\Table;
use Cake\Validation\Validator;

class RestaurantPaymentsTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {

        $this->addBehavior('Timestamp');
        $this->belongsTo('Users', [
            'foreignKey' => 'restaurant_id'
        ]);
       
    }
}