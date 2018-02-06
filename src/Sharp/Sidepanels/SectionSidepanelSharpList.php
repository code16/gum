<?php

namespace Code16\Gum\Sharp\Sidepanels;

use Code16\Gum\Models\Section;
use Code16\Gum\Sharp\Utils\SectionFilter;

class SectionSidepanelSharpList extends SidepanelSharpList
{

    /**
     * @return string
     */
    protected function containerFilter(): string
    {
        return SectionFilter::class;
    }

    /**
     * @return string
     */
    protected function containerType(): string
    {
        return Section::class;
    }

    /**
     * @return string
     */
    protected function containerName(): string
    {
        return "section";
    }
}