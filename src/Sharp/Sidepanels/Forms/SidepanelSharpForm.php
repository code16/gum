<?php

namespace Code16\Gum\Sharp\Sidepanels\Forms;

use Code16\Gum\Models\Page;
use Code16\Gum\Models\Sidepanel;
use Code16\Gum\Sharp\Utils\SharpGumSessionValue;
use Code16\Sharp\Form\Eloquent\Transformers\FormUploadModelTransformer;
use Code16\Sharp\Form\Eloquent\WithSharpFormEloquentUpdater;
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
            SharpFormTextField::make("layout_label")
                ->setReadOnly()
                ->setLabel("Type de panneau")
        )->addField(
            SharpFormTextField::make("container_label")
                ->setReadOnly()
                ->setLabel("Page ou section")
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
                SharpFormTextareaField::make("visual:legend")
                    ->setRowCount(3)
                    ->setLabel("Légende")
            );

            if($this->hasField("video")) {
                $this->addField(
                    SharpFormTextField::make("visual:video_url")
                        ->setLabel("URL de la vidéo")
                );
            }
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
                        SharpFormWysiwygField::B, SharpFormWysiwygField::I, SharpFormWysiwygField::A,
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

        foreach($this->additionalFields() as $field) {
            $this->addField($field);
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
            $column->withSingleField("container_label")
                ->withSingleField("layout_label");

            if($this->hasField("body_text")) {
                $column->withSingleField("body_text");
            }

            if($this->hasField("link")) {
                $column->withSingleField("link");
            }

        })->addColumn(6, function (FormLayoutColumn $column) {
            if($this->hasField("visual")) {
                $column->withSingleField("visual");

                if($this->hasField("video")) {
                    $column->withSingleField("visual:video_url");
                }

                $column->withSingleField("visual:legend");
            }

            if($this->hasField("download")) {
                $column->withSingleField("downloadableFile")
                    ->withSingleField("downloadableFile:title");
            }

            foreach ($this->additionalFields() as $key => $field) {
                $column->withSingleField($key);
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
        $this
            ->setCustomTransformer('visual', FormUploadModelTransformer::class)
            ->setCustomTransformer('downloadableFile', FormUploadModelTransformer::class)
            ->setCustomTransformer('container_label', function($value, $sidepanel) {
                return $sidepanel->container->title;
            })
            ->setCustomTransformer('layout_label', function() {
                return $this->layoutLabel();
            });

        foreach($this->additionalTransformers() as $attribute => $transformer) {
            $this->setCustomTransformer($attribute, $transformer);
        }

        return $this->transform(Sidepanel::with("visual", "downloadableFile", "container")->findOrFail($id));
    }

    /**
     * @return array
     */
    public function create(): array
    {
        return $this
            ->setCustomTransformer('layout_label', function() {
                return $this->layoutLabel();
            })
            ->setCustomTransformer('container_label', function() {
                return call_user_func([
                    SharpGumSessionValue::get("sidepanel_container_type"),
                    "find"
                ], $this->containerId())->title;
            })
            ->transform(new Sidepanel());
    }

    /**
     * @param $id
     * @param array $data
     * @return mixed
     */
    function update($id, array $data)
    {
        $sidepanel = $id ? Sidepanel::findOrFail($id) : new Sidepanel();

        $this->ignore(["container_label", "layout_label"])
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
     * @return array
     */
    protected function additionalFields(): array
    {
        return [];
    }

    /**
     * @return array
     */
    protected function additionalTransformers(): array
    {
        return [];
    }

    /**
     * @param $field
     * @return bool
     */
    private function hasField($field): bool
    {
        return in_array($field, $this->sidepanelFields());
    }

    /**
     * @param $data
     * @return array
     */
    protected function cleanUpData($data): array
    {
        if($this->context()->isCreation()) {
            $data["layout"] = $this->layoutKey();
            $data["container_id"] = $this->containerId();
            $data["container_type"] = SharpGumSessionValue::get("sidepanel_container_type");
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

    /**
     * @return string
     */
    protected function containerId()
    {
        return SharpGumSessionValue::get("sidepanel_container_type") == Page::class
            ? SharpGumSessionValue::get("page")
            : SharpGumSessionValue::get("section");
    }
}