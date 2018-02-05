<?php

namespace Code16\Gum\Sharp\Sidepanels\Forms;

use Code16\Gum\Models\Page;
use Code16\Gum\Models\Sidepanel;
use Code16\Gum\Sharp\Utils\SharpGumSessionValue;
use Code16\Sharp\Form\Eloquent\Transformers\FormUploadModelTransformer;
use Code16\Sharp\Form\Eloquent\WithSharpFormEloquentUpdater;
use Code16\Sharp\Form\Fields\SharpFormAutocompleteField;
use Code16\Sharp\Form\Fields\SharpFormCheckField;
use Code16\Sharp\Form\Fields\SharpFormTextareaField;
use Code16\Sharp\Form\Fields\SharpFormTextField;
use Code16\Sharp\Form\Fields\SharpFormUploadField;
use Code16\Sharp\Form\Fields\SharpFormWysiwygField;
use Code16\Sharp\Form\Layout\FormLayoutColumn;
use Code16\Sharp\Form\SharpForm;
use Code16\Sharp\Http\WithSharpFormContext;

abstract class SidepanelSharpForm extends SharpForm
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
            SharpFormAutocompleteField::make("layout", "local")
                ->setResultItemInlineTemplate("{{label}}")
                ->setListItemInlineTemplate("{{label}}")
                ->setLocalValues([$this->layoutKey() => $this->layoutLabel()])
                ->setReadOnly()
                ->setLabel("Type de panneau")
        )->addField(
            SharpFormAutocompleteField::make("container", "local")
                ->setResultItemInlineTemplate("{{label}}")
                ->setListItemInlineTemplate("{{label}}")
                ->setLocalValues([SharpGumSessionValue::get("sidepanel_page") => Page::find(SharpGumSessionValue::get("sidepanel_page"))->title])
                ->setReadOnly()
                ->setLabel("Page")
        );

        if($this->hasField("visual")) {
            $this->addField(
                SharpFormUploadField::make("visual")
                    ->setLabel("Visuel")
                    ->setFileFilterImages()
                    ->setMaxFileSize(5)
                    ->setStorageDisk("local")
                    ->setStorageBasePath("data/sidepanels/{id}")
            )->addField(
                SharpFormCheckField::make("visual:is_video", "Vidéo")
            )->addField(
                SharpFormTextField::make("visual:video_url")
                    ->addConditionalDisplay("visual:is_video")
                    ->setLabel("URL de la vidéo")
            )->addField(
                SharpFormTextareaField::make("visual:legend")
                    ->setRowCount(3)
                    ->setLabel("Légende")
            );
        }

        if($this->hasField("download")) {
            $this->addField(
                SharpFormUploadField::make("downloadableFile")
                    ->setLabel("Fichier")
                    ->setFileFilter($this->getDownloadableFileFilter())
                    ->setMaxFileSize(10)
                    ->setStorageDisk("local")
                    ->setStorageBasePath("data/sidepanels/{id}")
            )->addField(
                SharpFormTextareaField::make("downloadableFile:title")
                    ->setRowCount(3)
                    ->setLabel("Titre")
            );
        }

        if($this->hasField("body_text")) {
            $this->addField(
                SharpFormWysiwygField::make("body_text")
                    ->setLabel("Texte")
                    ->setToolbar([
                        SharpFormWysiwygField::H1,
                        SharpFormWysiwygField::SEPARATOR,
                        SharpFormWysiwygField::B, SharpFormWysiwygField::I,
                        SharpFormWysiwygField::SEPARATOR,
                        SharpFormWysiwygField::UL
                    ])
            );
        }

        if($this->hasField("link")) {
            $this->addField(
                SharpFormTextField::make("link")
                    ->setLabel("Lien")
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
            $column->withSingleField("container")
                ->withSingleField("layout");

            if($this->hasField("body_text")) {
                $column->withSingleField("body_text");
            }

            if($this->hasField("link")) {
                $column->withSingleField("link");
            }

        })->addColumn(6, function (FormLayoutColumn $column) {
            if($this->hasField("visual")) {
                $column->withSingleField("visual")
                    ->withSingleField("visual:legend");

                if($this->hasField("video")) {
                    $column->withSingleField("visual:is_video")
                        ->withSingleField("visual:video_url");
                }
            }

            if($this->hasField("download")) {
                $column->withSingleField("downloadableFile")
                    ->withSingleField("downloadableFile:title");
            }
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
        return $this
            ->setCustomTransformer('visual', FormUploadModelTransformer::class)
            ->setCustomTransformer('downloadableFile', FormUploadModelTransformer::class)
            ->transform(Sidepanel::with("visual", "downloadableFile", "container")->findOrFail($id));
    }

    public function create(): array
    {
        $sidepanel = new Sidepanel([
            "layout" => $this->layoutKey()
        ]);

        $sidepanel->container()->associate(
            Page::find(SharpGumSessionValue::get("sidepanel_page"))
        );

        return $this->transform($sidepanel);
    }

    /**
     * @param $id
     * @param array $data
     * @return mixed
     */
    function update($id, array $data)
    {
        $sidepanel = $id ? Sidepanel::findOrFail($id) : new Sidepanel();

        $this->ignore("container")
            ->save($sidepanel, $this->cleanUpData($data));

        return $sidepanel->id;
    }

    /**
     * @param $id
     */
    function delete($id)
    {
        Sidepanel::findOrFail($id)->delete();
    }

    /**
     * @return array
     */
    protected function sidepanelFields(): array
    {
        return [
            "visual", "download", "body_text", "link"
        ];
    }

    /**
     * @return string
     */
    abstract protected function layoutKey(): string;

    /**
     * @return string
     */
    abstract protected function layoutLabel(): string;

    /**
     * @param $field
     * @return bool
     */
    private function hasField($field): bool
    {
        return in_array($field, $this->configFields);
    }

    /**
     * @param $data
     * @return array
     */
    protected function cleanUpData($data): array
    {
        if($this->context()->isCreation()) {
            $data["layout"] = $this->layoutKey();
            $data["container_id"] = SharpGumSessionValue::get("sidepanel_page");
            $data["container_type"] = Page::class;
        }

        return $data;
    }

    /**
     * @return array
     */
    protected function getDownloadableFileFilter()
    {
        return ["pdf","zip"];
    }
}