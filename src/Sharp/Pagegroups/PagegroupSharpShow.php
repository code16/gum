<?php

namespace Code16\Gum\Sharp\Pagegroups;

use Code16\Gum\Models\ContentUrl;
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
            ->addField(SharpShowTextField::make("slug")
                ->setLabel("URL"))
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
                    $column->withFields("slug|3");
                });
        })
            ->addEntityListSection("pages");
    }

    function find($id): array
    {
        return $this->transform(Pagegroup::find($id));
    }

    protected function getParentContentUrl(ContentUrl $contentUrl): ContentUrl
    {
        if($contentUrl->parent()->exists()) {
            return $this->getParentContentUrl($contentUrl->parent);
        }

        return $contentUrl;
    }
}