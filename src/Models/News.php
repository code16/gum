<?php

namespace Code16\Gum\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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

        return $query->select("news.*")
            ->join('taggables', function ($join) {
                $join->on('news.id', '=', 'taggables.taggable_id')
                    ->where('taggables.taggable_type', News::class);
            })
            ->whereIn("taggables.tag_id", $tags->pluck("id"));
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
     * @param Carbon|null $date
     * @return Builder
     */
    public function scopePublished(Builder $query, Carbon $date = null)
    {
        return $query->where("published_at", "<=", $date ?: Carbon::now());
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeNewest(Builder $query)
    {
        if(config("database.default") == 'mysql') {
            return $query->orderByRaw(DB::raw("(ABS(DATEDIFF(NOW(), news.published_at))+1) * news.importance ASC"));
        }

        return $query->orderBy("news.published_at", "desc");
    }

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeOnline(Builder $query)
    {
        return $query->where("visibility", "ONLINE");
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

    /**
     * @return bool
     */
    public function isVisible(): bool
    {
        return $this->visibility == "ONLINE";
    }

    /**
     * @return bool
     */
    public function isPublished(): bool
    {
        return $this->published_at <= Carbon::now();
    }

    /**
     * @param string $tagName
     * @return bool
     */
    public function hasTag(string $tagName)
    {
        return $this->tags()->where("name", $tagName)->count() == 1;
    }

    /**
     * @return string
     */
    public function getUrlAttribute()
    {
        return route("news.article", ["news" => $this->id, "slug" => str_slug($this->title)]);
    }
}
