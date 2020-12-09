<?php

namespace Code16\Gum\Sharp\Sections;

use Code16\Gum\Models\Section;
use Code16\Gum\Sharp\Utils\SharpGumSessionValue;
use Code16\Sharp\Show\Fields\SharpShowEntityListField;
use Code16\Sharp\Show\Fields\SharpShowTextField;
use Code16\Sharp\Show\Layout\ShowLayoutColumn;
use Code16\Sharp\Show\Layout\ShowLayoutSection;
use Code16\Sharp\Show\SharpShow;

class SectionSharpShow extends SharpShow
{

    function buildShowFields()
    {
        $this->addField(SharpShowTextField::make("title")
            ->setLabel("Titre"))
            ->addField(SharpShowTextField::make("heading_text")
                ->setLabel("Chapeau"))
            ->addField(SharpShowTextField::make("slug")
                ->setLabel("URL"))
            ->addField(SharpShowTextField::make("style_key")
                ->setLabel("Thème"))
            ->addField(SharpShowTextField::make("has_news")
                ->setLabel("Actualités"))
            ->addField(
                SharpShowEntityListField::make("tileblocks", "tileblocks")
                    ->showCreateButton(true)
                    ->setLabel("Tuiles")
                    ->hideFilterWithValue('domain',null)
                    ->hideFilterWithValue("section", function($instanceId) {
                        return $instanceId;
                    })
            )
            ->addField(
                SharpShowEntityListField::make("section_sidepanels", "section_sidepanels")
                    ->showCreateButton(true)
                    ->setLabel("Panneaux sections")
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
                    $column->withFields( "style_key|2", "slug|2");
                })
                ->addColumn(12, function(ShowLayoutColumn $column) {
                    $column->withSingleField("has_news");
                });
        })
            ->addEntityListSection("tileblocks")
            ->addEntityListSection("section_sidepanels");
    }

    function find($id): array
    {
        $section = Section::find($id);

        return $this
            ->setCustomTransformer("style_key", function($value, $instance) {
                $configKey = "gum.styles"
                    . (SharpGumSessionValue::getDomain() ? "." . SharpGumSessionValue::getDomain() :  "");
                return config($configKey)[$value];
            })
            ->setCustomTransformer("has_news", function($value, $instance) use ($section) {
                return $section->tags->map(function ($value) {
                    return $value->name;
                })->implode(', ');
            })
            ->transform($section);
    }
}