<?php

namespace Code16\Gum\Sharp\Sections;

use Code16\Gum\Models\ContentUrl;
use Code16\Gum\Models\Section;
use Code16\Gum\Sharp\Utils\SharpGumSessionValue;
use Code16\Sharp\Form\Fields\SharpFormSelectField;
use Code16\Sharp\Form\Layout\FormLayoutColumn;

class RootSectionSharpForm extends SectionSharpForm
{
    function buildFormFields(): void
    {
        parent::buildFormFields();

        if($menus = $this->getMenus()) {
            $this->addField(
                SharpFormSelectField::make("menu_key", $menus)
                    ->setLabel("Afficher dans le menu")
                    ->setClearable()
            );
        }
    }

    function buildFormLayout(): void
    {
        $this->addColumn(6, function (FormLayoutColumn $column) {
            $column
                ->withSingleField("title")
                ->withSingleField("short_title")
                ->withSingleField("slug");

            if($this->getMenus()) {
                $column->withSingleField("menu_key");
            }

            if($this->hasStylesDefined()) {
                $column->withSingleField("style_key");
            }

        })->addColumn(6, function (FormLayoutColumn $column) {
            $column
                ->withSingleField("heading_text")
                ->withSingleField("has_news")
                ->withSingleField("tags");
        });
    }

    function update($id, array $data)
    {
        $data["is_root"] = true;

        if(currentSharpRequest()->isCreation()) {
            $data["root_menu_order"] = 100;
        }

        $id = parent::update($id, $data);

        if(currentSharpRequest()->isCreation()) {
            $section = Section::find($id);
            $section->url()->create([
                "uri" => (new ContentUrl())->findAvailableUriFor($section, $section->domain),
                "domain" => SharpGumSessionValue::getDomain(),
                "visibility" => "OFFLINE",
            ]);
        }

        return $id;
    }

    protected function getMenus(): array
    {
        $configKey = "gum.menus" . (SharpGumSessionValue::getDomain() ? "." . SharpGumSessionValue::getDomain() :  "");

        return config($configKey) && sizeof(config($configKey)) > 1
            ? config($configKey)
            : [];
    }
}