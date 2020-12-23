<?php

namespace Code16\Gum\Sharp\Pagegroups;

use Closure;
use Code16\Gum\Models\Pagegroup;
use Code16\Gum\Models\Section;
use Code16\Gum\Sharp\Utils\DomainFilter;
use Code16\Gum\Sharp\Utils\GumSharpList;
use Code16\Gum\Sharp\Utils\SectionRootFilter;
use Code16\Gum\Sharp\Utils\SharpGumSessionValue;
use Code16\Gum\Sharp\Utils\UrlsCustomTransformer;
use Code16\Sharp\EntityList\Containers\EntityListDataContainer;
use Code16\Sharp\EntityList\EntityListQueryParams;
use Code16\Sharp\Utils\Transformers\SharpAttributeTransformer;

class PagegroupSharpList extends GumSharpList
{

    function buildListDataContainers(): void
    {
        $this
            ->addDataContainer(
                EntityListDataContainer::make("title")
                    ->setLabel("Titre")
            )
            ->addDataContainer(
                EntityListDataContainer::make("urls")
                    ->setLabel("Url groupe")
            )
            ->addDataContainer(
                EntityListDataContainer::make("pages")
                    ->setLabel("Pages")
            );
    }

    function buildListLayout(): void
    {
        $this->addColumn("title", 4, 6)
            ->addColumn("pages", 4, 6)
            ->addColumnLarge("urls", 4);
    }

    function buildListConfig(): void
    {
        if(sizeof(config("gum.domains"))) {
            $this->addFilter("domain", DomainFilter::class, function($value, EntityListQueryParams $params) {
                SharpGumSessionValue::setDomain($value);
            });
        }

        $this->addFilter("root", new SectionRootFilter(true), function($value, EntityListQueryParams $params) {
            SharpGumSessionValue::set("root_section", $value);
        });
    }

    function getListData(EntityListQueryParams $params): array
    {
        $rootId = $params->filterFor("root");

        $pagegroups = Pagegroup::orderBy('title')
            ->with($this->requestWiths());

        if($rootId && ($root = Section::where("is_root", true)->find($rootId))) {
            $pagegroups->whereExists(function($query) use($root) {
                $subquery = $query->from("content_urls")
                    ->whereRaw("content_id = pagegroups.id")
                    ->where("content_type", Pagegroup::class);

                if($root->isHome()) {
                    $subquery->where("parent_id", function($query) {
                        return $query->select("id")
                            ->from("content_urls")
                            ->when(SharpGumSessionValue::getDomain(), function($query) {
                                $query->where("domain", SharpGumSessionValue::getDomain());
                            })
                            ->where("uri", "/")
                            ->limit(1);
                    });

                } else {
                    $subquery
                        ->where("uri", "like", "{$root->url->uri}/%")
                        ->when(SharpGumSessionValue::getDomain(), function($query) {
                            $query->where("domain", SharpGumSessionValue::getDomain());
                        });
                }

                return $subquery;
            });

        } else {
            $pagegroups->whereNotExists(function($query) {
                return $query->from("content_urls")
                    ->whereRaw("content_id = pagegroups.id")
                    ->where("content_type", Pagegroup::class);
            });
        }

        $this->applyCustomTransformers();

        return $this->transform($pagegroups->get());
    }

    protected function requestWiths(): array
    {
        return ["pages"];
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

        if($attribute == "pages") {
            return function($value, $pagegroup) {
                return '<p class="mb-1"><small>'
                    . $pagegroup
                        ->pages->take(6)
                        ->pluck("title")
                        ->implode('</small></p><p class="mb-1"><small>')
                    . '</small></p>';
            };
        }

        return null;
    }
}