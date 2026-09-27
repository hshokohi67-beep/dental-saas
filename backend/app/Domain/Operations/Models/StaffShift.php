<?php

namespace App\Domain\Operations\Models;

use App\Domain\Dental\Models\DentalConditionCatalog;
use App\Domain\Identity\Models\Staff;
use App\Domain\Tenancy\Models\Branch;
use App\Shared\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A weekly-recurring availability block. `dentalConditionCatalog` null means
 * the shift is unrestricted (any bookable service); set, it means the shift
 * is dedicated to that one service only (business rule preserved from
 * legacy: "service restriction per shift"). `daily_cap` null means uncapped.
 */
class StaffShift extends Model
{
    use BelongsToTenant;
    use HasUlids;

    protected $fillable = [
        'tenant_id', 'staff_id', 'branch_id', 'room_id',
        'day_of_week', 'start_time', 'end_time',
        'dental_condition_catalog_id', 'daily_cap',
        'effective_from', 'effective_until', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'effective_from' => 'date',
            'effective_until' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Staff, $this>
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * @return BelongsTo<Branch, $this>
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * @return BelongsTo<Room, $this>
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * @return BelongsTo<DentalConditionCatalog, $this>
     */
    public function dentalConditionCatalog(): BelongsTo
    {
        return $this->belongsTo(DentalConditionCatalog::class);
    }

    public function isUnrestricted(): bool
    {
        return $this->dental_condition_catalog_id === null;
    }

    public function allowsService(string $dentalConditionCatalogId): bool
    {
        return $this->isUnrestricted() || $this->dental_condition_catalog_id === $dentalConditionCatalogId;
    }

    public function coversDate(\DateTimeInterface $date): bool
    {
        if ($this->effective_from !== null && $date < $this->effective_from) {
            return false;
        }

        if ($this->effective_until !== null && $date > $this->effective_until) {
            return false;
        }

        return true;
    }
}
