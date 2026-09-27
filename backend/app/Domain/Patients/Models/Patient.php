<?php

namespace App\Domain\Patients\Models;

use App\Domain\Dental\Models\PatientToothCondition;
use App\Domain\Identity\Models\Staff;
use App\Domain\Tenancy\Models\Branch;
use App\Domain\Tenancy\Models\Tenant;
use App\Models\User;
use App\Shared\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read Tenant $tenant
 * @property-read Branch|null $branch
 * @property-read Staff|null $primaryDoctor
 */
class Patient extends Model
{
    use BelongsToTenant;
    use HasUlids;

    protected $fillable = [
        'tenant_id', 'branch_id', 'first_name', 'last_name', 'mobile',
        'national_id', 'gender', 'date_of_birth', 'notes', 'status',
        'merged_into_id', 'created_by', 'chart_mode', 'primary_doctor_staff_id',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * @return BelongsTo<Branch, $this>
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<Patient, $this>
     */
    public function mergedInto(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'merged_into_id');
    }

    /**
     * @return HasMany<Patient, $this>
     */
    public function mergedFrom(): HasMany
    {
        return $this->hasMany(Patient::class, 'merged_into_id');
    }

    /**
     * @return HasMany<PatientMedicalCondition, $this>
     */
    public function medicalConditions(): HasMany
    {
        return $this->hasMany(PatientMedicalCondition::class);
    }

    /**
     * @return HasMany<PatientAllergy, $this>
     */
    public function allergies(): HasMany
    {
        return $this->hasMany(PatientAllergy::class);
    }

    /**
     * @return HasMany<PatientMedication, $this>
     */
    public function medications(): HasMany
    {
        return $this->hasMany(PatientMedication::class);
    }

    /**
     * @return HasMany<PatientTimelineEvent, $this>
     */
    public function timelineEvents(): HasMany
    {
        return $this->hasMany(PatientTimelineEvent::class)->latest('occurred_at');
    }

    /**
     * @return BelongsTo<Staff, $this>
     */
    public function primaryDoctor(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'primary_doctor_staff_id');
    }

    /**
     * @return HasMany<PatientToothCondition, $this>
     */
    public function toothConditions(): HasMany
    {
        return $this->hasMany(PatientToothCondition::class);
    }

    public function fullName(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
