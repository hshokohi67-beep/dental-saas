<?php

namespace App\Domain\Tenancy\Models;

use App\Domain\Platform\Models\Plan;
use App\Domain\Platform\Models\Subscription;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

/**
 * @property-read Subscription|null $subscription
 */
class Tenant extends Model
{
    use HasUlids;

    protected $fillable = ['name', 'slug', 'status'];

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function mainBranch(): HasOne
    {
        return $this->hasOne(Branch::class)->where('is_main', true);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    public function plan(): HasOneThrough
    {
        return $this->hasOneThrough(Plan::class, Subscription::class, 'tenant_id', 'id', 'id', 'plan_id');
    }
}
