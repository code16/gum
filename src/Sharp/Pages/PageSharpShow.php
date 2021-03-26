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

    function buildShowFields(): void
    {
        $this
            ->addField(SharpShowTextField::make("title")
                ->setLabel("Titre")
            )
            ->addField(SharpShowTextField::make("heading_text")
                ->collapseToWordCount(25)
            )
            ->addField(
                SharpShowTextField::make("body_text")
                    ->collapseToWordCount(100)
            )
            ->addField(
                SharpShowEntityListField::make("subpages", "pages")
                    ->showCreateButton(true)
                    ->setLabel("Sous-pages")
                    ->hideFilterWithValue('domain', null)
                    ->hideFilterWithValue("pagegroup", function($instanceId) {
                        return $instanceId;
                    })
            )
            ->addField(
                SharpShowEntityListField::make("sidepanels", "sidepanels")
                    ->showCreateButton(true)
                    ->setLabel("Panneaux latéraux")
                    ->hideFilterWithValue('domain', null)
                    ->hideFilterWithValue("page", function($instanceId) {
                        return $instanceId;
                    })
            )
            ->addField(
                SharpShowEntityListField::make("tileblocks", "tileblocks")
                    ->showCreateButton(true)
                    ->setLabel("Tuiles")
                    ->hideFilterWithValue('domain', null)
                    ->hideFilterWithValue("page", function($instanceId) {
                        return $instanceId;
                    })
            );
    }

    function buildShowLayout(): void
    {
        $page = Page::findOrFail(currentSharpRequest()->instanceId());
        
        $this
            ->addSection("Page", function(ShowLayoutSection $section) {
                $section
                    ->addColumn(6, function(ShowLayoutColumn $column) {
                        $column->withSingleField("title");
                    })
                    ->addColumn(6, function(ShowLayoutColumn $column) {
                        $column->withSingleField("heading_text");
                    });
            })
            ->addSection("Texte", function(ShowLayoutSection $section) {
                $section->addColumn(8, function(ShowLayoutColumn $column) {
                    $column->withSingleField("body_text");
                });
            });
        
        if($page->isPagegroup()) {
            $this->addEntityListSection("subpages");
        } else {
            $this->addEntityListSection("sidepanels")
                ->addEntityListSection("tileblocks");
        }
    }

    public function buildShowConfig(): void
    {
        $this->setBreadcrumbCustomLabelAttribute("title");
    }

    function find($id): array
    {
        return $this
            ->setCustomTransformer("heading_text", function($value) {
                return (new \Parsedown())->parse($value);
            })
            ->setCustomTransformer("body_text", function($value) {
                return (new \Parsedown())->parse($value);
            })
            ->transform(
                Page::with("tileblocks") // TODO Domain??
                    ->findOrFail($id)
            );
    }
}