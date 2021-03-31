<?php

namespace Code16\Gum\Sharp\Pages;

use Code16\Gum\Models\Page;
use Code16\Sharp\Show\Fields\SharpShowEntityListField;
use Code16\Sharp\Show\Fields\SharpShowTextField;
use Code16\Sharp\Show\Layout\ShowLayoutColumn;
use Code16\Sharp\Show\Layout\ShowLayoutSection;
use Code16\Sharp\Show\SharpSingleShow;

class HomepageSharpShow extends SharpSingleShow
{
    function buildShowFields(): void
    {
        $homepage = $this->findHomePage();
        
        $this
            ->addField(SharpShowTextField::make("heading_text")
                ->setLabel("Chapeau")
                ->collapseToWordCount(25)
            )
            ->addField(
                SharpShowEntityListField::make("sidepanels", "sidepanels")
                    ->showCreateButton(true)
                    ->setLabel("Panneaux latéraux")
                    ->hideFilterWithValue("page", $homepage->id)
            )
            ->addField(
                SharpShowEntityListField::make("tileblocks", "tileblocks")
                    ->showCreateButton(true)
                    ->setLabel("Tuiles")
                    ->hideFilterWithValue("page", $homepage->id)
            );
    }

    function buildShowLayout(): void
    {
        $this
            ->addSection("Page", function(ShowLayoutSection $section) {
                $section
                    ->addColumn(6, function(ShowLayoutColumn $column) {
                        $column->withSingleField("heading_text");
                    });
            })
            ->addEntityListSection("sidepanels")
            ->addEntityListSection("tileblocks");
    }

    public function buildShowConfig(): void
    {
        $this->setBreadcrumbCustomLabelAttribute("title");
    }

    function findSingle(): array
    {
        return $this
            ->transform(
                $this->findHomePage(["tileblocks"])
            );
    }

    protected function findHomePage(?array $with = []): Page
    {
        return Page::home(gum_sharp_current_domain())
            ->with($with)
            ->firstOrFail();
    }
}