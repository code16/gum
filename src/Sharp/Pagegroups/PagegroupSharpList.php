<?php

namespace Code16\Gum\Sharp\Pagegroups;

use Code16\Gum\Models\Pagegroup;
use Code16\Gum\Models\Section;
use Code16\Gum\Sharp\Utils\DomainFilter;
use Code16\Gum\Sharp\Utils\SectionRootFilter;
use Code16\Gum\Sharp\Utils\SharpGumSessionValue;
use Code16\Gum\Sharp\Utils\UrlsCustomTransformer;
use Code16\Sharp\EntityList\Containers\EntityListDataContainer;
use Code16\Sharp\EntityList\EntityListQueryParams;
use Code16\Sharp\EntityList\SharpEntityList;

class PagegroupSharpList extends SharpEntityList
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
        $pagegroups = Pagegroup::orderBy('title');

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

        return $this
            ->setCustomTransformer("urls", UrlsCustomTransformer::class)
            ->transform($pagegroups->get());
    }
}