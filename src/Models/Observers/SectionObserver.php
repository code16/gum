<?php

namespace Code16\Gum\Models\Observers;

use Code16\Gum\Models\Section;

class SectionObserver
{
    /**
     * @param Section $section
     */
    public function updated(Section $section)
    {
        if($section->getOriginal()['slug'] != $section->slug && $section->url) {
            $section->url->updateUri();
        }
    }

    /**
     * @param Section $section
     * @throws \Exception
     */
    public function deleted(Section $section)
    {
        $section->tileblocks->each->delete();

        if($section->url) {
            $section->url->delete();
        }
    }
}