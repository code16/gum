<?php

namespace Code16\Gum\Models;

use Code16\Gum\Models\Utils\GumContext;
use Code16\Gum\Models\Utils\WithMenuTitle;
use Code16\Gum\Models\Utils\WithUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Pagegroup extends Model
{
    use WithUuid, WithMenuTitle;

    public $incrementing = false;

    protected $guarded = [];

    public function pages()
    {
        return $this->hasMany(Page::class)
            ->orderby("pagegroup_order");
    }

    /**
     * Return a Collection of ContentUrl for all Pages in Pagegroup.
     *
     * @return Collection
     */
    public function getPageUrlsAttribute()
    {
        return $this

            // First grab the right Pagegroup URL in the current GumContext...
            ->urls()
            ->where("parent_id", function($query) {
                $query->select("id")
                    ->from("content_urls")
                    ->where("content_id", GumContext::section()->id)
                    ->where("content_type", Section::class);
            })
            ->firstOrFail()

            // ... then get all ContentUrl for included Pages, in the right order.
            ->children()
            ->join("pages", "pages.id", "=", "content_urls.content_id")
            ->orderBy("pages.pagegroup_order")
            ->get();
    }

    public function urls()
    {
        return $this->morphMany(ContentUrl::class, "content");
    }
}
