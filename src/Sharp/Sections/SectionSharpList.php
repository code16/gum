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

    function buildListDataContainers(): void
    {
        $this
            ->addDataContainer(
                EntityListDataContainer::make("title")
                    ->setLabel("Titre")
            )
            ->addDataContainer(
                EntityListDataContainer::make("url")
                    ->setLabel("Url")
            );
    }

    function buildListLayout(): void
    {
        $this->addColumn("title", 4, 6)
            ->addColumn("url", 8, 6);
    }

    function buildListConfig(): void
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

    function getListData(EntityListQueryParams $params): array
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
        return $attribute == "url"
            ?  UrlsCustomTransformer::class
            : null;
    }
}