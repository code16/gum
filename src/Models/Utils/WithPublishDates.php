<?php

namespace Code16\Gum\Models\Utils;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

trait WithPublishDates
{

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopePublished($query)
    {
        $now = Carbon::now();

        $query->where(function($query) use($now) {
            $query->orWhere("published_at", "<=", $now)
                ->orWhereNull("published_at");

        })->where(function($query) use($now) {
            $query->orWhere("unpublished_at", ">", $now)
                ->orWhereNull("unpublished_at");
        });

        return $query;
    }
}