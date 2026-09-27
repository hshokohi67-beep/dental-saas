<?php

namespace App\Domain\Platform\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigurationValue extends Model
{
    protected $fillable = ['scope_type', 'scope_id', 'key', 'value'];

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }
}
