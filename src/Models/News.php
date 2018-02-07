<?php

namespace Code16\Gum\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $guarded = [];
    protected $table = "news";
    protected $dates = ["created_at", "updated_at", "published_at"];

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
