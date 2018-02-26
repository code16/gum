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
            ->addColumn("layout_label", 2, 6)
            ->addColumn("tiles", 10, 6);
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
                    return $tile->isFreeLink()
                        ? '<p class="mb-2" style="color:gray"><small>' . $tile->free_link_url . '</small></p>'
                        :  ($tile->contentUrl
                            ? '<p class="mb-2"><small>' . $tile->contentUrl->uri . '</small></p>'
                            : '<p class="mb-2" style="color:orange"><small>pas de lien</small></p>'
                        );
                })->implode('');
            };
        }

        return null;
    }
}