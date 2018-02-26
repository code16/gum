<?php

namespace Code16\Gum\Sharp\Tiles;

use Closure;
use Code16\Gum\Models\Tile;
use Code16\Gum\Models\Tileblock;
use Code16\Gum\Sharp\Utils\DomainFilter;
use Code16\Gum\Sharp\Utils\GumSharpList;
use Code16\Gum\Sharp\Utils\SectionFilter;
use Code16\Gum\Sharp\Utils\SharpGumSessionValue;
use Code16\Sharp\EntityList\Containers\EntityListDataContainer;
use Code16\Sharp\EntityList\EntityListQueryParams;
use Code16\Sharp\Utils\Transformers\SharpAttributeTransformer;

class TileblockSharpList extends GumSharpList
{

    /**
     * Build list containers using ->addDataContainer()
     *
     * @return void
     */
    function buildListDataContainers()
    {
        $this->addDataContainer(
            EntityListDataContainer::make("layout_label")
                ->setLabel("Type")
        )->addDataContainer(
            EntityListDataContainer::make("tiles")
                ->setLabel("Tuiles")
        );
    }

    /**
     * Build list layout using ->addColumn()
     *
     * @return void
     */
    function buildListLayout()
    {
        $this
            ->addColumn("layout_label", 2, 4)
            ->addColumn("tiles", 10, 8);
    }

    /**
     * Build list config
     *
     * @return void
     */
    function buildListConfig()
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

    /**
     * Retrieve all rows data as array.
     *
     * @param EntityListQueryParams $params
     * @return array
     */
    function getListData(EntityListQueryParams $params)
    {
        $tileblocks = Tileblock::with($this->requestWiths())
            ->orderBy("order");

        if($params->filterFor("section")) {
            $tileblocks->where("section_id", $params->filterFor("section"));
        } else {
            $tileblocks->whereNull("section_id");
        }

        $this->applyCustomTransformers();

        return $this->transform($tileblocks->get());
    }

    /**
     * @return array
     */
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
                return $tileblock->tiles->map(function(Tile $tile) {
                    $style = "background-color:#eee; padding:5px; display:inline; ";
                    if($tile->isFreeLink()) {
                        $link = $tile->free_link_url;
                        $style .= "color:gray";
                    } elseif($tile->contentUrl) {
                        $link = $tile->contentUrl->uri;
                    } else {
                        $link = 'pas de lien';
                        $style .= 'color:orange';
                    }

                    return sprintf(
                        '<div style="%s"><small>%s</small> <span style="color:gray; font-style:italic"><small>%s</small></span></div><div class="mb-2"></div>',
                        $style, $link, $this->formatPublishDates($tile)
                    );

                })->implode('');
            };
        }

        return null;
    }

    protected function formatPublishDates(Tile $tile)
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
}