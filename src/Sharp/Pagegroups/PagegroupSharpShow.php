<?php

namespace Code16\Gum\Sharp\Pagegroups;

use Code16\Gum\Models\ContentUrl;
use Code16\Gum\Models\Page;
use Code16\Gum\Models\Pagegroup;
use Code16\Sharp\Http\WithSharpContext;
use Code16\Sharp\Show\Fields\SharpShowEntityListField;
use Code16\Sharp\Show\Fields\SharpShowTextField;
use Code16\Sharp\Show\Layout\ShowLayoutColumn;
use Code16\Sharp\Show\Layout\ShowLayoutSection;
use Code16\Sharp\Show\SharpShow;

class PagegroupSharpShow extends SharpShow
{

    use WithSharpContext;

    function buildShowFields()
    {
        $this->addField(SharpShowTextField::make("title")
            ->setLabel("Titre"))
            ->addField(SharpShowTextField::make("url")
                ->setLabel("URL du groupe"))
            ->addField(SharpShowTextField::make("urls")
                ->setLabel("URLs"))
            ->addField(
                SharpShowEntityListField::make("pages", "pages")
                    ->showCreateButton(true)
                    ->setLabel("Pages associées")
                    ->hideFilterWithValue('domain',null)
                    ->hideFilterWithValue('root', function($instanceId) {
                        $current = ContentUrl::where('content_id', $instanceId)
                            ->where('content_type', Pagegroup::class)
                            ->first();

                        return $current ? $this->getParentContentUrl($current)->content_id : null;
                    })
                    ->hideFilterWithValue("pagegroup", function($instanceId) {
                        return $instanceId;
                    })
            );
    }

    function buildShowLayout()
    {
        $this->addSection("Groupe de page", function(ShowLayoutSection $section) {
            $section
                ->addColumn(12, function(ShowLayoutColumn $column) {
                    $column->withSingleField("title");
                })
                ->addColumn(12, function(ShowLayoutColumn $column) {
                    $column->withSingleField("url");
                })
                ->addColumn(12, function(ShowLayoutColumn $column) {
                    $column->withSingleField("urls");
                });
        })
            ->addEntityListSection("pages");
    }

    function find($id): array
    {
        $pagegroup = Pagegroup::find($id);

        return $this
            ->setCustomTransformer("url", function () use ($pagegroup) {
                $current = ContentUrl::where('content_id', $pagegroup->id)
                    ->where('content_type', Pagegroup::class)
                    ->first();

                return $current ? $current->uri : null;
            })
            ->setCustomTransformer("urls", function () use ($pagegroup) {
                return $this->getPagesUrls($pagegroup);
            })
            ->transform($pagegroup);
    }

    protected function getParentContentUrl(ContentUrl $contentUrl): ContentUrl
    {
        if($contentUrl->parent()->exists()) {
            return $this->getParentContentUrl($contentUrl->parent);
        }

        return $contentUrl;
    }

    protected function getPagesUrls(Pagegroup $pagegroup)
    {
        $pages = $pagegroup->pages;

        return $pages->map(function ($page) {
            $contentUrls = ContentUrl::where('content_id', $page->id)
                ->where('content_type', Page::class)
                ->get();

            $urls = $contentUrls->map(function ($value) {
                return $value->uri;
            })
                ->flatten()
                ->implode('<br>');;

            return sprintf("<small>".$page->title."</small> <br>%s",$urls);
        })
            ->flatten()
            ->implode('<br><br>');
    }
}