<?php

namespace Code16\Gum\Models;

use Illuminate\Database\Eloquent\Model;

class Sidepanel extends Model
{
    protected $guarded = [];

    public function container()
    {
        return $this->morphTo();
    }

    public function relatedContent()
    {
        return $this->morphTo('related_content');
    }

    public function visual()
    {
        return $this->morphOne(Media::class, "model")
            ->where("model_key", "visual");
    }

    public function downloadableFile()
    {
        return $this->morphOne(Media::class, "model")
            ->where("model_key", "downloadableFile");
    }

    public function getDefaultAttributesFor($attribute)
    {
        return in_array($attribute, ["visual", "downloadableFile"])
            ? ["model_key" => $attribute]
            : [];
    }
}
