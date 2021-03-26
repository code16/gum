<?php

namespace Code16\Gum\Sharp\Tiles;

use Closure;
use Code16\Gum\Models\Tile;
use Code16\Gum\Models\Tileblock;
use Code16\Gum\Sharp\Utils\GumSharpList;
use Code16\Sharp\EntityList\Containers\EntityListDataContainer;
use Code16\Sharp\EntityList\EntityListQueryParams;
use Code16\Sharp\Utils\Transformers\SharpAttributeTransformer;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TileblockSharpList extends GumSharpList
{
    function buildListDataContainers(): void
    {
        $this
            ->addDataContainer(
                EntityListDataContainer::make("layout_label")
                    ->setLabel("Type")
            )
            ->addDataContainer(
                EntityListDataContainer::make("tiles")
                    ->setLabel("Tuiles")
            );
    }

    function buildListLayout(): void
    {
        $this
            ->addColumn("layout_label", 2, 4)
            ->addColumn("tiles", 10, 8);
    }

    function buildListConfig(): void
    {
        $this
            ->setMultiformAttribute("layout")
            ->setReorderable(TileblockSharpReorderHandler::class);
    }

    function getListData(EntityListQueryParams $params): array
    {
        $tileblocks = Tileblock::with($this->requestWiths())
            ->orderBy("order")
            ->where("page_id", $params->filterFor("page"));

        $this->applyCustomTransformers();

        return $this->transform($tileblocks->get());
    }

    protected function requestWiths(): array
    {
        return ["tiles"];
    }

    /**
     * @param string $attribute
     * @return SharpAttributeTransformer|string|Closure
     */
    protected function customTransformerFor(string $attribute)
    {
        if($attribute == "tiles") {
            return function($value, $tileblock) {
                $customTransformer = Str::camel($tileblock->layout) . "TileCustomTransformer";
                if(method_exists($this, $customTransformer)) {
                    return $this->$customTransformer($tileblock);
                }

                $lines = $tileblock->tiles
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
            };
        }

        return null;
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