<?php

namespace App\Domain\Operations\Models;

use App\Domain\Dental\Models\DentalConditionCatalog;
use App\Domain\Identity\Models\Staff;
use App\Domain\Patients\Models\Patient;
use App\Domain\Tenancy\Models\Branch;
use App\Models\User;
use App\Shared\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class Appointment extends Model
{
    use BelongsToTenant;
    use HasUlids;

    public const STATUS_BOOKED = 'booked';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_CHECKED_IN = 'checked_in';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_NO_SHOW = 'no_show';

    /** Statuses that still occupy the calendar / count against conflicts and caps. */
    public const ACTIVE_STATUSES = [
        self::STATUS_BOOKED, self::STATUS_CONFIRMED, self::STATUS_CHECKED_IN, self::STATUS_COMPLETED,
    ];

    protected $fillable = [
        'tenant_id', 'branch_id', 'patient_id', 'staff_id', 'room_id',
        'dental_condition_catalog_id', 'treatment_plan_step_id',
        'scheduled_at', 'duration_minutes', 'status', 'notes',
        'cancelled_reason', 'checked_in_at', 'completed_at', 'cancelled_at', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'checked_in_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function dentalConditionCatalog(): BelongsTo
    {
        return $this->belongsTo(DentalConditionCatalog::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function endsAt(): Carbon
    {
        return $this->scheduled_at->clone()->addMinutes($this->duration_minutes);
    }

    public function isActive(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES, true);
    }
}
