<?php

namespace Code16\Gum\Sharp\Sidepanels;

use Code16\Gum\Models\Page;

class PageSidepanelSharpList extends SidepanelSharpList
{

    /**
     * @return string
     */
    protected function containerFilter(): string
    {
        return SidepanelPageFilter::class;
    }

    /**
     * @return string
     */
    protected function containerType(): string
    {
        return Page::class;
    }

    /**
     * @return string
     */
    protected function containerName(): string
    {
        return "page";
    }
}