<?php

namespace Code16\Gum\Models;

use Code16\Gum\Models\Utils\WithMenuTitle;
use Code16\Gum\Models\Utils\WithUuid;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Page extends Model
{
    use WithUuid, WithMenuTitle, Searchable;

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
        $urls = $this->urls()->visible()->published()->get()
            ->filter(function(ContentUrl $url) {
                return $url->isVisible() && $url->isPublished();
            });

        return [
            "type" => "page",
            "depth" => 5,
            "title" => $this->title,
            "group" => $this->pagegroup ? $this->pagegroup->title : "",
            "text" => $this->body_text,
            "_tags" => $urls->pluck("domain")->unique()->all(),
            "url" => $urls->groupBy("domain")->map(function($urlGroup) {
                return $urlGroup->pluck("uri")->all();
            })->all(),
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
