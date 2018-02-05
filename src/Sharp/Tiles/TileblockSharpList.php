<?php

namespace Code16\Gum\Sharp\Tiles;

use Code16\Gum\Models\Tile;
use Code16\Gum\Models\Tileblock;
use Code16\Gum\Sharp\Utils\DomainFilter;
use Code16\Gum\Sharp\Utils\SectionFilter;
use Code16\Gum\Sharp\Utils\SectionWithHomeFilter;
use Code16\Gum\Sharp\Utils\SharpGumSessionValue;
use Code16\Sharp\EntityList\Containers\EntityListDataContainer;
use Code16\Sharp\EntityList\EntityListQueryParams;
use Code16\Sharp\EntityList\SharpEntityList;

class TileblockSharpList extends SharpEntityList
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
            EntityListDataContainer::make("published_at")
                ->setLabel("Dates")
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
            ->addColumnLarge("tiles", 6)
            ->addColumn("published_at", 4, 6);
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
        $tileblocks = Tileblock::with("tiles", "tiles.contentUrl")
            ->orderBy("order");

        if($params->filterFor("section")) {
            $tileblocks->where("section_id", $params->filterFor("section"));
        } else {
            $tileblocks->whereNull("section_id");
        }

        return $this
            ->setCustomTransformer("layout_label", function($value, $tileblock) {
                return $this->layoutCustomTransformer($tileblock->layout, $tileblock);
            })

            ->setCustomTransformer("published_at", function($value, $tileblock) {
                if(!$tileblock->published_at && !$tileblock->unpublished_at) {
                    return "";
                }

                if(!$tileblock->published_at) {
                    return "jusqu'au " . $tileblock->unpublished_at->formatLocalized("%e %b %Y à %Hh%M");
                }

                if(!$tileblock->unpublished_at) {
                    return "à partir du " . $tileblock->published_at->formatLocalized("%e %b %Y à %Hh%M");
                }

                if($tileblock->published_at->isSameYear($tileblock->unpublished_at)) {
                    return sprintf(
                        "du %s au %s",
                        $tileblock->published_at->formatLocalized("%e %b à %Hh%M"),
                        $tileblock->unpublished_at->formatLocalized("%e %b %Y à %Hh%M")
                    );
                }

                return sprintf(
                    "du %s au %s",
                    $tileblock->published_at->formatLocalized("%e %b %Y à %Hh%M"),
                    $tileblock->unpublished_at->formatLocalized("%e %b %Y à %Hh%M")
                );
            })

            ->setCustomTransformer("tiles", function($value, $tileblock) {
                return $tileblock->tiles->map(function(Tile $tile) {
                    return $tile->isFreeLink()
                        ? '<p class="mb-2" style="color:gray"><small>' . $tile->free_link_url . '</small></p>'
                        :  ($tile->contentUrl
                            ? '<p class="mb-2"><small>' . $tile->contentUrl->uri . '</small></p>'
                            : '<p class="mb-2" style="color:orange"><small>pas de lien</small></p>'
                        );
                })->implode('');
            })

            ->transform($tileblocks->get());
    }

    /**
     * @param string $layout
     * @param Tileblock $tileblock
     * @return string
     */
    protected function layoutCustomTransformer(string $layout, Tileblock $tileblock)
    {
        return $layout;
    }
}