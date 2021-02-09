<?php

namespace Code16\Gum\Models;

use Code16\Gum\Models\Utils\WithMenuTitle;
use Code16\Gum\Models\Utils\WithUuid;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Parsedown;

class Page extends Model
{
    use WithUuid, WithMenuTitle, Searchable;

    /** @var string */
    protected $keyType = 'string';

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

    public function tags()
    {
        return $this->morphToMany(Tag::class, "taggable");
    }

    public function getDefaultAttributesFor($attribute)
    {
        return in_array($attribute, ["visual"])
            ? ["model_key" => $attribute]
            : [];
    }

    /**
     * Get the index name for the model.
     *
     * @return string
     */
    public function searchableAs()
    {
        return env('SCOUT_PREFIX') . 'content';
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        return [
            "title" => strip_tags($this->title),
            "heading_text" => strip_tags($this->heading_text),
            "text" => (new Parsedown)->text($this->body_text),
        ];
    }

    /**
     * @return bool
     */
    public function shouldBeSearchable()
    {
        foreach($this->urls()->visible()->published()->get() as $url) {
            if($url->isVisible() && $url->isPublished()) {
                return true;
            }
        }

        return false;
    }
}
