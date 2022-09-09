<?php

namespace Modules\Account\Infrastructure\Adapter\Out\Persistence\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Account\Infrastructure\Adapter\Out\Persistence\Factories\ActivityModelFactory;

/**
 * Modules\Account\Infrastructure\Adapter\Out\Persistence\Models\Activity.
 *
 * @property int $id
 * @property int $owner_account_id
 * @property int $source_account_id
 * @property int $target_account_id
 * @property int $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Modules\Account\Infrastructure\Adapter\Out\Persistence\Models\AccountModel|null $account
 * @method static \Modules\Account\Infrastructure\Adapter\Out\Persistence\Factories\ActivityModelFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityModel newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityModel newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ActivityModel query()
 * @mixin \Eloquent
 */
class ActivityModel extends Model
{
    use HasFactory;

    protected $table = 'activities';

    protected $guarded = [];

    protected static function newFactory(): ActivityModelFactory
    {
        return ActivityModelFactory::new();
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(AccountModel::class, 'owner_account_id', 'id');
    }
}
