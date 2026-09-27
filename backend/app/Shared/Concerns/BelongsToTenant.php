<?php

namespace App\Shared\Concerns;

use App\Domain\Tenancy\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (TenantContext::isBypassed()) {
                return;
            }

            $builder->where($builder->getModel()->qualifyColumn('tenant_id'), TenantContext::id());
        });

        static::creating(function (self $model) {
            if ($model->tenant_id === null && TenantContext::check()) {
                $model->tenant_id = TenantContext::id();
            }
        });
    }

    public function scopeWithoutTenantScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('tenant');
    }
}
