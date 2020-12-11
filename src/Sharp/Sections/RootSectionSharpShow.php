<?php

namespace Code16\Gum\Sharp\Sections;

use Code16\Gum\Models\Section;
use Code16\Gum\Sharp\Utils\SharpGumSessionValue;
use Code16\Sharp\Show\Fields\SharpShowTextField;
use Code16\Sharp\Show\Layout\ShowLayoutColumn;
use Code16\Sharp\Show\Layout\ShowLayoutSection;

class RootSectionSharpShow extends SectionSharpShow
{

    function buildShowFields()
    {
        parent::buildShowFields();

        $this->addField(SharpShowTextField::make("menu_key")
            ->setLabel("Emplacement"));
    }

    function buildShowLayout()
    {
        $this->addSection("Section racine", function(ShowLayoutSection $section) {
            $section
                ->addColumn(12, function(ShowLayoutColumn $column) {
                    $column->withSingleField("title");
                })
                ->addColumn(12, function(ShowLayoutColumn $column) {
                    $column->withSingleField("heading_text");
                })
                ->addColumn(12, function(ShowLayoutColumn $column) {
                    $column->withFields("style_key|2", "menu_key|2", "url|6");
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

        $this->applySectionCustomTransformers($section);

        return $this
            ->setCustomTransformer("menu_key", function($value, $instance) {
                $configKey = "gum.menus"
                    . (SharpGumSessionValue::getDomain() ? "." . SharpGumSessionValue::getDomain() :  "");
                return $value ? config($configKey)[$value] : null;
            })
            ->transform($section);
    }
}