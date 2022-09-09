<?php

namespace Modules\Account\Infrastructure\Adapter\Out\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Account\Infrastructure\Adapter\Out\Persistence\Factories\AccountModelFactory;

/**
 * Modules\Account\Infrastructure\Adapter\Out\Persistence\Models\Account.
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Modules\Account\Infrastructure\Adapter\Out\Persistence\Models\ActivityModel[] $activityModels
 * @property-read int|null $activity_models_count
 * @method static \Modules\Account\Infrastructure\Adapter\Out\Persistence\Factories\AccountFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|AccountModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AccountModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AccountModel query()
 * @mixin \Eloquent
 */
class AccountModel extends Model
{
    use HasFactory;

    protected $table = 'accounts';

    protected $guarded = [];

    protected static function newFactory(): AccountModelFactory
    {
        return AccountModelFactory::new();
    }

    public function activityModels(): HasMany
    {
        return $this->hasMany(ActivityModel::class, 'owner_account_id', 'id');
    }
}
