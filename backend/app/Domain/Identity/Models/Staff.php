<?php

namespace App\Domain\Identity\Models;

use App\Domain\Tenancy\Models\Branch;
use App\Domain\Tenancy\Models\Tenant;
use App\Models\User;
use App\Shared\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read User $user
 */
class Staff extends Model
{
    use BelongsToTenant;
    use HasUlids;

    protected $fillable = ['tenant_id', 'user_id', 'branch_id', 'title', 'status'];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
