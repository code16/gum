<?php

namespace Code16\Gum\Sharp\News;

use Code16\Gum\Models\News;
use Code16\Sharp\EntityList\Containers\EntityListDataContainer;
use Code16\Sharp\EntityList\Eloquent\Transformers\SharpUploadModelAttributeTransformer;
use Code16\Sharp\EntityList\EntityListQueryParams;
use Code16\Sharp\EntityList\SharpEntityList;

class NewsSharpList extends SharpEntityList
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
            EntityListDataContainer::make("tags")
                ->setLabel("Tags")
        )->addDataContainer(
            EntityListDataContainer::make("heading_text")
                ->setLabel("Chapô")
        )->addDataContainer(
            EntityListDataContainer::make("published_at")
                ->setLabel("Mise en ligne")
        );
    }

    /**
     * Build list layout using ->addColumn()
     *
     * @return void
     */
    function buildListLayout()
    {
        $this->addColumn("visual", 2, 3)
            ->addColumn("title", 2, 5)
            ->addColumnLarge("tags", 2)
            ->addColumnLarge("heading_text", 4)
            ->addColumn("published_at", 2, 4);
    }

    /**
     * Build list config
     *
     * @return void
     */
    function buildListConfig()
    {
//        $this
//            ->setEntityState("visibility", PageBlocEntityState::class);
    }

    /**
     * Retrieve all rows data as array.
     *
     * @param EntityListQueryParams $params
     * @return array
     */
    function getListData(EntityListQueryParams $params)
    {
        $news = News::with("visual", "tags")
            ->orderBy("published_at", "desc");

        if($params->specificIds()) {
            $news->whereIn("id", $params->specificIds());
        }

        return $this
            ->setCustomTransformer("visual", new SharpUploadModelAttributeTransformer(200))
            ->setCustomTransformer("tags", function($value, $news) {
                return implode(", ", $news->tags->pluck("name")->all());
            })
            ->setCustomTransformer("published_at", function($value, $news) {
                $date = $news->published_at->formatLocalized("%e %b %Y à %Hh%M");

                if($news->published_at->isFuture()) {
                    return "Publié à partir du $date";
                }

                return "Publié depuis le $date";
            })
            ->transform($news->get());
    }
}