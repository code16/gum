<?php

namespace Code16\Gum\Sharp\News;

use Closure;
use Code16\Gum\Models\News;
use Code16\Gum\Sharp\Utils\GumSharpList;
use Code16\Sharp\EntityList\Containers\EntityListDataContainer;
use Code16\Sharp\EntityList\Eloquent\Transformers\SharpUploadModelAttributeTransformer;
use Code16\Sharp\EntityList\EntityListQueryParams;
use Code16\Sharp\Utils\Transformers\SharpAttributeTransformer;
use Illuminate\Support\Facades\DB;

class NewsSharpList extends GumSharpList
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
        $news = News::select($this->requestSelect())
            ->with($this->requestWiths())
            ->orderByRaw($this->requestOrderBy());

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

        $this->applyCustomTransformers();

        return $this->transform($news->paginate(30));
    }

    /**
     * @return string|array
     */
    protected function requestSelect()
    {
        return ["news.*", DB::raw("(ABS(DATEDIFF(NOW(), published_at))+1) * importance as diff")];
    }

    /**
     * @return array
     */
    protected function requestWiths(): array
    {
        return ["visual", "tags"];
    }

    /**
     * @return string
     */
    protected function requestOrderBy(): string
    {
        return "diff ASC";
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

        if($attribute == "title") {
            return function($value, $news) {
                return $news->surtitle
                    ? sprintf("<small>%s</small><br>%s", e($news->surtitle), e($news->title))
                    : $news->title;
            };
        }

        if($attribute == "heading_text") {
            return function($value, $news) {
                return str_limit(strip_tags($news->heading_text), 100);
            };
        }

        if($attribute == "tags") {
            return function($value, $news) {
                return implode(", ", $news->tags->pluck("name")->all());
            };
        }

        if($attribute == "published_at") {
            return function($value, $news) {
                $date = $news->published_at->formatLocalized("%e %b %Y à %Hh%M");

                $date = $news->published_at->isFuture()
                    ? "<em>Publié à partir du<br>$date</em>"
                    : "<span style='color:gray'>Publié depuis le<br>$date</span>";

                $score = ($news->published_at->diffInDays()+1) * $news->importance;

                return sprintf('%s<br><span style="background: orange; border-radius: 3px; padding: 2px 3px; color:#fff;"><small>%s</small></span>', $date, $score);
            };
        }

        return null;
    }
}