<?php
namespace App\Model\Table;


use Cake\ORM\Table;
use Cake\Validation\Validator;

class UserFavouritesTable extends Table
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

    }
}