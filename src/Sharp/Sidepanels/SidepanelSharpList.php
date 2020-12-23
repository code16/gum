<?php

namespace Code16\Gum\Sharp\Sidepanels;

use Code16\Gum\Models\Sidepanel;
use Code16\Gum\Sharp\Utils\DomainFilter;
use Code16\Gum\Sharp\Utils\GumSharpList;
use Code16\Gum\Sharp\Utils\SharpGumSessionValue;
use Code16\Sharp\EntityList\Containers\EntityListDataContainer;
use Code16\Sharp\EntityList\EntityListQueryParams;
use Code16\Sharp\Utils\Transformers\SharpAttributeTransformer;

abstract class SidepanelSharpList extends GumSharpList
{

    function buildListDataContainers(): void
    {
        $this
            ->addDataContainer(
                EntityListDataContainer::make("layout_label")
                    ->setLabel("Type")
            )
            ->addDataContainer(
                EntityListDataContainer::make("content")
                    ->setLabel("")
            );
    }

    function buildListLayout(): void
    {
        $this->addColumn("layout_label", 2, 3)
            ->addColumn("content", 10, 9);
    }

    function buildListConfig(): void
    {
        $this
            ->setMultiformAttribute("layout")
            ->setReorderable(SidepanelSharpReorderHandler::class);

        if(sizeof(config("gum.domains"))) {
            $this->addFilter("domain", DomainFilter::class, function($value, EntityListQueryParams $params) {
                SharpGumSessionValue::setDomain($value);
            });
        }

        $this->addFilter("container", $this->containerFilter(), function($value) {
            SharpGumSessionValue::set($this->containerName(), $value);
            SharpGumSessionValue::set("sidepanel_container_type", $this->containerType());
        });
    }

    function getListData(EntityListQueryParams $params): array
    {
        $sidepanels = Sidepanel::where("container_id", $params->filterFor("container"))
            ->where("container_type", $this->containerType())
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

    protected abstract function containerFilter(): string;

    protected abstract function containerType(): string;

    protected abstract function containerName(): string;
}