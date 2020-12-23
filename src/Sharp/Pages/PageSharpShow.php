<?php

namespace Code16\Gum\Sharp\Pages;

use Code16\Gum\Models\ContentUrl;
use Code16\Gum\Models\Page;
use Code16\Gum\Sharp\Utils\SharpGumSessionValue;
use Code16\Sharp\Show\Fields\SharpShowEntityListField;
use Code16\Sharp\Show\Fields\SharpShowTextField;
use Code16\Sharp\Show\Layout\ShowLayoutColumn;
use Code16\Sharp\Show\Layout\ShowLayoutSection;
use Code16\Sharp\Show\SharpShow;

class PageSharpShow extends SharpShow
{

    function buildShowFields()
    {
        $this->addField(SharpShowTextField::make("title")
            ->setLabel("Titre"))
            ->addField(SharpShowTextField::make("heading_text")
                ->setLabel("Chapeau")
                ->collapseToWordCount(25))
            ->addField(SharpShowTextField::make("urls")
                ->setLabel("URLs"))
            ->addField(
                SharpShowEntityListField::make("page_sidepanels", "page_sidepanels")
                    ->showCreateButton(true)
                    ->setLabel("Panneaux page")
                    ->hideFilterWithValue('domain',null)
                    ->hideFilterWithValue("container", function($instanceId) {
                        SharpGumSessionValue::set("sidepanel_container_type", Page::class);
                        return $instanceId;
                    })
            );
    }

    function buildShowLayout()
    {
        $this
            ->addSection("Page", function(ShowLayoutSection $section) {
                $section
                    ->addColumn(6, function(ShowLayoutColumn $column) {
                        $column
                            ->withSingleField("title")
                            ->withSingleField("urls");
                    })
                    ->addColumn(6, function(ShowLayoutColumn $column) {
                        $column->withSingleField("heading_text");
                    });
            })
            ->addEntityListSection("page_sidepanels");
    }

    function find($id): array
    {
        $page = Page::find($id);

        return $this
            ->setCustomTransformer("urls", function() use ($page) {
                $urls = ContentUrl::where('content_id', $page->id)
                    ->where('content_type', Page::class)
                    ->get()
                    ->map(function ($value) {
                        return $value->uri;
                    })
                    ->flatten()
                    ->implode('<br>');

                return $urls ?: '<span class="mb-2" style="color:orange"><small>pas de lien</small></span>';
            })
            ->transform($page);
    }
}