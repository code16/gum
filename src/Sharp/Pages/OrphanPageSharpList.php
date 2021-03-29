<?php

namespace Code16\Gum\Sharp\Pages;

use Code16\Gum\Models\Page;
use Code16\Gum\Sharp\Utils\GumSharpList;
use Code16\Sharp\EntityList\Containers\EntityListDataContainer;
use Code16\Sharp\EntityList\EntityListQueryParams;
use Code16\Sharp\Utils\Transformers\Attributes\Eloquent\SharpUploadModelThumbnailUrlTransformer;

class OrphanPageSharpList extends GumSharpList
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
                    ->setSortable()
            )
            ->addDataContainer(
                EntityListDataContainer::make("slug")
                    ->setLabel("Adresse")
                    ->setSortable()
            );
    }

    function buildListConfig(): void
    {
        $this->setPaginated()
            ->setDefaultSort("title", "asc");
    }

    function buildListLayout(): void
    {
        $this
            ->addColumn("visual", 2)
            ->addColumn("title", 5, 10)
            ->addColumnLarge("slug", 5);
    }

    function getListData(EntityListQueryParams $params) 
    {
        $pages = Page::orphan()
            ->domain(gum_sharp_current_domain())
            ->notHome()
            ->select("pages.*")
            ->with($this->requestWiths())
            ->orderBy($params->sortedBy(), $params->sortedDir());
        
        $this->applyCustomTransformers();

        return $this->transform($pages->paginate(25));
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