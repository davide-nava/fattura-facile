<?php

namespace App\Modules\Tenancy\Scopes;

use App\Modules\Tenancy\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        /** @var TenantContext $context */
        $context = app(TenantContext::class);

        if ($context->isBypassingIsolation()) {
            return;
        }

        $tenantId = $context->getTenantId();

        if ($tenantId !== null) {
            $builder->where($model->qualifyColumn('tenant_id'), $tenantId);
        } else {
            // If running in a tenant-required environment without a tenant context, prevent accidental data leaks!
            if (! app()->runningInConsole() || app()->runningUnitTests()) {
                $builder->whereRaw('1 = 0');
            }
        }
    }
}
