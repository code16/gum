<?php

namespace Code16\Gum\Models\Utils;

use Illuminate\Database\Eloquent\Builder;

trait WithVisibility
{

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeVisible(Builder $query)
    {
        return $query->where("visibility", "ONLINE");
    }
}