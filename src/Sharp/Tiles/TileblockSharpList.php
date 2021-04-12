<?php

namespace Code16\Gum\Sharp\Tiles;

use Closure;
use Code16\Gum\Models\Tileblock;
use Code16\Gum\Sharp\Tiles\Utils\TileblockTilesCustomTransformer;
use Code16\Gum\Sharp\Utils\GumSharpList;
use Code16\Sharp\EntityList\Containers\EntityListDataContainer;
use Code16\Sharp\EntityList\EntityListQueryParams;
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
    }

    function getListData(EntityListQueryParams $params): array
    {
        $tileblocks = Tileblock::with($this->requestWiths())
            ->orderBy("order")
            ->where("page_id", $params->filterFor("page"))
            ->whereNotIn("layout", ["_menu"]);

        $this->applyCustomTransformers();

        return $this
//            ->setCustomTransformer("tiles", TileblockTilesCustomTransformer::class)
            ->transform($tileblocks->get());
    }

    protected function requestWiths(): array
    {
        return ["tiles", "page"];
    }

    /**
     * @param string $attribute
     * @return SharpAttributeTransformer|string|Closure
     */
    protected function customTransformerFor(string $attribute)
    {
        if($attribute == "tiles") {
            return function ($value, $tileblock) {
                $customTransformer = Str::camel($tileblock->layout) . "TileCustomTransformer";
                if (method_exists($this, $customTransformer)) {
                    return $this->$customTransformer($tileblock);
                }

                return (new TileblockTilesCustomTransformer())->apply($value, $tileblock);
            };
        }

        return null;
    }
}