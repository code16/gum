<?php

namespace Code16\Gum\Sharp\Sections;

use Code16\Gum\Models\Section;
use Code16\Gum\Sharp\Utils\DomainFilter;
use Code16\Gum\Sharp\Utils\SectionRootFilter;
use Code16\Gum\Sharp\Utils\SharpGumSessionValue;
use Code16\Gum\Sharp\Utils\UrlsCustomTransformer;
use Code16\Sharp\EntityList\Containers\EntityListDataContainer;
use Code16\Sharp\EntityList\EntityListQueryParams;
use Code16\Sharp\EntityList\SharpEntityList;

class SectionSharpList extends SharpEntityList
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
            ->addColumnLarge("url", 4);
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
            ->orderBy('title')
            ->where("is_root", false);

        $rootId = $params->filterFor("root");

        if($rootId && ($root = Section::where("is_root", true)->find($rootId))) {
            $sections->whereExists(function($query) use($root) {
                return $query->from("content_urls")
                    ->whereRaw("content_id = sections.id")
                    ->where("content_type", Section::class)
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

        return $this
            ->setCustomTransformer("url", UrlsCustomTransformer::class)
            ->transform($sections->get());
    }
}