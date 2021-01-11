<?php

namespace Code16\Gum\Sharp\Tiles;

use Closure;
use Code16\Gum\Models\Page;
use Code16\Gum\Models\Pagegroup;
use Code16\Gum\Models\Section;
use Code16\Gum\Models\Tile;
use Code16\Gum\Models\Tileblock;
use Code16\Gum\Sharp\Utils\DomainFilter;
use Code16\Gum\Sharp\Utils\GumSharpList;
use Code16\Gum\Sharp\Utils\SectionFilter;
use Code16\Gum\Sharp\Utils\SharpGumSessionValue;
use Code16\Sharp\EntityList\Containers\EntityListDataContainer;
use Code16\Sharp\EntityList\EntityListQueryParams;
use Code16\Sharp\Utils\Links\LinkToShowPage;
use Code16\Sharp\Utils\Transformers\SharpAttributeTransformer;
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

        $this->addFilter("section", SectionFilter::class, function($value) {
            SharpGumSessionValue::set("section", $value);
        });
    }

    function getListData(EntityListQueryParams $params): array
    {
        $tileblocks = Tileblock::with($this->requestWiths())
            ->orderBy("order")
            ->where("section_id", $params->filterFor("section") ?: (new SectionFilter())->defaultValue());

        $this->applyCustomTransformers();

        return $this->transform($tileblocks->get());
    }

    protected function requestWiths(): array
    {
        return ["tiles", "tiles.contentUrl"];
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
                        $style = "background-color:#eee; padding:5px; display:inline; color:gray;";
                        if($tile->isFreeLink()) {
                            $link = $tile->free_link_url;
                        } elseif($tile->contentUrl) {
                            $link = $tile->contentUrl->uri;
                        } else {
                            $link = 'pas de lien';
                            $style .= 'color:orange';
                        }
    
                        return sprintf(
                            '%s <div style="%s"><small>%s</small> <span style="color:gray; font-style:italic"><small>%s</small></span></div><div class="mb-2"></div>',
                            $this->linkEntityTile($tile),
                            $style,
                            $link,
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

    protected function linkEntityTile(Tile $tile)
    {
        if($tile->linkable_type === null || !$tile->linkable_id) {
            return sprintf("<span><i class='fa fa-external-link'></i> %s</span>",
                $tile->title
            );
        }

        if($tile->linkable_type == Page::class) {
            $icon = "fa-file-o";
            $entityKey = "pages";
        } elseif ($tile->linkable_type == Pagegroup::class) {
            $icon = "fa-files-o";
            $entityKey = "pagegroups";
        } else {
            $entityKey = "sections";
            $icon = Section::find($tile->linkable_id)->is_root
                ? "fa-sitemap"
                : "fa-clone";
        }

        return sprintf("<span><i class='fa %s'></i> %s</span>",
            $icon,
            $tile->title
                ? LinkToShowPage::make($entityKey, $tile->linkable_id)
                ->renderAsText($tile->title)
                : LinkToShowPage::make($entityKey, $tile->linkable_id)
                ->renderAsText("Sans titre")
        );
    }
}