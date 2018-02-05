<?php

namespace Code16\Gum\Sharp\Sections;

use Code16\Gum\Models\Section;
use Code16\Gum\Sharp\Utils\SharpGumSessionValue;
use Code16\Sharp\Form\Eloquent\WithSharpFormEloquentUpdater;
use Code16\Sharp\Form\Fields\SharpFormTextareaField;
use Code16\Sharp\Form\Fields\SharpFormTextField;
use Code16\Sharp\Form\Fields\SharpFormWysiwygField;
use Code16\Sharp\Form\Layout\FormLayoutColumn;
use Code16\Sharp\Form\SharpForm;
use Code16\Sharp\Http\WithSharpFormContext;

class SectionSharpForm extends SharpForm
{
    use WithSharpFormEloquentUpdater, WithSharpFormContext;

    /**
     * Build form fields using ->addField()
     *
     * @return void
     */
    function buildFormFields()
    {
        $this->addField(
            SharpFormTextareaField::make("title")
                ->setLabel("Titre")
                ->setRowCount(2)
        )->addField(
            SharpFormTextField::make("short_title")
                ->setLabel("Titre court")
                ->setHelpMessage("Utilisé dans les menus / fils d'ariane. Facultatif.")
        )->addField(
            SharpFormWysiwygField::make("heading_text")
                ->setLabel("Chapeau")
                ->setToolbar([
                    SharpFormWysiwygField::H1,
                    SharpFormWysiwygField::SEPARATOR,
                    SharpFormWysiwygField::B, SharpFormWysiwygField::I,
                    SharpFormWysiwygField::SEPARATOR,
                    SharpFormWysiwygField::UL
                ])
        )->addField(
            SharpFormTextField::make("style_key")
                ->setLabel("Thème (TODO select)")
        )->addField(
            SharpFormTextField::make("slug")
                ->setLabel("URL")
                ->setHelpMessage("Il s'agit de l'URL (slug) de la section ; laissez ce champ vide pour remplissage automatique à partir du titre. Ne peut contenir que des lettres, des chiffres et des tirets.Attention, si vous modifiez cette valeur, les URLs du site seront modifiées.")
        );
//        )->addField(
//            SharpFormAutocompleteField::make("news_tag", "local")
//                ->setLabel("Actualités concernées")
//                ->setLocalValues(Tag::ofType(News::class)->get()->pluck("name", "id")->all())
//                ->setResultItemInlineTemplate("{{label}}")
//                ->setListItemInlineTemplate("{{label}}")
//                ->addConditionalDisplay("layout", "news_banner")
//        );
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
                ->withSingleField("slug")
                ->withSingleField("style_key");

        })->addColumn(6, function (FormLayoutColumn $column) {
            $column->withSingleField("heading_text");
        });
    }

    /**
     * Retrieve a Model for the form and pack all its data as JSON.
     *
     * @param $id
     * @return array
     */
    function find($id): array
    {
        return $this->transform(Section::findOrFail($id));
    }

    /**
     * @param $id
     * @param array $data
     * @return mixed
     */
    function update($id, array $data)
    {
        $section = $id
            ? Section::findOrFail($id)
            : new Section(["domain" => SharpGumSessionValue::getDomain()]);

        if(!trim($data["slug"])) {
            $data["slug"] = str_slug($data["title"]);
        }

        $this->save($section, $data);

        return $section->id;
    }

    /**
     * @param $id
     */
    function delete($id)
    {
        Section::findOrFail($id)->delete();
    }
}