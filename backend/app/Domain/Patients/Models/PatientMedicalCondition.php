<?php

namespace App\Domain\Patients\Models;

use App\Models\User;
use App\Shared\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientMedicalCondition extends Model
{
    use BelongsToTenant;
    use HasUlids;

    protected $fillable = ['tenant_id', 'patient_id', 'medical_condition_id', 'notes', 'recorded_by', 'recorded_at'];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
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
     * @return BelongsTo<MedicalCondition, $this>
     */
    public function condition(): BelongsTo
    {
        return $this->belongsTo(MedicalCondition::class, 'medical_condition_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
