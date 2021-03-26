<?php

namespace Code16\Gum\Sharp\Tiles;

use Closure;
use Code16\Gum\Models\Tile;
use Code16\Gum\Models\Tileblock;
use Code16\Gum\Sharp\Utils\DomainFilter;
use Code16\Gum\Sharp\Utils\GumSharpList;
use Code16\Gum\Sharp\Utils\SharpGumSessionValue;
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

        if(sizeof(config("gum.domains"))) {
            $this->addFilter("domain", DomainFilter::class, function($value, EntityListQueryParams $params) {
                SharpGumSessionValue::setDomain($value);
            });
        }
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

                return $tileblock->tiles
                    ->map(function(Tile $tile) {
                        $style = "padding:5px; display:inline; color:gray;";
//                        if(!$link = $tile->url) {
//                            $link = 'pas de lien';
//                            $style .= 'color:orange';
//                        }
    
                        return sprintf(
                            '%s <div style="%s"> <span style="color:gray; font-style:italic"><small>%s</small></span></div><div class="mb-2"></div>',
                            $this->linkEntityTile($tile),
                            $style,
                            $this->formatPublishDates($tile)
                        );
                    })
                    ->implode('');
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
            return " • jusqu'au " . $tile->unpublished_at->formatLocalized("%e %b %Y à %Hh%M");
        }

        if(!$tile->unpublished_at) {
            return " • à partir du " . $tile->published_at->formatLocalized("%e %b %Y à %Hh%M");
        }

        if($tile->published_at->isSameYear($tile->unpublished_at)) {
            return sprintf(
                " • du %s au %s",
                $tile->published_at->formatLocalized("%e %b à %Hh%M"),
                $tile->unpublished_at->formatLocalized("%e %b %Y à %Hh%M")
            );
        }

        return sprintf(
            " • du %s au %s",
            $tile->published_at->formatLocalized("%e %b %Y à %Hh%M"),
            $tile->unpublished_at->formatLocalized("%e %b %Y à %Hh%M")
        );
    }

    protected function linkEntityTile(Tile $tile)
    {
        if($tile->isFreeLink()) {
            return sprintf(
                "<span><i class='fa fa-external-link'></i> %s</span> <span class='text-muted'><small>%s</small></span>",
                $tile->title,
                $tile->free_link_url
            );
        }
        
        if($tile->page_id) {
            return sprintf(
                '<a href="/%s/%s/s-show/pages/%s">%s</a>',
                sharp_base_url_segment(),
                $this->getSegmentsFromRequest()->implode("/"),
                $tile->page_id,
                $tile->page->title
            );
        }
        
        return $tile->title;
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