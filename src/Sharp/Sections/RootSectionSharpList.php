<?php

namespace Code16\Gum\Sharp\Sections;

use Closure;
use Code16\Gum\Models\Section;
use Code16\Gum\Sharp\Utils\DomainFilter;
use Code16\Gum\Sharp\Utils\GumSharpList;
use Code16\Gum\Sharp\Utils\SharpGumSessionValue;
use Code16\Gum\Sharp\Utils\UrlsCustomTransformer;
use Code16\Sharp\EntityList\Containers\EntityListDataContainer;
use Code16\Sharp\EntityList\EntityListQueryParams;
use Code16\Sharp\Utils\Transformers\SharpAttributeTransformer;

class RootSectionSharpList extends GumSharpList
{

    /**
     * Build list containers using ->addDataContainer()
     *
     * @return void
     */
    function buildListDataContainers()
    {
        $this->addDataContainer(
            EntityListDataContainer::make("title")
                ->setLabel("Titre")
        )->addDataContainer(
            EntityListDataContainer::make("urls")
                ->setLabel("Url")
        );
    }

    /**
     * Build list layout using ->addColumn()
     *
     * @return void
     */
    function buildListLayout()
    {
        $this->addColumn("title", 4, 6)
            ->addColumnLarge("urls", 4);
    }

    /**
     * Build list config
     *
     * @return void
     */
    function buildListConfig()
    {
        $this->setReorderable(RootSectionSharpReorderHandler::class)
            ->setEntityState("visibility", RootSectionVisibilityStateHandler::class);

        if(sizeof(config("gum.domains"))) {
            $this->addFilter("domain", DomainFilter::class, function($value, EntityListQueryParams $params) {
                SharpGumSessionValue::setDomain($value);
            });
        }
    }

    /**
     * Retrieve all rows data as array.
     *
     * @param EntityListQueryParams $params
     * @return array
     */
    function getListData(EntityListQueryParams $params)
    {
        $sections = Section::domain(SharpGumSessionValue::getDomain())
            ->with($this->requestWiths())
            ->orderBy('root_menu_order')
            ->where("is_root", true)
            ->where("slug", "!=", "");

        if($params->specificIds()) {
            $sections->whereIn("id", $params->specificIds());
        }

        $this->applyCustomTransformers();

        return $this->transform($sections->get());
    }

    /**
     * @return array
     */
    protected function requestWiths(): array
    {
        return ["url"];
    }

    /**
     * @param string $attribute
     * @return SharpAttributeTransformer|string|Closure
     */
    protected function customTransformerFor(string $attribute)
    {
        if($attribute == "urls") {
            return UrlsCustomTransformer::class;
        }

        if($attribute == "visibility") {
            return function($value, Section $section) {
                return $section->url->visibility;
            };
        }

        return null;
    }
}