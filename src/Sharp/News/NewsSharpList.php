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
                ->setLabel("Chapeau")
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
        $this->addColumnLarge("visual", 1)
            ->addColumn("title", 3, 7)
            ->addColumnLarge("tags", 2)
            ->addColumnLarge("heading_text", 3)
            ->addColumn("published_at", 3, 5);
    }

    /**
     * Build list config
     *
     * @return void
     */
    function buildListConfig()
    {
        $this
            ->setSearchable()
            ->setPaginated()
            ->setEntityState("visibility", NewsVisibilityStateHandler::class)
            ->addFilter("tags", NewsTagFilterHandler::class);
    }

    /**
     * Retrieve all rows data as array.
     *
     * @param EntityListQueryParams $params
     * @return array
     */
    function getListData(EntityListQueryParams $params)
    {
        $news = News::select("news.*")
            ->with("visual", "tags")
            ->orderBy("published_at", "desc");

        if($params->specificIds()) {
            $news->whereIn("id", $params->specificIds());
        }

        if($tags = (array)$params->filterFor("tags")) {
            foreach($tags as $tag) {
                $news->whereExists(function ($query) use ($tag) {
                    return $query->from("taggables")
                        ->whereRaw("taggables.taggable_id = news.id")
                        ->where("taggables.taggable_type", News::class)
                        ->where("tag_id", $tag);
                });
            }
        }

        if($params->hasSearch()) {
            foreach ($params->searchWords() as $word) {
                $news->where(function ($query) use ($word) {
                    $query->orWhere("news.title", "like", $word)
                        ->orWhere('news.heading_text', 'like', $word);
                });
            }
        }

        return $this
            ->setCustomTransformer("visual", new SharpUploadModelAttributeTransformer(200))

            ->setCustomTransformer("title", function($value, $news) {
                return $news->surtitle
                    ? sprintf("<small>%s</small><br>%s", $news->surtitle, $news->title)
                    : $news->title;
            })

            ->setCustomTransformer("tags", function($value, $news) {
                return implode(", ", $news->tags->pluck("name")->all());
            })

            ->setCustomTransformer("published_at", function($value, $news) {
                $date = $news->published_at->formatLocalized("%e %b %Y à %Hh%M");

                if($news->published_at->isFuture()) {
                    return "Publié à partir du<br>$date";
                }

                return "<span style='color:gray'>Publié depuis le<br>$date</span>";
            })

            ->transform($news->paginate(30));
    }
}