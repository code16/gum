<?php

namespace Code16\Gum\Sharp\Sections;

use Closure;
use Code16\Gum\Models\Section;
use Code16\Gum\Sharp\Utils\DomainFilter;
use Code16\Gum\Sharp\Utils\GumSharpList;
use Code16\Gum\Sharp\Utils\SectionRootFilter;
use Code16\Gum\Sharp\Utils\SharpGumSessionValue;
use Code16\Gum\Sharp\Utils\UrlsCustomTransformer;
use Code16\Sharp\EntityList\Containers\EntityListDataContainer;
use Code16\Sharp\EntityList\EntityListQueryParams;
use Code16\Sharp\Utils\Transformers\SharpAttributeTransformer;

class SectionSharpList extends GumSharpList
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
            EntityListDataContainer::make("url")
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
            ->addColumn("url", 8, 6);
    }

    /**
     * Build list config
     *
     * @return void
     */
    function buildListConfig()
    {
        if(sizeof(config("gum.domains"))) {
            $this->addFilter("domain", DomainFilter::class, function($value, EntityListQueryParams $params) {
                SharpGumSessionValue::setDomain($value);
            });
        }

        $this->addFilter("root", SectionRootFilter::class, function($value, EntityListQueryParams $params) {
            SharpGumSessionValue::set("root_section", $value);
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
        $sections = Section::domain(SharpGumSessionValue::getDomain())
            ->with($this->requestWiths())
            ->orderBy('title')
            ->where("is_root", false);

        $rootId = $params->filterFor("root");

        if($rootId && ($root = Section::where("is_root", true)->find($rootId))) {
            $sections->whereExists(function($query) use($root) {
                return $query->from("content_urls")
                    ->whereRaw("content_id = sections.id")
                    ->where("content_type", Section::class)
                    ->when(SharpGumSessionValue::getDomain(), function($query) {
                        $query->where("domain", SharpGumSessionValue::getDomain());
                    })
                    ->where("uri", "like", "{$root->url->uri}%");
            });

        } else {
            $sections->whereNotExists(function($query) {
                return $query->from("content_urls")
                    ->whereRaw("content_id = sections.id")
                    ->where("content_type", Section::class);
            });
        }

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
        return [];
    }

    /**
     * @param string $attribute
     * @return SharpAttributeTransformer|string|Closure
     */
    protected function customTransformerFor(string $attribute)
    {
        if($attribute == "url") {
            return UrlsCustomTransformer::class;
        }

        return null;
    }
}