<?php

namespace Code16\Gum\Sharp\Utils;

use Code16\Gum\Models\Section;
use Code16\Sharp\EntityList\EntityListSelectRequiredFilter;

class SectionRootFilter implements EntityListSelectRequiredFilter
{
    protected bool $withHome;

    public function __construct(bool $withHome = false)
    {
        $this->withHome = $withHome;
    }

    public function label(): string
    {
        return "Racine";
    }

    public function values(): array
    {
        return
            ["-" => "- Aucune -"] +
            Section::domain(SharpGumSessionValue::getDomain())
                ->where("is_root", true)
                ->when(!$this->withHome, function($query) {
                    $query->where("slug", "!=", "");
                })
                ->orderBy("root_menu_order")
                ->get()
                ->pluck("url.uri", "id")
                ->all();
    }

    public function defaultValue()
    {
        if($sectionId = SharpGumSessionValue::get("root_section")) {
            if(Section::domain(SharpGumSessionValue::getDomain())
                ->where("is_root", true)
                ->find($sectionId)
            ) {
                return $sectionId;
            }
        }

        SharpGumSessionValue::set("root_section", null);

        return "-";
    }
}