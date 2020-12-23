<?php

namespace Code16\Gum\Sharp\Sidepanels;

use Code16\Gum\Models\Page;

class PageSidepanelSharpList extends SidepanelSharpList
{

    protected function containerFilter(): string
    {
        return SidepanelPageFilter::class;
    }

    protected function containerType(): string
    {
        return Page::class;
    }

    protected function containerName(): string
    {
        return "page";
    }
}