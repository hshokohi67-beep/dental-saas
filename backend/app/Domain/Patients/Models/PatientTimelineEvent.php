<?php

namespace App\Domain\Patients\Models;

use App\Models\User;
use App\Shared\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientTimelineEvent extends Model
{
    use BelongsToTenant;
    use HasUlids;

    public const TYPE_CREATED = 'created';

    public const TYPE_UPDATED = 'updated';

    public const TYPE_MEDICAL_CONDITION_ADDED = 'medical_condition_added';

    public const TYPE_MEDICAL_CONDITION_REMOVED = 'medical_condition_removed';

    public const TYPE_ALLERGY_ADDED = 'allergy_added';

    public const TYPE_ALLERGY_REMOVED = 'allergy_removed';

    public const TYPE_MEDICATION_ADDED = 'medication_added';

    public const TYPE_MEDICATION_UPDATED = 'medication_updated';

    public const TYPE_MERGED_FROM = 'merged_from';

    public const TYPE_MERGED_INTO = 'merged_into';

    protected $fillable = ['tenant_id', 'patient_id', 'type', 'description', 'metadata', 'recorded_by', 'occurred_at'];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
