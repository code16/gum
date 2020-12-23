<?php

namespace Code16\Gum\Sharp\Sidepanels\Forms;

use Code16\Gum\Models\Page;
use Code16\Gum\Models\Sidepanel;
use Code16\Gum\Sharp\Utils\SharpGumSessionValue;
use Code16\Sharp\Form\Eloquent\Uploads\Transformers\SharpUploadModelFormAttributeTransformer;
use Code16\Sharp\Form\Eloquent\WithSharpFormEloquentUpdater;
use Code16\Sharp\Form\Fields\SharpFormMarkdownField;
use Code16\Sharp\Form\Fields\SharpFormTextareaField;
use Code16\Sharp\Form\Fields\SharpFormTextField;
use Code16\Sharp\Form\Fields\SharpFormUploadField;
use Code16\Sharp\Form\Layout\FormLayoutColumn;
use Code16\Sharp\Form\SharpForm;

abstract class SidepanelSharpForm extends SharpForm
{
    use WithSharpFormEloquentUpdater;

    function buildFormFields(): void
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
            );

            if($this->hasField("visual_legend")) {
                $this->addField(
                    SharpFormTextareaField::make("visual:legend")
                        ->setRowCount(3)
                        ->setLabel("Légende")
                );
            }

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
                    ->setMaxFileSize(12)
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
                SharpFormMarkdownField::make("body_text")
                    ->setLabel("Texte")
                    ->setToolbar([
                        SharpFormMarkdownField::H1,
                        SharpFormMarkdownField::SEPARATOR,
                        SharpFormMarkdownField::B, SharpFormMarkdownField::I, SharpFormMarkdownField::A,
                        SharpFormMarkdownField::SEPARATOR,
                        SharpFormMarkdownField::UL,
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
    
    function buildFormLayout(): void
    {
        $this
            ->addColumn(6, function (FormLayoutColumn $column) {
                $column->withSingleField("container_label")
                    ->withSingleField("layout_label");
    
                if($this->hasField("body_text")) {
                    $column->withSingleField("body_text");
                }
    
                if($this->hasField("link")) {
                    $column->withSingleField("link");
                }
    
            })
            ->addColumn(6, function (FormLayoutColumn $column) {
                if($this->hasField("visual")) {
                    $column->withSingleField("visual");
    
                    if($this->hasField("video")) {
                        $column->withSingleField("visual:video_url");
                    }
    
                    if($this->hasField("visual_legend")) {
                        $column->withSingleField("visual:legend");
                    }
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
            ->setCustomTransformer('visual', SharpUploadModelFormAttributeTransformer::class)
            ->setCustomTransformer('downloadableFile', SharpUploadModelFormAttributeTransformer::class)
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

    function update($id, array $data)
    {
        $sidepanel = $id ? Sidepanel::findOrFail($id) : new Sidepanel();

        $this->ignore(["container_label", "layout_label"])
            ->save($sidepanel, $this->cleanUpData($data));

        return $sidepanel->id;
    }

    function delete($id): void
    {
        Sidepanel::findOrFail($id)->delete();
    }

    protected function sidepanelFields(): array
    {
        return [
            "visual", "download", "body_text", "link"
        ];
    }

    abstract protected function layoutKey(): string;

    abstract protected function layoutLabel(): string;

    protected function additionalFields(): array
    {
        return [];
    }

    protected function additionalTransformers(): array
    {
        return [];
    }

    private function hasField($field): bool
    {
        return in_array($field, $this->sidepanelFields());
    }

    protected function cleanUpData($data): array
    {
        if(currentSharpRequest()->isCreation()) {
            $data["layout"] = $this->layoutKey();
            $data["container_id"] = $this->containerId();
            $data["container_type"] = SharpGumSessionValue::get("sidepanel_container_type");
        }

        return $data;
    }

    protected function getDownloadableFileFilter(): array
    {
        return ["pdf","zip"];
    }

    protected function containerId(): string
    {
        return SharpGumSessionValue::get("sidepanel_container_type") == Page::class
            ? SharpGumSessionValue::get("page")
            : SharpGumSessionValue::get("section");
    }
}