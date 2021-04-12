<?php

namespace Code16\Gum\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Scout\Searchable;

class News extends Model
{
    use Searchable;

    protected $guarded = [];
    protected $table = "news";
    protected $dates = ["created_at", "updated_at", "published_at"];

    public function scopeForTags(Builder $query, Collection $tags = null)
    {
        $query->whereHas("tags", function ($query) use ($tags) {
            return $query->whereIn("tags.id", $tags->pluck('id'));
        });
    }

    public function scopeForAllTags(Builder $query, Collection $tags = null)
    {
        $tags->each(function($tag) use ($query) {
            return $query->whereHas("tags", function ($query) use ($tag) {
                return $query->where("tags.id", $tag->id);
            });
        });
    }

    public function scopeForTag(Builder $query, string $tagName)
    {
        $this->scopeForTags($query, collect([Tag::where("name", $tagName)->first()]));
    }

    public function scopePublished(Builder $query, Carbon $date = null)
    {
        $query->where("published_at", "<=", $date ?: Carbon::now());
    }

    public function scopeNewest(Builder $query)
    {
        if(config("database.default") == 'mysql') {
            $query->orderByRaw(DB::raw("(ABS(DATEDIFF(NOW(), news.published_at))+1) * news.importance ASC"));
        } else {
            $query->orderBy("news.published_at", "desc");
        }
    }

    public function scopeOnline(Builder $query)
    {
        $query->where("visibility", "ONLINE");
    }

    public function visual(): MorphOne
    {
        return $this->morphOne(Media::class, "model")
            ->where("model_key", "visual");
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Media::class, "model")
            ->where("model_key", "attachments")
            ->orderBy("order");
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, "taggable");
    }

    public function getDefaultAttributesFor($attribute): array
    {
        return in_array($attribute, ["visual", "attachments"])
            ? ["model_key" => $attribute]
            : [];
    }

    public function isVisible(): bool
    {
        return $this->visibility == "ONLINE";
    }

    public function isPublished(): bool
    {
        return $this->published_at <= Carbon::now();
    }

    public function hasTag(string $tagName): bool
    {
        return $this->tags()->where("name", $tagName)->count() == 1;
    }

    public function toSearchableArray()
    {
        return [
            "id" => $this->id,
            "surtitle" => strip_tags($this->surtitle),
            "title" => strip_tags($this->title),
            "heading_text" => strip_tags($this->heading_text),
            "text" => gum_markdown($this->body_text),
        ];
    }

    public function shouldBeSearchable()
    {
        return config("gum.scout_enabled") && $this->isVisible() && $this->isPublished();
    }
}
