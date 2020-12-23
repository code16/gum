<?php

namespace Code16\Gum\Models;

use Carbon\Carbon;
use Code16\Gum\Models\Utils\WithPublishDates;
use Code16\Gum\Models\Utils\WithVisibility;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class ContentUrl extends Model
{
    use WithPublishDates, WithVisibility;

    protected $guarded = [];

    protected $dates = ['created_at', 'updated_at', 'published_at', 'unpublished_at'];

    protected $touches = ['content', 'children'];

    public static function scopeByPath(Builder $query, string $path)
    {
        $path = !Str::startsWith($path, "/") ? "/$path" : $path;

        return $query->where("uri", $path);
    }

    public static function scopeForDomain(Builder $query, string $domain = null)
    {
        return $query->where("domain", $domain);
    }

    public function isVisible(bool $recursive = true): bool
    {
        return $this->visibility == "ONLINE"
            && ($recursive && $this->parent ? $this->parent->isVisible() : true);
    }

    public function isPublished(?Carbon $date = null): bool
    {
        $now = $date ?? Carbon::now();

        return (is_null($this->published_at) || $this->published_at <= $now)
            && (is_null($this->unpublished_at) || $this->unpublished_at > $now)
            && ($this->parent ? $this->parent->isPublished($date) : true);
    }

    public static function createForTile(Section $section, Tile $tile): void
    {
        if($tile->isFreeLink()) {
            return;
        }

        // First create the Section's url if missing.
        if(is_null($sectionUrl = $section->url)) {
            $sectionUrl = $section->url()->create([
                "uri" => (new ContentUrl())->findAvailableUriFor($section, $section->domain),
                "domain" => $section->domain,
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

    public static function createForPageInPagegroup(Page $page): void
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
            "uri" => $containerUrl->findAvailableUriFor($content, $containerUrl->domain),
            "domain" => $containerUrl->domain,
            "content_id" => $content->id,
            "content_type" => get_class($content),
            "visibility" => $visibility ?: $containerUrl->visibility,
            "published_at" => $publishedAt ?: $containerUrl->published_at,
            "unpublished_at" => $unpublishedAt ?: $containerUrl->unpublished_at,
            "parent_id" => $containerUrl->id
        ]);
    }

    public function content(): MorphTo
    {
        return $this->morphTo();
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ContentUrl::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(ContentUrl::class, "parent_id");
    }

    public function getRelativeUriAttribute(): string
    {
        return substr($this->attributes["uri"], 1);
    }

    public function getDepthAttribute(): int
    {
        return count(explode("/", $this->relative_uri));
    }

    /**
     * @param Page|Section|Pagegroup $subContent
     * @param string|null $domain
     * @param string|null $subContentSlug
     * @return string
     */
    public function findAvailableUriFor($subContent, string $domain = null, string $subContentSlug = null): string
    {
        $subContentSlug = $subContentSlug ?: $subContent->slug;
        $uri = preg_replace('#/+#', '/', sprintf("%s/%s", $this->uri, $subContentSlug));

        if ($existingUrl = ContentUrl::where("uri", $uri)
            ->forDomain($domain)
            ->first())
        {
            // Slug issue
            return $this->findAvailableUriFor(
                $subContent,
                $domain,
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

        $this->fresh()->children->each(function(ContentUrl $child) use($newUri) {
            $child->updateUri($newUri);
        });
    }

    public function buildBreadcrumb(array &$breadcrumb = []): array
    {
        if($this->parent_id) {
            $this->parent->buildBreadcrumb($breadcrumb);
        }

        $breadcrumb[$this->uri] = $this->content->menu_title;

        return $breadcrumb;
    }
}
