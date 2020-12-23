<?php

namespace Code16\Gum\Sharp\Sections;

use Code16\Gum\Models\ContentUrl;
use Code16\Gum\Models\Section;
use Code16\Gum\Sharp\Utils\SharpGumSessionValue;
use Code16\Sharp\Show\Fields\SharpShowEntityListField;
use Code16\Sharp\Show\Fields\SharpShowTextField;
use Code16\Sharp\Show\Layout\ShowLayoutColumn;
use Code16\Sharp\Show\Layout\ShowLayoutSection;
use Code16\Sharp\Show\SharpShow;

class SectionSharpShow extends SharpShow
{

    function buildShowFields(): void
    {
        $this
            ->addField(SharpShowTextField::make("title")
                ->setLabel("Titre")
            )
            ->addField(SharpShowTextField::make("heading_text")
                ->setLabel("Chapeau")
                ->collapseToWordCount(25)
            )
            ->addField(SharpShowTextField::make("url")
                ->setLabel("URL")
            )
            ->addField(SharpShowTextField::make("style_key")
                ->setLabel("Thème")
            )
            ->addField(SharpShowTextField::make("has_news")
                ->setLabel("Actualités")
            )
            ->addField(
                SharpShowEntityListField::make("tileblocks", "tileblocks")
                    ->showCreateButton(true)
                    ->setLabel("Tuiles")
                    ->hideFilterWithValue('domain', SharpGumSessionValue::getDomain())
                    ->hideFilterWithValue("section", function($instanceId) {
                        return $instanceId;
                    })
            )
            ->addField(
                SharpShowEntityListField::make("section_sidepanels", "section_sidepanels")
                    ->showCreateButton(true)
                    ->setLabel("Panneaux section")
                    ->hideFilterWithValue('domain',null)
                    ->hideFilterWithValue("container", function($instanceId) {
                        SharpGumSessionValue::set("sidepanel_container_type", Section::class);
                        return $instanceId;
                    })
            );
    }

    function buildShowLayout(): void
    {
        $this
            ->addSection("Sous-section", function(ShowLayoutSection $section) {
                $section
                    ->addColumn(6, function(ShowLayoutColumn $column) {
                        $column
                            ->withSingleField("title")
                            ->withSingleField("style_key")
                            ->withSingleField("url")
                            ->withSingleField("has_news");
                    })
                    ->addColumn(6, function(ShowLayoutColumn $column) {
                        $column->withSingleField("heading_text");
                    });
            })
            ->addEntityListSection("tileblocks")
            ->addEntityListSection("section_sidepanels");
    }

    public function buildShowConfig(): void
    {
        $this->setBreadcrumbCustomLabelAttribute("title");
    }

    function find($id): array
    {
        $section = Section::find($id);

        $this->applySectionCustomTransformers($section);

        return $this->transform($section);
    }

    protected function applySectionCustomTransformers(Section $section)
    {
        $this
            ->setCustomTransformer("url", function () use ($section) {
                if($section->isHome()) {
                    return null;
                }
                
                $current = ContentUrl::where('content_id', $section->id)
                    ->where('content_type', Section::class)
                    ->first();

                return $current ? $current->uri : null;
            })
            ->setCustomTransformer("style_key", function($value) use ($section) {
                if($section->isHome()) {
                    return null;
                }
                
                $configKey = "gum.styles"
                    . (SharpGumSessionValue::getDomain() ? "." . SharpGumSessionValue::getDomain() :  "");
                return config($configKey)[$value];
            })
            ->setCustomTransformer("has_news", function() use ($section) {
                if($section->isHome()) {
                    return null;
                }
                
                if($section->has_news) {
                    return $section->tags->pluck("name")->implode(', ');
                }

                return '<i class="fa fa-times"></i> aucune';
            });
    }
}