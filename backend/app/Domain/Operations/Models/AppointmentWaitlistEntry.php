<?php

namespace App\Domain\Operations\Models;

use App\Domain\Dental\Models\DentalConditionCatalog;
use App\Domain\Identity\Models\Staff;
use App\Domain\Patients\Models\Patient;
use App\Domain\Tenancy\Models\Branch;
use App\Shared\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentWaitlistEntry extends Model
{
    use BelongsToTenant;
    use HasUlids;

    protected $table = 'appointment_waitlist';

    public const STATUS_WAITING = 'waiting';

    public const STATUS_NOTIFIED = 'notified';

    public const STATUS_BOOKED = 'booked';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'tenant_id', 'branch_id', 'patient_id', 'staff_id', 'dental_condition_catalog_id',
        'preferred_from', 'preferred_until', 'status', 'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'preferred_from' => 'date',
            'preferred_until' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Branch, $this>
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * @return BelongsTo<Patient, $this>
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * @return BelongsTo<Staff, $this>
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * @return BelongsTo<DentalConditionCatalog, $this>
     */
    public function dentalConditionCatalog(): BelongsTo
    {
        return $this->belongsTo(DentalConditionCatalog::class);
    }
}
