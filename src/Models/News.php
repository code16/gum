<?php

namespace Code16\Gum\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $guarded = [];
    protected $table = "news";
    protected $dates = ["created_at", "updated_at", "published_at"];

    public function mainVisual()
    {
        return $this->morphOne(Media::class, "model")
            ->where("model_key", "mainVisual");
    }

    public function tags()
    {
        return $this->morphMany(Tag::class, "model")
            ->orderBy("order");
    }

    public function getDefaultAttributesFor($attribute)
    {
        return in_array($attribute, ["mainVisual"])
            ? ["model_key" => $attribute]
            : [];
    }
}
