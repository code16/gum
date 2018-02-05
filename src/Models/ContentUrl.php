<?php

namespace Code16\Gum\Models;

use Carbon\Carbon;
use Code16\Gum\Models\Utils\WithPublishDates;
use Code16\Gum\Models\Utils\WithVisibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ContentUrl extends Model
{
    use WithPublishDates, WithVisibility;

    protected $guarded = [];

    protected $dates = ['created_at', 'updated_at', 'published_at', 'unpublished_at'];

    public function scopeByPath(Builder $query, string $path)
    {
        $path = !starts_with($path, "/") ? "/$path" : $path;

        return $query->where("uri", $path);
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        return $this->visibility == "ONLINE"
            && ($this->parent ? $this->parent->isVisible() : true);
    }

    /**
     * @return bool
     */
    public function isPublished()
    {
        return (is_null($this->published_at) || $this->published_at->isPast())
            && (is_null($this->unpublished_at) || $this->unpublished_at->isFuture())
            && ($this->parent ? $this->parent->isPublished() : true);
    }

    /**
     * @param Section $section
     * @param Tile $tile
     */
    public static function createForTile(Section $section, Tile $tile)
    {
        if($tile->isFreeLink()) {
            return;
        }

        // First create the Section's url if missing.
        if(is_null($sectionUrl = $section->url)) {
            $sectionUrl = $section->url()->create([
                "uri" => (new ContentUrl())->findAvailableUriFor($section),
                "visibility" => "ONLINE",
            ]);
        }

        // Then create or find the Tile's content (Page, Section, Pagegroup) url
        // and link it back to the Tile.
        $tile->contentUrl()->associate(
            self::findOrCreateSubContentUrl(
                $tile->linkable, $sectionUrl, $tile->visibility,
                $tile->published_at, $tile->unpublished_at
            )
        )->save();
    }

    /**
     * @param Page $page
     */
    public static function createForPageInPagegroup(Page $page)
    {
        try {
            $pagegroupUrl = ContentUrl::where([
                "content_id" => $page->pagegroup_id,
                "content_type" => Pagegroup::class
            ])->firstOrFail();

            self::findOrCreateSubContentUrl(
                $page, $pagegroupUrl
            );

        } catch(ModelNotFoundException $e) {}
    }

    /**
     * @param Page|Section|Pagegroup $content the content to be linked
     * @param ContentUrl $containerUrl the Section or Pagegroup url
     * @param string $visibility
     * @param Carbon|null $publishedAt
     * @param Carbon|null $unpublishedAt
     * @return ContentUrl
     */
    public static function findOrCreateSubContentUrl(
        $content, ContentUrl $containerUrl, string $visibility = null,
        Carbon $publishedAt = null, Carbon $unpublishedAt = null
    ): ContentUrl
    {
        $existingUrl = ($content instanceof Section)
            ? $content->url
            : $content->urls()->where('parent_id', $containerUrl->id)->first();

        return $existingUrl ?: ContentUrl::create([
            "uri" => $containerUrl->findAvailableUriFor($content),
            "content_id" => $content->id,
            "content_type" => get_class($content),
            "visibility" => $visibility ?: $containerUrl->visibility,
            "published_at" => $publishedAt ?: $containerUrl->published_at,
            "unpublished_at" => $unpublishedAt ?: $containerUrl->unpublished_at,
            "parent_id" => $containerUrl->id
        ]);
    }

    public function content()
    {
        return $this->morphTo();
    }

    public function parent()
    {
        return $this->belongsTo(ContentUrl::class);
    }

    public function children()
    {
        return $this->hasMany(ContentUrl::class, "parent_id");
    }

    public function getRelativeUriAttribute()
    {
        return substr($this->attributes["uri"], 1);
    }

    /**
     * @param Page|Section|Pagegroup $subContent
     * @param string|null $subContentSlug
     * @return string
     */
    public function findAvailableUriFor($subContent, string $subContentSlug = null)
    {
        $subContentSlug = $subContentSlug ?: $subContent->slug;
        $uri = sprintf("%s/%s", $this->uri, $subContentSlug);

        if ($existingUrl = ContentUrl::where("uri", $uri)->first()) {
            // Slug issue
            return $this->findAvailableUriFor(
                $subContent,
                append_suffix_to_slug($subContentSlug)
            );
        }

        return $uri;
    }

    /**
     * @param string|null $baseUri
     * @param string|null $contentSlug
     * @return mixed
     */
    public function updateUri($baseUri = null, $contentSlug = null)
    {
        $contentSlug = $contentSlug ?: $this->content->slug;
        $baseUri = $baseUri ?: dirname($this->uri);

        $newUri = $baseUri . (strlen($baseUri) != 1 ? '/' : '') . $contentSlug;

        if (ContentUrl::where("uri", $newUri)->first()) {
            // Slug issue
            return $this->updateUri(
                $baseUri,
                append_suffix_to_slug($contentSlug)
            );
        }

        $this->update([
            "uri" => $newUri
        ]);

        $this->children->each(function(ContentUrl $child) use($newUri) {
            $child->updateUri($newUri);
        });
    }

    /**
     * @param array $breadcrumb
     * @return array
     */
    public function buildBreadcrumb(array &$breadcrumb = [])
    {
        if($this->parent_id) {
            $this->parent->buildBreadcrumb($breadcrumb);
        }

        $breadcrumb[$this->uri] = $this->content->menu_title;

        return $breadcrumb;
    }
}
