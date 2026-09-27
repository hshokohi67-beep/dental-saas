<?php

namespace App\Domain\Tenancy\Models;

use App\Domain\Identity\Models\Staff;
use App\Domain\Operations\Models\Room;
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

    protected static function booted(): void
    {
        // Every branch gets one default room automatically (roadmap Phase 4
        // improvement note): a single-chair clinic never has to configure
        // Room/Unit at all — shifts/appointments fall back to this silently.
        static::created(function (self $branch) {
            Room::withoutTenantScope()->create([
                'tenant_id' => $branch->tenant_id,
                'branch_id' => $branch->id,
                'name' => 'اتاق پیش‌فرض',
                'is_default' => true,
            ]);
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function defaultRoom(): ?Room
    {
        return $this->rooms()->where('is_default', true)->first();
    }
}
