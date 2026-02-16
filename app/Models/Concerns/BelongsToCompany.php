<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToCompany
{
    protected static function bootBelongsToCompany(): void
    {
        // Avoid breaking in CLI / migrations / jobs when company_id() isn't available
        if (app()->runningInConsole()) {
            return;
        }

        static::addGlobalScope('company', function (Builder $builder) {
            // If company_id() returns null, don't apply the scope (prevents "where company_id = null")
            $cid = function_exists('company_id') ? company_id() : null;

            if (!empty($cid)) {
                $builder->where($builder->getModel()->getTable() . '.company_id', (int) $cid);
            }
        });

        static::creating(function ($model) {
            if (empty($model->company_id) && function_exists('company_id')) {
                $cid = company_id();
                if (!empty($cid)) {
                    $model->company_id = (int) $cid;
                }
            }
        });
    }

    /**
     * Explicit scope (used by controllers/services)
     * Works even if you later disable/ignore the global scope.
     */
    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->where($this->getTable() . '.company_id', $companyId);
    }

    /**
     * Optional helper: if you ever need to bypass the global company scope.
     */
    public function scopeWithoutCompany(Builder $query): Builder
    {
        return $query->withoutGlobalScope('company');
    }
}
