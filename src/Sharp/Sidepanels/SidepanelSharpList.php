<?php

namespace Code16\Gum\Sharp\Sidepanels;

use Code16\Gum\Models\Sidepanel;
use Code16\Gum\Sharp\Utils\GumSharpList;
use Code16\Sharp\EntityList\Containers\EntityListDataContainer;
use Code16\Sharp\EntityList\EntityListQueryParams;
use Code16\Sharp\Utils\Transformers\SharpAttributeTransformer;

class SidepanelSharpList extends GumSharpList
{

    function buildListDataContainers(): void
    {
        $this
            ->addDataContainer(
                EntityListDataContainer::make("layout_label")
                    ->setLabel("Type")
            )
            ->addDataContainer(
                EntityListDataContainer::make("page")
                    ->setLabel("")
            );
    }

    function buildListLayout(): void
    {
        $this->addColumn("layout_label", 2, 3)
            ->addColumn("page", 10, 9);
    }

    function buildListConfig(): void
    {
        $this
            ->setMultiformAttribute("layout")
            ->setReorderable(SidepanelSharpReorderHandler::class);
    }

    function getListData(EntityListQueryParams $params): array
    {
        $sidepanels = Sidepanel::where("page_id", $params->filterFor("page"))
            ->with($this->requestWiths())
            ->orderBy("order");

        $this->applyCustomTransformers();

        return $this->transform($sidepanels->get());
    }

    protected function requestWiths(): array
    {
        return [];
    }

    /**
     * @param string $attribute
     * @return SharpAttributeTransformer|string|Closure
     */
    protected function customTransformerFor(string $attribute)
    {
        return null;
    }
}