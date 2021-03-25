<?php

namespace Code16\Gum\Models;

use Code16\Gum\Models\Utils\WithMenuTitle;
use Code16\Gum\Models\Utils\WithUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Laravel\Scout\Searchable;

class Page extends Model
{
    use WithUuid, WithMenuTitle, Searchable;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    public static function buildBreadcrumbFromPath(string $path, ?string $domain): Collection
    {
        $currentPage = Page::domain($domain)
            ->home()
            ->with("tileblocks")
            ->firstOrFail();

        $breadcrumb = collect();

        foreach(explode("/", $path) as $segment) {
            $pages = Page::where("slug", $segment)
                ->with("tileblocks")
                ->get();

            foreach($pages as $page) {
                if($currentPage->is_pagegroup) {
                    // Check that $page is subpage of $currentPage
                    $isSegmentValid = $page->pagegroup_id === $currentPage->id;
                    
                } else {
                    // Look for an online tile which links $currentPage -> $page 
                    $isSegmentValid = Tile::where("page_id", $page->id)
                        ->visible()
                        ->published()
                        ->whereIn("tileblock_id", $currentPage->tileblocks->pluck("id"))
                        ->exists();
                }

                if($isSegmentValid) {
                    $breadcrumb->add($page);
                    $currentPage = $page;
                    
                    continue 2;
                }
            }

            throw new ModelNotFoundException();
        }
        
        return $breadcrumb;
    }

    public function scopeDomain(Builder $query, $domain): void
    {
        if($domain) {
            $query->where("domain", $domain);
        }
    }

    public function scopeHome(Builder $query): void
    {
        $query->where("slug", "");
    }

    public function pagegroup(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function subpages(): HasMany
    {
        return $this->hasMany(Page::class, "pagegroup_id")
            ->orderBy("pagegroup_order");
    }

    public function tileblocks(): HasMany
    {
        return $this->hasMany(Tileblock::class)
            ->orderby("order");
    }

    public function getOnlineTileblocksAttribute(): Collection
    {
        // TODO refactor this to a proper sql query
        return $this
            ->tileblocks()
            ->with("tiles")
            ->get()
            ->filter(function(Tileblock $tileblock) {
                $tileblock->tiles = $tileblock->tiles
                    ->filter(function(Tile $tile) {
                        return $tile->isVisible() && $tile->isPublished();
                    });

                return count($tileblock->tiles);
            });
    }

    public function visual(): MorphOne
    {
        return $this->morphOne(Media::class, "model")
            ->where("model_key", "visual");
    }

    public function sidepanels(): HasMany
    {
        return $this->hasMany(Sidepanel::class)
            ->orderBy("order");
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, "taggable");
    }

    public function getDefaultAttributesFor(string $attribute): array
    {
        return in_array($attribute, ["visual"])
            ? ["model_key" => $attribute]
            : [];
    }

    public function searchableAs(): string
    {
        return env('SCOUT_PREFIX') . 'content';
    }

    public function toSearchableArray(): array
    {
        return [
            "id" => $this->id,
            "type" => Page::class,
            "domain" => $this->urls()->visible()->published()->first()->domain,
            "updated_at" => $this->updated_at->timestamp,
            "title" => strip_tags($this->title),
            "text" => strip_tags($this->heading_text . " " . $this->body_text),
        ];
    }

    public function shouldBeSearchable(): bool
    {
        if(!config("gum.scout_enabled")){
            return false;
        }
        
        return true; // TODO $this->urls()->visible()->published()->count() > 0;
    }
}
