<?php

namespace Code16\Gum\Sharp\Sections;

use Code16\Gum\Models\Section;
use Code16\Sharp\Show\Fields\SharpShowEntityListField;
use Code16\Sharp\Show\Fields\SharpShowTextField;
use Code16\Sharp\Show\Layout\ShowLayoutColumn;
use Code16\Sharp\Show\Layout\ShowLayoutSection;
use Code16\Sharp\Show\SharpShow;

class RootSectionSharpShow extends SharpShow
{

    function buildShowFields()
    {
        $this->addField(SharpShowTextField::make("title")
            ->setLabel("Titre"))
            ->addField(
                SharpShowEntityListField::make("tileblocks", "tileblocks")
                    ->showCreateButton(true)
                    ->setLabel("Tuiles")
                    ->hideFilterWithValue("section", function($instanceId) {
                        return $instanceId;
                    })
            );
    }

    function buildShowLayout()
    {
        $this->addSection("Structure", function(ShowLayoutSection $section) {
            $section
                ->addColumn(7, function(ShowLayoutColumn $column) {
                    $column->withSingleField("title");
                });
            })
            ->addEntityListSection("tileblocks");
    }

    function find($id): array
    {
        return $this->transform(Section::find($id));
    }
}