<?php

namespace Code16\Gum\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class News extends Model
{
    protected $guarded = [];
    protected $table = "news";
    protected $dates = ["created_at", "updated_at", "published_at"];

    public function scopeForTags($query, Collection $tags = null)
    {
        if(!$tags) {
            return $query;
        }

        return $query->join('taggables', function ($join) {
            $join->on('news.id', '=', 'taggables.taggable_id')
                ->where('taggables.taggable_type', News::class);
        })->whereIn("taggables.tag_id", $tags->pluck("id"));
    }

    public function scopePublished($query)
    {
        return $query->where("published_at", "<=", Carbon::now());
    }

    public function scopeNewest($query)
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
