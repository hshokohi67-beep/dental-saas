<?php

namespace App\Domain\Dental\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A shared, platform-level catalog of dental treatment/diagnostic codes —
 * not tenant data (see migration comment).
 */
class DentalConditionCatalog extends Model
{
    use HasUlids;

    protected $table = 'dental_condition_catalog';

    protected $fillable = [
        'key', 'label', 'category', 'scope', 'dentitions',
        'status_priority', 'status_color', 'status_border', 'is_active',
        'booking_eligible', 'duration_minutes', 'buffer_minutes',
    ];

    protected function casts(): array
    {
        return [
            'dentitions' => 'array',
            'is_active' => 'boolean',
            'booking_eligible' => 'boolean',
        ];
    }

    public function isApplicableToScope(string $scopeType): bool
    {
        $matching = match ($scopeType) {
            'tooth' => 'tooth',
            'quadrant' => 'half_arch',
            'arch' => 'arch',
            'whole_mouth' => 'whole_mouth',
            default => null,
        };

        return $this->scope === $matching;
    }

    public function isApplicableToDentition(string $dentition): bool
    {
        return in_array($dentition, $this->dentitions, true);
    }

    public function patientToothConditions(): HasMany
    {
        return $this->hasMany(PatientToothCondition::class);
    }
}
