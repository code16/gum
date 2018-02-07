<?php

namespace Code16\Gum\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class News extends Model
{
    protected $guarded = [];
    protected $table = "news";
    protected $dates = ["created_at", "updated_at", "published_at"];

    /**
     * @param Builder $query
     * @param Collection|null $tags
     * @return Builder
     */
    public function scopeForTags(Builder $query, Collection $tags = null)
    {
        if(!$tags) {
            return $query;
        }

        return $query->join('taggables', function ($join) {
            $join->on('news.id', '=', 'taggables.taggable_id')
                ->where('taggables.taggable_type', News::class);
        })->whereIn("taggables.tag_id", $tags->pluck("id"));
    }

    /**
     * @param Builder $query
     * @param string $tagName
     * @return Builder
     */
    public function scopeForTag(Builder $query, string $tagName)
    {
        return $this->scopeForTags($query, collect([Tag::where("name", $tagName)->first()]));
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopePublished(Builder $query)
    {
        return $query->where("published_at", "<=", Carbon::now());
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeNewest(Builder $query)
    {
        return $query->orderBy("published_at", "desc");
    }

    public function visual()
    {
        return $this->morphOne(Media::class, "model")
            ->where("model_key", "visual");
    }

    public function attachments()
    {
        return $this->morphMany(Media::class, "model")
            ->where("model_key", "attachments")
            ->orderBy("order");
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, "taggable");
    }

    public function getDefaultAttributesFor($attribute)
    {
        return in_array($attribute, ["visual", "attachments"])
            ? ["model_key" => $attribute]
            : [];
    }
}
