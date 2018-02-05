<?php

namespace Code16\Gum\Models;

use Code16\Gum\Models\Utils\WithMenuTitle;
use Code16\Gum\Models\Utils\WithUuid;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use WithUuid, WithMenuTitle;

    public $incrementing = false;

    protected $guarded = [];

    public function visual()
    {
        return $this->morphOne(Media::class, "model")
            ->where("model_key", "visual");
    }

    public function pagegroup()
    {
        return $this->belongsTo(Pagegroup::class);
    }

    public function urls()
    {
        return $this->morphMany(ContentUrl::class, "content");
    }

    public function sidepanels()
    {
        return $this->morphMany(Sidepanel::class, "container")
            ->orderBy("order");
    }

    public function getDefaultAttributesFor($attribute)
    {
        return in_array($attribute, ["visual"])
            ? ["model_key" => $attribute]
            : [];
    }
}
