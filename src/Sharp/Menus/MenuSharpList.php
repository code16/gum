<?php

namespace Code16\Gum\Sharp\Menus;

use Code16\Gum\Models\Page;
use Code16\Gum\Models\Tileblock;
use Code16\Gum\Sharp\Tiles\Utils\TileblockTilesCustomTransformer;
use Code16\Gum\Sharp\Utils\GumSharpList;
use Code16\Sharp\EntityList\Containers\EntityListDataContainer;
use Code16\Sharp\EntityList\EntityListQueryParams;

class MenuSharpList extends GumSharpList
{
    function buildListDataContainers(): void
    {
        $this
            ->addDataContainer(
                EntityListDataContainer::make("layout_variant")
                    ->setLabel("")
            )
            ->addDataContainer(
                EntityListDataContainer::make("pages")
                    ->setLabel("Liens")
            );
    }

    function buildListLayout(): void
    {
        $this->addColumn("layout_variant", 4, 6)
            ->addColumn("pages", 8, 6);
    }

    function buildListConfig(): void
    {
    }

    function getListData(EntityListQueryParams $params)
    {
        $this->applyCustomTransformers();
        
        return $this
            ->transform(
                Tileblock::with($this->requestWiths())
                    ->where("page_id", Page::home(gum_sharp_current_domain())->first()->id)
                    ->where("layout", "_menu")
                    ->orderBy("layout_variant")
                    ->get()
            );
    }

    protected function requestWiths(): array
    {
        return ["tiles", "tiles.page"];
    }

    protected function customTransformerFor(string $attribute)
    {
        if($attribute === "pages") {
            return TileblockTilesCustomTransformer::class;
        }
    }
}