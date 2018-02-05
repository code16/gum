<?php

namespace Code16\Gum\Models;

use Code16\Gum\Models\Utils\WithMenuTitle;
use Code16\Gum\Models\Utils\WithUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use WithUuid, WithMenuTitle;

    public $incrementing = false;

    protected $guarded = [];

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeUnattached(Builder $query)
    {
        return $query->whereNotExists(function($query) {
            return $query->from("content_urls")
                ->where("content_type", Section::class)
                ->whereRaw("content_id = sections.id");
        });
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeDomain(Builder $query, $domain)
    {
        if($domain) {
            return $query->where("domain", $domain);
        }

        return $query;
    }

    public function tileblocks()
    {
        return $this->hasMany(Tileblock::class)
            ->orderby("order");
    }

    public function getOnlineTileblocksAttribute()
    {
        return $this->tileblocks()
            ->published()
            ->with("tiles", "tiles.contentUrl")
            ->get()
            ->filter(function(Tileblock $tileblock) {
                $tileblock->tiles = $tileblock->tiles->filter(function(Tile $tile) {
                    return !$tile->contentUrl
                        || ($tile->contentUrl->isVisible() && $tile->contentUrl->isPublished());
                });

                return count($tileblock->tiles);
            });
    }

    public function url()
    {
        return $this->morphOne(ContentUrl::class, "content");
    }
}
