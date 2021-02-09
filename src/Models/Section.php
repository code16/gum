<?php

namespace Code16\Gum\Models;

use Code16\Gum\Models\Utils\WithMenuTitle;
use Code16\Gum\Models\Utils\WithUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Laravel\Scout\Searchable;
use Parsedown;

class Section extends Model
{
    use WithUuid, WithMenuTitle, Searchable;

    /** @var string */
    protected $keyType = 'string';

    /** @var bool */
    public $incrementing = false;

    /** @var array */
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

    public function url(): MorphOne
    {
        return $this->morphOne(ContentUrl::class, "content");
    }

    public function sidepanels(): MorphMany
    {
        return $this->morphMany(Sidepanel::class, "container")
            ->orderBy("order");
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, "taggable");
    }

    public function isHome(): bool
    {
        return $this->slug == "";
    }

    /**
     * Get the indexable data array for the model.
     */
    public function toSearchableArray(): array
    {
        return [
            "id" => $this->id,
            "title" => strip_tags($this->title),
            "text" => (new Parsedown)->text($this->heading_text),
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return config("gum.scout_enabled")
            && $this->url
            && $this->url->isVisible()
            && (($this->is_root && !$this->isHome()) || $this->url->isPublished());
    }
}
