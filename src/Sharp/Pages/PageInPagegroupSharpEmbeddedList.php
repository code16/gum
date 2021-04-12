<?php

namespace Code16\Gum\Sharp\Pages;

use Code16\Gum\Models\Page;
use Code16\Gum\Sharp\Utils\GumSharpList;
use Code16\Sharp\EntityList\Containers\EntityListDataContainer;
use Code16\Sharp\EntityList\EntityListQueryParams;
use Code16\Sharp\Utils\Transformers\Attributes\Eloquent\SharpUploadModelThumbnailUrlTransformer;

class PageInPagegroupSharpEmbeddedList extends GumSharpList
{

    function buildListDataContainers(): void
    {
        $this
            ->addDataContainer(
                EntityListDataContainer::make("visual")
                    ->setLabel("")
            )
            ->addDataContainer(
                EntityListDataContainer::make("title")
                    ->setLabel("Titre")
            );
    }

    function buildListConfig(): void
    {
    }

    function buildListLayout(): void
    {
        $this
            ->addColumn("visual", 2)
            ->addColumn("title", 10);
    }

    function getListData(EntityListQueryParams $params): array
    {
        $pages = Page::select("pages.*")
            ->with($this->requestWiths())
            ->where('pagegroup_id', $params->filterFor("pagegroup"))
            ->orderBy("pagegroup_order")
            ->get();
        
        $this->applyCustomTransformers();

        return $this->transform($pages);
    }

    protected function requestWiths(): array
    {
        return ["visual"];
    }

    protected function customTransformerFor(string $attribute)
    {
        if($attribute == "visual") {
            return (new SharpUploadModelThumbnailUrlTransformer(200))->renderAsImageTag();
        }

        return null;
    }
}