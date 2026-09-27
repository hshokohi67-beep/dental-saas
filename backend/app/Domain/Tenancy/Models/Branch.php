<?php

namespace App\Domain\Tenancy\Models;

use App\Domain\Identity\Models\Staff;
use App\Shared\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read Tenant $tenant
 */
class Branch extends Model
{
    use BelongsToTenant;
    use HasUlids;

    protected $fillable = ['tenant_id', 'name', 'address', 'phone', 'is_main'];

    protected function casts(): array
    {
        return [
            'is_main' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }
}
