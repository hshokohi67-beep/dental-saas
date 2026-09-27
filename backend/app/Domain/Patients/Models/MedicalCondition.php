<?php

namespace App\Domain\Patients\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A shared, platform-level catalog of comorbidities and their clinical alert
 * text — not tenant data (see migration comment).
 */
class MedicalCondition extends Model
{
    use HasUlids;

    protected $fillable = ['key', 'label', 'alert_text', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function patientLinks(): HasMany
    {
        return $this->hasMany(PatientMedicalCondition::class);
    }
}
