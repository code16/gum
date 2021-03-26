<?php

namespace Code16\Gum\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Tile extends Model
{
    protected $guarded = [];

    protected $dates = ['created_at', 'updated_at', 'published_at', 'unpublished_at'];

    protected $touches = ['page'];

    public static function scopeVisible(Builder $query)
    {
        $query->where("visibility", "ONLINE");
    }

    public static function scopePublished(Builder $query)
    {
        $query
            ->where(function ($query) {
                $query
                    ->where(function ($query) {
                        $query->whereNull("published_at")
                            ->whereNull("unpublished_at");
                    })
                    ->orWhere(function ($query) {
                        $query->where("published_at", "<=", now())
                            ->where("unpublished_at", ">", now());
                    })
                    ->orWhere(function ($query) {
                        $query->where("published_at", "<=", now())
                            ->whereNull("unpublished_at");
                    })
                    ->orWhere(function ($query) {
                        $query->whereNull("published_at")
                            ->where("unpublished_at", ">", now());
                    });
            });
    }

    public function visual(): MorphOne
    {
        return $this->morphOne(Media::class, "model")
            ->where("model_key", "visual");
    }

    public function tileblock(): BelongsTo
    {
        return $this->belongsTo(Tileblock::class);
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function getUrlAttribute(): string
    {
        if($this->isFreeLink()) {
            return $this->free_link_url;
        }
        
        if($this->page) {
            return route(
                "page.show",
                implode("/", request()->segments()) . "/{$this->page->slug}"
            );
        }
        
        return "";
    }

    public function isFreeLink(): bool
    {
        return is_null($this->page_id) && strlen($this->free_link_url) > 0;
    }

    public function isVisible(): bool
    {
        return $this->visibility == "ONLINE";
    }

    public function isPublished(Carbon $date = null): bool
    {
        $now = $date ?? now();

        return (is_null($this->published_at) || $this->published_at <= $now)
            && (is_null($this->unpublished_at) || $this->unpublished_at > $now);
    }

    public function getDefaultAttributesFor($attribute): array
    {
        return in_array($attribute, ["visual"])
            ? ["model_key" => $attribute]
            : [];
    }
}
