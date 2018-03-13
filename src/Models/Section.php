<?php

namespace Code16\Gum\Models;

use Code16\Gum\Models\Utils\WithMenuTitle;
use Code16\Gum\Models\Utils\WithUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Parsedown;

class Section extends Model
{
    use WithUuid, WithMenuTitle, Searchable;

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

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeHome(Builder $query)
    {
        return $query->where("slug", "");
    }

    public function tileblocks()
    {
        return $this->hasMany(Tileblock::class)
            ->orderby("order");
    }

    public function getOnlineTileblocksAttribute()
    {
        return $this
            ->tileblocks()
            ->with("tiles", "tiles.contentUrl")
            ->get()
            ->filter(function(Tileblock $tileblock) {
                $tileblock->tiles = $tileblock->tiles
                    ->filter(function(Tile $tile) {
                        if(!$tile->contentUrl) {
                            return $tile->isVisible() && $tile->isPublished();
                        }

                        return $tile->contentUrl->isVisible() && $tile->contentUrl->isPublished();
                    });

                return count($tileblock->tiles);
            });
    }

    public function url()
    {
        return $this->morphOne(ContentUrl::class, "content");
    }

    public function sidepanels()
    {
        return $this->morphMany(Sidepanel::class, "container")
            ->orderBy("order");
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, "taggable");
    }

    /**
     * @return bool
     */
    public function isHome()
    {
        return $this->slug == "";
    }

    /**
     * Get the index name for the model.
     *
     * @return string
     */
    public function searchableAs()
    {
        return env('SCOUT_PREFIX') . 'content';
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        return [
            "type" => "section",
            "depth" => $this->url->depth,
            "title" => $this->title,
            "group" => "",
            "text" => (new Parsedown)->text($this->heading_text),
            "_tags" => $this->domain,
            "url" => $this->url->uri
        ];
    }

    /**
     * @return bool
     */
    public function shouldBeSearchable()
    {
        return $this->url && $this->url->isVisible()
            && ($this->is_root || $this->url->isPublished());
    }
}
