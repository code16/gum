<?php

namespace Code16\Gum\Models;

use Code16\Gum\Models\Utils\WithMenuTitle;
use Code16\Gum\Models\Utils\WithUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Laravel\Scout\Searchable;

class Section extends Model
{
    use WithUuid, WithMenuTitle, Searchable;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    public function scopeUnattached(Builder $query): void
    {
        $query->whereNotExists(function($query) {
            return $query->from("content_urls")
                ->where("content_type", Section::class)
                ->whereRaw("content_id = sections.id");
        });
    }

    public function scopeDomain(Builder $query, $domain): void
    {
        if($domain) {
            $query->where("domain", $domain);
        }
    }

    public function scopeHome(Builder $query): void
    {
        $query->where("slug", "");
    }

    public function tileblocks(): HasMany
    {
        return $this->hasMany(Tileblock::class)
            ->orderby("order");
    }

    public function getOnlineTileblocksAttribute(): Collection
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

    public function searchableAs(): string
    {
        return env('SCOUT_PREFIX') . 'content';
    }

    public function toSearchableArray(): array
    {
        return [
            "id" => $this->id,
            "type" => Section::class,
            "domain" => $this->url->domain,
            "updated_at" => $this->updated_at->timestamp,
            "title" => strip_tags($this->title),
            "text" => strip_tags($this->heading_text),
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return config("gum.scout_enabled")
            && !$this->isHome()
            && $this->url
            && $this->url->isVisible()
            && $this->url->isPublished();
    }
}
