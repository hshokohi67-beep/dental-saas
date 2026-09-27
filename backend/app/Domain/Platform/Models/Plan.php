<?php

namespace App\Domain\Platform\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property array<string, int|null>|null $limits
 * @property list<string>|null $features
 */
class Plan extends Model
{
    use HasUlids;

    protected $fillable = ['key', 'name', 'limits', 'features', 'is_active'];

    protected function casts(): array
    {
        return [
            'limits' => 'array',
            'features' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->features ?? [], true);
    }

    public function limit(string $key): ?int
    {
        return $this->limits[$key] ?? null;
    }
}
