<?php

namespace Code16\Gum\Models;

use Code16\Gum\Models\Utils\WithUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Scout\Searchable;

class Page extends Model
{
    use WithUuid, Searchable;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];
    protected $touches = [
        "subpages",
        "tileblocks"
    ];

    public static function buildBreadcrumbFromPath(string $path, ?string $domain): Collection
    {
        $currentPage = Page::home($domain)
            ->with("tileblocks")
            ->firstOrFail();

        $breadcrumb = collect();

        foreach(explode("/", $path) as $segment) {
            $pages = Page::where("slug", $segment)
                ->where("domain", $domain)
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

    public function scopeOrphan(Builder $query): void
    {
        $query
            ->whereNull("pagegroup_id")
            ->whereNotExists(function($query) {
                return $query->select(DB::raw(1))
                    ->from('tiles')
                    ->whereRaw('tiles.page_id = pages.id');
            });
    }

    public function scopeNotOrphan(Builder $query): void
    {
        $query->where(function($query) {
            return $query->orWhereNotNull("pagegroup_id")
                ->orWhereExists(function ($query) {
                    return $query->select(DB::raw(1))
                        ->from('tiles')
                        ->whereRaw('tiles.page_id = pages.id');
                });
        });
    }

    public function scopeHome(Builder $query, ?string $domain = null): void
    {
        $query
            ->where("slug", "")
            ->when($domain, function($query, $domain) {
                return $query->where("domain", $domain);
            });
    }

    public function scopeNotHome(Builder $query): void
    {
        $query
            ->where("slug", "!=", "")
            ->whereNotNull("slug");
    }

    public function scopeNotSubpage(Builder $query): void
    {
        $query->whereNull("pagegroup_id");
    }

    public function scopeDomain(Builder $query, ?string $domain = null): void
    {
        $query
            ->when($domain, function($query, $domain) {
                return $query->where("domain", $domain);
            });
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

    public function getMenuTitleAttribute(): string
    {
        return $this->attributes["short_title"] ?: $this->attributes["title"];
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

    public function isPagegroup(): bool
    {
        return $this->is_pagegroup;
    }

    public function isHome(): bool
    {
        return $this->slug === null || $this->slug === "";
    }

    public function findUri(string $currentPath = ""): ?string
    {
        if($this->isHome()) {
            return $currentPath;
        }

        if($this->pagegroup_id) {
            return $this->pagegroup->findUri("{$this->slug}/{$currentPath}");
        }
        
        $tile = Tile::select("tiles.*")
            ->visible()->published()
            ->with("tileblock", "tileblock.page")
            ->leftJoin("tileblocks", "tiles.tileblock_id", "=", "tileblocks.id")
            ->where("tileblocks.layout", "!=", "text") // Text layout is considered as "not publicly linked tile"
            ->where("tiles.page_id", $this->id)
            ->first();
        
        return $tile ? $tile->tileblock->page->findUri("{$this->slug}/{$currentPath}") : "";
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
            "title" => strip_tags($this->title),
            "text" => strip_tags($this->heading_text . " " . $this->body_text),
        ];
    }

    public function shouldBeSearchable(): bool
    {
        if(!config("gum.scout_enabled")) {
            return false;
        }
        
        if($this->isHome()) {
            return true;
        }
        
        if($this->pagegroup_id) {
            return $this->pagegroup->shouldBeSearchable();
        }
        
        $tile = Tile::select("tiles.*")
            ->visible()
            ->published()
            ->with("tileblock", "tileblock.page")
            ->leftJoin("tileblocks", "tiles.tileblock_id", "=", "tileblocks.id")
            ->where("tileblocks.layout", "!=", "text") // Text layout is considered as "not publicly linked tile"
            ->where("tiles.page_id", $this->id)
            ->get()
            ->first(function(Tile $tile) {
                return $tile->tileblock->page->shouldBeSearchable();
            });
        
        return $tile !== null;
    }
}
