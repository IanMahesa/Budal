<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasIsDelete
{
    protected static function bootHasIsDelete(): void
    {
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where($builder->getModel()->getTable() . '.is_delete', 0);
        });

        static::creating(function ($model) {
            if ($model->is_delete === null) {
                $model->is_delete = 0;
            }
        });
    }
}