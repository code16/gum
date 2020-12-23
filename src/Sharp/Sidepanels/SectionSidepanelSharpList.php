<?php

namespace Code16\Gum\Sharp\Sidepanels;

use Code16\Gum\Models\Section;
use Code16\Gum\Sharp\Utils\SectionFilter;

class SectionSidepanelSharpList extends SidepanelSharpList
{

    protected function containerFilter(): string
    {
        return SectionFilter::class;
    }

    protected function containerType(): string
    {
        return Section::class;
    }

    protected function containerName(): string
    {
        return "section";
    }
}