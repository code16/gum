<?php

namespace Code16\Gum\Sharp\Sections;

use Code16\Gum\Models\ContentUrl;
use Code16\Gum\Models\Section;

class RootSectionSharpForm extends SectionSharpForm
{

    /**
     * @param $id
     * @param array $data
     * @return mixed
     */
    function update($id, array $data)
    {
        $data["is_root"] = true;

        if($this->context()->isCreation()) {
            $data["root_menu_order"] = 100;
        }

        $id = parent::update($id, $data);

        if($this->context()->isCreation()) {
            $section = Section::find($id);
            $section->url()->create([
                "uri" => (new ContentUrl())->findAvailableUriFor($section, $section->domain),
                "visibility" => "ONLINE",
            ]);
        }

        return $id;
    }
}