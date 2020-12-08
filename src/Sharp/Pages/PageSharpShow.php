<?php

namespace Code16\Gum\Sharp\Pages;

use Code16\Gum\Models\Page;
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
                ->setLabel("Chapeau"))
            ->addField(SharpShowTextField::make("body_text")
                ->setLabel("Texte"))
            ->addField(SharpShowTextField::make("slug")
                ->setLabel("URL"))
            ->addField(
                SharpShowEntityListField::make("page_sidepanels", "page_sidepanels")
                    ->showCreateButton(true)
                    ->setLabel("Panneaux pages")
                    ->hideFilterWithValue('domain',null)
                    ->hideFilterWithValue("container", function($instanceId) {
                        return $instanceId;
                    })
            );
    }

    function buildShowLayout()
    {
        $this->addSection("Structure", function(ShowLayoutSection $section) {
            $section
                ->addColumn(12, function(ShowLayoutColumn $column) {
                    $column->withSingleField("title");
                })
                ->addColumn(12, function(ShowLayoutColumn $column) {
                    $column->withSingleField("heading_text");
                })
                ->addColumn(12, function(ShowLayoutColumn $column) {
                    $column->withSingleField("body_text");
                })
                ->addColumn(12, function(ShowLayoutColumn $column) {
                    $column->withFields("slug|3");
                });
        })
            ->addEntityListSection("page_sidepanels");
    }

    function find($id): array
    {
        return $this->transform(Page::find($id));
    }
}