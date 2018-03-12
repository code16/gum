<?php

namespace Code16\Gum\Sharp\Sections;

use Code16\Gum\Models\ContentUrl;
use Code16\Gum\Models\Section;
use Code16\Gum\Sharp\Utils\SharpGumSessionValue;
use Code16\Sharp\Form\Fields\SharpFormSelectField;
use Code16\Sharp\Form\Layout\FormLayoutColumn;

class RootSectionSharpForm extends SectionSharpForm
{
    /**
     * Build form fields using ->addField()
     *
     * @return void
     */
    function buildFormFields()
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

    /**
     * Build form layout using ->addTab() or ->addColumn()
     *
     * @return void
     */
    function buildFormLayout()
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
                "domain" => SharpGumSessionValue::getDomain(),
                "visibility" => "OFFLINE",
            ]);
        }

        return $id;
    }

    /**
     * @return array
     */
    protected function getMenus()
    {
        $configKey = "gum.menus"
            . (SharpGumSessionValue::getDomain() ? "." . SharpGumSessionValue::getDomain() :  "");

        return config($configKey) && sizeof(config($configKey)) > 1
            ? config($configKey)
            : [];
    }
}