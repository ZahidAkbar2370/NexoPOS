<?php
namespace Database\Factories;

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use App\Models\UnitGroup;
use App\Models\User;
use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitGroupFactory extends Factory
{
    protected $model    =   UnitGroup::class;

    public function definition()
    {
        return [
            'name'          =>  $this->faker->word,
            'description'   =>  $this->faker->sentence,
            'author'        =>  $this->faker->randomElement( User::get()->map( fn( $user ) => $user->id ) ),
        ];
    }
}
