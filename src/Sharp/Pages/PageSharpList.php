<?php

namespace Code16\Gum\Sharp\Pages;

use Closure;
use Code16\Gum\Models\Page;
use Code16\Gum\Models\Section;
use Code16\Gum\Sharp\Utils\DomainFilter;
use Code16\Gum\Sharp\Utils\GumSharpList;
use Code16\Gum\Sharp\Utils\SectionRootFilter;
use Code16\Gum\Sharp\Utils\SharpGumSessionValue;
use Code16\Gum\Sharp\Utils\UrlsCustomTransformer;
use Code16\Sharp\EntityList\Containers\EntityListDataContainer;
use Code16\Sharp\EntityList\Eloquent\Transformers\SharpUploadModelAttributeTransformer;
use Code16\Sharp\EntityList\EntityListQueryParams;
use Code16\Sharp\Utils\Transformers\SharpAttributeTransformer;

class PageSharpList extends GumSharpList
{

    /**
     * Build list containers using ->addDataContainer()
     *
     * @return void
     */
    function buildListDataContainers()
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
        $this->addColumnLarge("visual", 2)
            ->addColumn("title", 3, 6)
            ->addColumnLarge("pagegroup:title", 3)
            ->addColumn("urls", 4, 6);
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
        $pages = Page::select("pages.*")
            ->with($this->requestWiths());

        $rootId = $params->filterFor("root");

        if($rootId && ($root = Section::where("is_root", true)->find($rootId))) {
            $pages->whereExists(function($query) use($root) {
                return $query->from("content_urls")
                    ->whereRaw("content_id = pages.id")
                    ->where("content_type", Page::class)
                    ->where("uri", "like", "{$root->url->uri}%");
            });

        } else {
            $pages->whereNotExists(function($query) {
                return $query->from("content_urls")
                    ->whereRaw("content_id = pages.id")
                    ->where("content_type", Page::class);
            });
        }

        $pages = $pages->get()
            ->groupBy(function ($page) {
                return sizeof($page->urls)
                    ? dirname($page->urls[0]->uri)
                    : "";
            })->sortBy(function($group, $url) {
                return count(explode("/", $url));
            })
            ->flatten()
            ->values();

        $this->applyCustomTransformers();

        return $this->transform($pages);
    }

    /**
     * @return array
     */
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
            return new SharpUploadModelAttributeTransformer(200);
        }

        if($attribute == "urls") {
            return UrlsCustomTransformer::class;
        }

        return null;
    }
}