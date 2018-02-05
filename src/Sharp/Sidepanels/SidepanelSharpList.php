<?php

namespace Code16\Gum\Sharp\Sidepanels;

use Code16\Gum\Models\Page;
use Code16\Gum\Models\Sidepanel;
use Code16\Gum\Sharp\Utils\SharpGumSessionValue;
use Code16\Sharp\EntityList\Containers\EntityListDataContainer;
use Code16\Sharp\EntityList\EntityListQueryParams;
use Code16\Sharp\EntityList\SharpEntityList;

class SidepanelSharpList extends SharpEntityList
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
            ->setReorderable(SidepanelSharpReorderHandler::class)
            ->addFilter("page", SidePanelPageFilter::class, function($value, EntityListQueryParams $params) {
                SharpGumSessionValue::set("sidepanel_page", $value);
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
        $sidepanels = Sidepanel::where("container_id", $params->filterFor("page"))
            ->where("container_type", Page::class)
            ->orderBy("order");

        return $this
            ->setCustomTransformer("layout_label", function($value, $sidepanel) {
                return $this->layoutCustomTransformer($sidepanel->layout, $sidepanel);
            })
            ->setCustomTransformer("content", function($value, $sidepanel) {
                return $this->contentCustomTransformer($value, $sidepanel);
            })
            ->transform($sidepanels->get());
    }

    /**
     * @param string $layout
     * @param Sidepanel $sidepanel
     * @return string
     */
    protected function layoutCustomTransformer(string $layout, Sidepanel $sidepanel)
    {
        return $layout;
    }

    /**
     * @param $content
     * @param Sidepanel $sidepanel
     * @return mixed
     */
    protected function contentCustomTransformer($content, Sidepanel $sidepanel)
    {
        return "";
    }
}