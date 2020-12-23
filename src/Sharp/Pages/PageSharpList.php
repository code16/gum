<?php

namespace Code16\Gum\Sharp\Pages;

use Closure;
use Code16\Gum\Models\Page;
use Code16\Gum\Models\Pagegroup;
use Code16\Gum\Models\Section;
use Code16\Gum\Sharp\Utils\DomainFilter;
use Code16\Gum\Sharp\Utils\GumSharpList;
use Code16\Gum\Sharp\Utils\SectionRootFilter;
use Code16\Gum\Sharp\Utils\SharpGumSessionValue;
use Code16\Gum\Sharp\Utils\UrlsCustomTransformer;
use Code16\Sharp\EntityList\Containers\EntityListDataContainer;
use Code16\Sharp\EntityList\EntityListQueryParams;
use Code16\Sharp\Utils\Transformers\Attributes\Eloquent\SharpUploadModelThumbnailUrlTransformer;
use Code16\Sharp\Utils\Transformers\SharpAttributeTransformer;

class PageSharpList extends GumSharpList
{

    function buildListDataContainers(): void
    {
        $this->addDataContainer(
            EntityListDataContainer::make("visual")
                ->setLabel("")
        )->addDataContainer(
            EntityListDataContainer::make("title")
                ->setLabel("Titre")
        )->addDataContainer(
            EntityListDataContainer::make("pagegroup:title")
                ->setLabel("Groupe")
        )->addDataContainer(
            EntityListDataContainer::make("urls")
                ->setLabel("Urls")
        );
    }

    function buildListLayout(): void
    {
        $this->addColumnLarge("visual", 2);
            
        if(currentSharpRequest()->getCurrentBreadcrumbItem()->entityKey() === "pagegroups") {
            // EEL in PagegroupSharpShow
            $this->addColumn("title", 4, 6)
                ->addColumn("urls", 6);
        } else {
            $this->addColumn("title", 3, 6)
                ->addColumnLarge("pagegroup:title", 3)
                ->addColumn("urls", 4, 6);
        }
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

        $this->setSearchable();
    }

    function getListData(EntityListQueryParams $params): array
    {
        $pages = Page::select("pages.*")
            ->with($this->requestWiths());

        $rootId = $params->filterFor("root");

        if($pagegroupId = $params->filterFor("pagegroup")) {
            // EEL in PagegroupSharpShow
            $pages->where('pagegroup_id', $pagegroupId);
        }

        if($rootId && ($root = Section::where("is_root", true)->find($rootId))) {
            $pages->whereExists(function($query) use($root) {
                $subquery = $query->from("content_urls")
                    ->whereRaw("content_id = pages.id")
                    ->where("content_type", Page::class);

                if($root->isHome()) {
                    // Root case
                    $subquery->where(function($subsubquery) {
                        return $subsubquery->
                            whereIn("parent_id", function ($query) {
                                // Pages of Pagegroups linked to the root...
                                return $query->select("id")
                                    ->from("content_urls")
                                    ->where("content_type", Pagegroup::class)
                                    ->when(SharpGumSessionValue::getDomain(), function ($query) {
                                        $query->where("domain", SharpGumSessionValue::getDomain());
                                    })
                                    ->where("parent_id", function($query) {
                                        return $query->select("id")
                                            ->from("content_urls")
                                            ->when(SharpGumSessionValue::getDomain(), function ($query) {
                                                $query->where("domain", SharpGumSessionValue::getDomain());
                                            })
                                            ->where("uri", "/")
                                            ->limit(1);
                                    });
                            })
                            ->orWhere("parent_id", function ($query) {
                                // Pages of Sections linked to the root...
                                return $query->select("id")
                                    ->from("content_urls")
                                    ->when(SharpGumSessionValue::getDomain(), function ($query) {
                                        $query->where("domain", SharpGumSessionValue::getDomain());
                                    })
                                    ->where("uri", "/");
                            });
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
            $pages->whereNotExists(function($query) {
                return $query->from("content_urls")
                    ->whereRaw("content_id = pages.id")
                    ->where("content_type", Page::class);
            });
        }

        if($params->hasSearch()) {
            foreach ($params->searchWords() as $word) {
                $pages->where("pages.title", "like", $word);
            }
        }

        $pages = $pages->get()
            ->groupBy(function ($page) {
                return sizeof($page->urls)
                    ? dirname($page->urls[0]->uri)
                    : "";
            })
            ->sortBy(function($group, $url) {
                return count(explode("/", $url));
            })
            ->flatten()
            ->values();

        $this->applyCustomTransformers();

        return $this->transform($pages);
    }

    protected function requestWiths(): array
    {
        return ["urls", "visual", "pagegroup"];
    }

    /**
     * @param string $attribute
     * @return SharpAttributeTransformer|string|Closure
     */
    protected function customTransformerFor(string $attribute)
    {
        if($attribute == "visual") {
            return (new SharpUploadModelThumbnailUrlTransformer(200))->renderAsImageTag();
        }

        if($attribute == "urls") {
            return UrlsCustomTransformer::class;
        }

        return null;
    }
}