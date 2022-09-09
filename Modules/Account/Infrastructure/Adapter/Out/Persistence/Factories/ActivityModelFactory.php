<?php

namespace Modules\Account\Infrastructure\Adapter\Out\Persistence\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Modules\Account\Infrastructure\Adapter\Out\Persistence\Models\AccountModel;
use Modules\Account\Infrastructure\Adapter\Out\Persistence\Models\ActivityModel;

/**
 * @extends Factory<ActivityModel>
 */
class ActivityModelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<ActivityModel>
     */
    protected $model = ActivityModel::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'owner_account_id' => 1,
            'source_account_id' => 1,
            'target_account_id' => 1,
            'amount' => 500,
        ];
    }

    public function out(): static
    {
        return $this->state(function (array $attributes, Model|null $ownerAccount) {
            if (! $ownerAccount) {
                $ownerAccount = AccountModel::factory()->create();
            }
            $otherAccount = AccountModel::factory()->create();

            return [
                ...$attributes,
                'owner_account_id' => $ownerAccount,
                'source_account_id' => $ownerAccount,
                'target_account_id' => $otherAccount,
            ];
        });
    }

    public function in(): static
    {
        return $this->state(function (array $attributes, Model|null $ownerAccount) {
            if (! $ownerAccount) {
                $ownerAccount = AccountModel::factory()->create();
            }
            $otherAccount = AccountModel::factory()->create();

            return [
                ...$attributes,
                'owner_account_id' => $ownerAccount,
                'source_account_id' => $otherAccount,
                'target_account_id' => $ownerAccount,
            ];
        });
    }
}
