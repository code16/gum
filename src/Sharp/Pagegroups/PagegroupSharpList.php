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
                ->setLabel("Url groupe")
        )->addDataContainer(
            EntityListDataContainer::make("pages")
                ->setLabel("Pages")
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
            ->addColumn("pages", 4, 6)
            ->addColumnLarge("urls", 4);
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
        $pagegroups = Pagegroup::orderBy('title')
            ->with($this->requestWiths());

        $rootId = $params->filterFor("root");

        if($rootId && ($root = Section::where("is_root", true)->find($rootId))) {
            $pagegroups->whereExists(function($query) use($root) {
                return $query->from("content_urls")
                    ->whereRaw("content_id = pagegroups.id")
                    ->where("content_type", Pagegroup::class)
                    ->where("uri", "like", "{$root->url->uri}%");
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

    /**
     * @return array
     */
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
                return '<p class="mb-2"><small>'
                    . $pagegroup->pages->pluck("title")->implode('</small></p><p class="mb-2"><small>')
                    . '</small></p>';
            };
        }

        return null;
    }
}