<?php

namespace Code16\Gum\Models\Utils;

use Webpatser\Uuid\Uuid;

trait WithUuid
{

    /**
     * Boot function from laravel.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = Uuid::generate()->string;
        });
    }
}