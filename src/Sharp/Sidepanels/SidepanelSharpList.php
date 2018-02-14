<?php

namespace Code16\Gum\Sharp\Sidepanels;

use Code16\Gum\Models\Sidepanel;
use Code16\Gum\Sharp\Utils\DomainFilter;
use Code16\Gum\Sharp\Utils\GumSharpList;
use Code16\Gum\Sharp\Utils\SharpGumSessionValue;
use Code16\Sharp\EntityList\Containers\EntityListDataContainer;
use Code16\Sharp\EntityList\EntityListQueryParams;

abstract class SidepanelSharpList extends GumSharpList
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
            EntityListDataContainer::make("content")
                ->setLabel("")
        );
    }

    /**
     * Build list layout using ->addColumn()
     *
     * @return void
     */
    function buildListLayout()
    {
        $this->addColumn("layout_label", 3, 6)
            ->addColumn("content", 9, 6);
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

    /**
     * Retrieve all rows data as array.
     *
     * @param EntityListQueryParams $params
     * @return array
     */
    function getListData(EntityListQueryParams $params)
    {
        $sidepanels = Sidepanel::where("container_id", $params->filterFor("container"))
            ->where("container_type", $this->containerType())
            ->with($this->requestWiths())
            ->orderBy("order");

        $this->applyCustomTransformers();

        return $this->transform($sidepanels->get());
    }

    /**
     * @return array
     */
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

    /**
     * @return string
     */
    protected abstract function containerFilter(): string;

    /**
     * @return string
     */
    protected abstract function containerType(): string;

    /**
     * @return string
     */
    protected abstract function containerName(): string;
}