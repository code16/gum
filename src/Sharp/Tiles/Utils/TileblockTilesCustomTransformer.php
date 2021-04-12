<?php

namespace Code16\Gum\Sharp\Tiles\Utils;

use Code16\Gum\Models\Tile;
use Code16\Sharp\Utils\Transformers\SharpAttributeTransformer;
use Illuminate\Support\Collection;

class TileblockTilesCustomTransformer implements SharpAttributeTransformer
{

    function apply($value, $instance = null, $attribute = null)
    {
        $lines = $instance->tiles
            ->map(function(Tile $tile) {
                $link = $this->getLinkForTile($tile);

                return sprintf(
                    '<a class="list-group-item text-dark %s" %s><div>%s</div><div>%s</div><div class="text-muted"><small>%s</small></div></a>',
                    $link ? "list-group-item-action" : "bg-light",
                    $link ? "href='{$link}'" : '',
                    $tile->title,
                    $this->formatTileText($tile),
                    $this->formatPublishDates($tile)
                );
            })
            ->implode('');

        return $lines
            ? sprintf('<div class="list-group">%s</div>', $lines)
            : "";
    }

    protected function formatPublishDates(Tile $tile): string
    {
        if(!$tile->published_at && !$tile->unpublished_at) {
            return "";
        }

        if(!$tile->published_at) {
            return "jusqu'au " . $tile->unpublished_at->formatLocalized("%e %b %Y à %Hh%M");
        }

        if(!$tile->unpublished_at) {
            return "à partir du " . $tile->published_at->formatLocalized("%e %b %Y à %Hh%M");
        }

        if($tile->published_at->isSameYear($tile->unpublished_at)) {
            return sprintf(
                "du %s au %s",
                $tile->published_at->formatLocalized("%e %b à %Hh%M"),
                $tile->unpublished_at->formatLocalized("%e %b %Y à %Hh%M")
            );
        }

        return sprintf(
            "du %s au %s",
            $tile->published_at->formatLocalized("%e %b %Y à %Hh%M"),
            $tile->unpublished_at->formatLocalized("%e %b %Y à %Hh%M")
        );
    }

    protected function getLinkForTile(Tile $tile): ?string
    {
        if($tile->page_id) {
            return sprintf(
                '/%s/%s/s-show/pages/%s',
                sharp_base_url_segment(),
                $this->getSegmentsFromRequest()->implode("/"),
                $tile->page_id
            );
        }

        return null;
    }

    protected function formatTileText(Tile $tile): string
    {
        $html = "";

        if(!$tile->isVisible()) {
            $html = '<i class="fa fa-eye-slash text-danger"></i> ';
        } elseif(!$tile->isPublished()) {
            $html = '<i class="fa fa-calendar-times text-danger"></i> ';
        }

        if($tile->isFreeLink()) {
            $html .= sprintf(
                "<span><i class='fa fa-external-link'></i> %s</span>",
                $tile->free_link_url
            );

        } elseif($tile->page_id) {
            $html .= sprintf(
                '<i class="fa fa-file-o"></i> <span class="ui-font text-primary">%s</span>',
                $tile->page->title
            );
        }

        return $html;
    }

    private function getSegmentsFromRequest(): Collection
    {
        if(request()->wantsJson()) {
            // API case: we use the referer
            $urlToParse = request()->header("referer");

            return collect(explode("/", parse_url($urlToParse)["path"]))
                ->filter(function(string $segment) {
                    return strlen(trim($segment)) && $segment !== sharp_base_url_segment();
                })
                ->values();
        }

        return collect(request()->segments())->slice(1)->values();
    }
}