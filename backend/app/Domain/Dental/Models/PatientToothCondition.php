<?php

namespace App\Domain\Dental\Models;

use App\Domain\Patients\Models\Patient;
use App\Models\User;
use App\Shared\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read DentalConditionCatalog $condition
 */
class PatientToothCondition extends Model
{
    use BelongsToTenant;
    use HasUlids;

    protected $fillable = [
        'tenant_id', 'patient_id', 'dental_condition_catalog_id', 'scope_type',
        'tooth_number', 'quadrant', 'arch', 'surfaces', 'notes', 'recorded_by', 'recorded_at',
        'voided_at', 'voided_by',
    ];

    protected function casts(): array
    {
        return [
            'surfaces' => 'array',
            'recorded_at' => 'datetime',
            'voided_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Patient, $this>
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * @return BelongsTo<DentalConditionCatalog, $this>
     */
    public function condition(): BelongsTo
    {
        return $this->belongsTo(DentalConditionCatalog::class, 'dental_condition_catalog_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function isVoided(): bool
    {
        return $this->voided_at !== null;
    }

    /**
     * @param  Builder<PatientToothCondition>  $query
     * @return Builder<PatientToothCondition>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('voided_at');
    }
}
