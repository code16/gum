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

    function buildListDataContainers(): void
    {
        $this
            ->addDataContainer(
                EntityListDataContainer::make("title")
                    ->setLabel("Titre")
            )->addDataContainer(
                EntityListDataContainer::make("urls")
                    ->setLabel("Url")
            );

        if($this->hasMultipleMenus()) {
            $this->addDataContainer(
                EntityListDataContainer::make("menu_key")
                    ->setLabel("Menu")
            );
        }
    }

    function buildListLayout(): void
    {
        $this->addColumn("title", 4, 6)
            ->addColumn("urls", 4, 6);

        if($this->hasMultipleMenus()) {
            $this->addColumnLarge("menu_key", 4);
        }
    }

    function buildListConfig(): void
    {
        $this->setReorderable(RootSectionSharpReorderHandler::class)
            ->setEntityState("visibility", RootSectionVisibilityStateHandler::class);

        if(sizeof(config("gum.domains"))) {
            $this->addFilter("domain", DomainFilter::class, function($value, EntityListQueryParams $params) {
                SharpGumSessionValue::setDomain($value);
            });
        }
    }

    function getListData(EntityListQueryParams $params): array
    {
        $sections = Section::domain(SharpGumSessionValue::getDomain())
            ->with($this->requestWiths())
            ->orderBy('root_menu_order')
            ->where("is_root", true);

        if($params->specificIds()) {
            $sections->whereIn("id", $params->specificIds());
        }

        $this->applyCustomTransformers();

        return $this->transform($sections->get());
    }

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

        if($attribute == "menu_key") {
            return function($value, Section $section) {
                if(!$this->hasMultipleMenus()) {
                    return "";
                }

                $values = config("gum.menus". (SharpGumSessionValue::getDomain() ? "." . SharpGumSessionValue::getDomain() :  ""));

                return $values[$value] ?? "";
            };
        }

        return null;
    }

    protected function hasMultipleMenus(): bool
    {
        $configKey = "gum.menus" . (SharpGumSessionValue::getDomain() ? "." . SharpGumSessionValue::getDomain() :  "");

        return config($configKey) && sizeof(config($configKey)) > 1;
    }
}