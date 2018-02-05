<?php

namespace Code16\Gum\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $guarded = [];

    /**
     * @param Builder $query
     * @param string $type
     * @return Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('model_type', $type);
    }

    public function model()
    {
        return $this->morphTo('model');
    }
}
