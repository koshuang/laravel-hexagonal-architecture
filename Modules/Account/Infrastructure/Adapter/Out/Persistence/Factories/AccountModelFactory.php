<?php

namespace Modules\Account\Infrastructure\Adapter\Out\Persistence\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Account\Infrastructure\Adapter\Out\Persistence\Models\AccountModel;

class AccountModelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<AccountModel>
     */
    protected $model = AccountModel::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            //
        ];
    }
}
