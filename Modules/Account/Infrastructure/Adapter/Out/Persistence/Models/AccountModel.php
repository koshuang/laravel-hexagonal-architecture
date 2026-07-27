<?php

namespace Modules\Account\Infrastructure\Adapter\Out\Persistence\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Modules\Account\Infrastructure\Adapter\Out\Persistence\Factories\AccountModelFactory;

/**
 * Modules\Account\Infrastructure\Adapter\Out\Persistence\Models\Account.
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, ActivityModel> $activityModels
 * @property-read int|null $activity_models_count
 * @method static \Modules\Account\Infrastructure\Adapter\Out\Persistence\Factories\AccountModelFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|AccountModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AccountModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AccountModel query()
 * @mixin \Eloquent
 */
class AccountModel extends Model
{
    /** @use HasFactory<AccountModelFactory> */
    use HasFactory;

    protected $table = 'accounts';

    protected $guarded = [];

    /**
     * @return HasMany<ActivityModel, $this>
     */
    public function activityModels(): HasMany
    {
        return $this->hasMany(ActivityModel::class, 'owner_account_id', 'id');
    }

    protected static function newFactory(): AccountModelFactory
    {
        return AccountModelFactory::new();
    }
}
