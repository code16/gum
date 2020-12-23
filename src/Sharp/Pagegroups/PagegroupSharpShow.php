<?php

namespace Code16\Gum\Sharp\Pagegroups;

use Code16\Gum\Models\ContentUrl;
use Code16\Gum\Models\Pagegroup;
use Code16\Sharp\Show\Fields\SharpShowEntityListField;
use Code16\Sharp\Show\Fields\SharpShowTextField;
use Code16\Sharp\Show\Layout\ShowLayoutColumn;
use Code16\Sharp\Show\Layout\ShowLayoutSection;
use Code16\Sharp\Show\SharpShow;

class PagegroupSharpShow extends SharpShow
{

    function buildShowFields(): void
    {
        $this
            ->addField(SharpShowTextField::make("title")
                ->setLabel("Titre")
            )
            ->addField(SharpShowTextField::make("url")
                ->setLabel("Adresse")
            )
            ->addField(
                SharpShowEntityListField::make("pages", "pages")
                    ->showCreateButton(true)
                    ->setLabel("Pages associées")
                    ->hideFilterWithValue('domain', null)
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

    function buildShowLayout(): void
    {
        $this
            ->addSection("Groupe de page", function(ShowLayoutSection $section) {
                $section
                    ->addColumn(12, function(ShowLayoutColumn $column) {
                        $column->withSingleField("title")
                            ->withSingleField("url");
                    });
            })
            ->addEntityListSection("pages");
    }

    function find($id): array
    {
        $pageGroup = Pagegroup::find($id);

        return $this
            ->setCustomTransformer("url", function () use ($pageGroup) {
                $current = ContentUrl::where('content_id', $pageGroup->id)
                    ->where('content_type', Pagegroup::class)
                    ->first();

                return $current ? $current->uri : '<p class="mb-2" style="color:orange"><small>pas de lien</small></p>';
            })
            ->transform($pageGroup);
    }

    protected function getParentContentUrl(ContentUrl $contentUrl): ContentUrl
    {
        if($contentUrl->parent()->exists()) {
            return $this->getParentContentUrl($contentUrl->parent);
        }

        return $contentUrl;
    }
}