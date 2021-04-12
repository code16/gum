<?php

namespace Code16\Gum\Models\Utils;

use Illuminate\Support\Str;

trait WithUuid
{
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = (string) Str::orderedUuid();
        });
    }
}