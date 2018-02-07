<?php

namespace Code16\Gum\Sharp\News;

use Carbon\Carbon;
use Code16\Gum\Models\News;
use Code16\Sharp\Form\Eloquent\Transformers\FormUploadModelTransformer;
use Code16\Sharp\Form\Eloquent\WithSharpFormEloquentUpdater;
use Code16\Sharp\Form\Fields\SharpFormDateField;
use Code16\Sharp\Form\Fields\SharpFormListField;
use Code16\Sharp\Form\Fields\SharpFormMarkdownField;
use Code16\Sharp\Form\Fields\SharpFormTextareaField;
use Code16\Sharp\Form\Fields\SharpFormTextField;
use Code16\Sharp\Form\Fields\SharpFormUploadField;
use Code16\Sharp\Form\Layout\FormLayoutColumn;
use Code16\Sharp\Form\Layout\FormLayoutFieldset;
use Code16\Sharp\Form\SharpForm;

class NewsSharpForm extends SharpForm
{
    use WithSharpFormEloquentUpdater;

    /**
     * Build form fields using ->addField()
     *
     * @return void
     */
    function buildFormFields()
    {
        $this->addField(
            SharpFormDateField::make("published_at")
                ->setLabel("Date de publication")
                ->setMondayFirst()
                ->setStepTime(15)
                ->setHasTime(true)
                ->setDisplayFormat("DD/MM/YYYY HH:mm")
        );

        if($this->hasField("visual")) {
            $this->addField(
                SharpFormUploadField::make("visual")
                    ->setFileFilterImages()
                    ->setMaxFileSize(5)
                    ->setStorageDisk("local")
                    ->setStorageBasePath("data/news/{id}")
                )->addField(
                    SharpFormTextField::make("visual:legend")
                        ->setPlaceholder("Légende")
                );

            foreach($this->additionalVisualFields() as $field) {
                $this->addField($field);
            }
        }

        if($this->hasField("surtitle")) {
            $this->addField(
                SharpFormTextareaField::make("surtitle")
                    ->setLabel("Sur-titre")
            );
        }

        if($this->hasField("title")) {
            $this->addField(
                SharpFormTextareaField::make("title")
                    ->setLabel("Titre")
            );
        }

        if($this->hasField("heading_text")) {
            $this->addField(
                SharpFormMarkdownField::make("heading_text")
                    ->setLabel("Chapeau")
                    ->setHeight(200)
            );
        }

        if($this->hasField("body_text")) {
            $this->addField(
                SharpFormMarkdownField::make("body_text")
                    ->setLabel("Texte")
            );
        }

//        )->addField(
//            SharpFormTagsField::make("tags", Tag::ofType(News::class)->get()->pluck("name", "id")->all())
//                ->setCreatable()
//                ->setCreateAttribute("name")
//                ->setCreateText("Nouveau : ")
//                ->setLabel("Étiquettes")
//        )

        if($this->hasField("attachments")) {
            $attachments = SharpFormListField::make("attachments")
                ->setLabel("Pièces jointes")
                ->setAddable()->setAddText("Ajouter")
                ->setRemovable()
                ->setSortable()
                ->setOrderAttribute("order")
                ->addItemField(
                    SharpFormUploadField::make("file")
                        ->setMaxFileSize(12)
                        ->setStorageDisk("local")
                        ->setStorageBasePath("data/news/{id}/attachments")
                )->addItemField(
                    SharpFormTextField::make("legend")
                        ->setPlaceholder("Légende")
                );

            foreach($this->additionalAttachmentItemFields() as $field) {
                $attachments->addItemField($field);
            }

            $this->addField($attachments);
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
            $column->withSingleField("published_at");

            if($this->hasField("title")) {
                $column->withSingleField("title");
            }

            if($this->hasField("surtitle")) {
                $column->withSingleField("surtitle");
            }

            if($this->hasField("visual")) {
                $column->withFieldset("Visuel", function (FormLayoutFieldset $fieldset) {
                    $fieldset->withSingleField("visual")
                        ->withSingleField("visual:legend");

                    foreach ($this->additionalVisualFields() as $key => $field) {
                        $fieldset->withSingleField($key);
                    }
                });
            }

            if($this->hasField("heading_text")) {
                $column->withSingleField("heading_text");
            }

        })->addColumn(6, function (FormLayoutColumn $column) {
            if($this->hasField("body_text")) {
                $column->withSingleField("body_text");
            }

            if($this->hasField("attachments")) {
                $column->withSingleField("attachments", function (FormLayoutColumn $item) {
                    $item->withSingleField("file")
                        ->withSingleField("legend");

                    foreach($this->additionalAttachmentItemFields() as $key => $field) {
                        $item->withSingleField($key);
                    }
                });
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
            ->setCustomTransformer('attachments', FormUploadModelTransformer::class)
            ->transform(News::with("visual", "attachments")->findOrFail($id));
    }

    /**
     * @param $id
     * @param array $data
     * @return mixed
     */
    function update($id, array $data)
    {
        $news = $id ? News::findOrFail($id) : new News();

        $this->save($news, $data);

        return $news->id;
    }

    function create(): array
    {
        // Date rounded to the next quarter hour (15, 30, 45)
        $date = Carbon::createFromTimestamp(ceil(time() / (15 * 60)) * (15 * 60));

        return $this->transform(new News([
            "published_at" => $date
        ]));
    }

    /**
     * @param $id
     */
    function delete($id)
    {
        News::findOrFail($id)->delete();
    }

    /**
     * @return array
     */
    protected function newsFields(): array
    {
        return [
            "visual", "legend", "title", "surtitle",
            "body_text", "heading_text", "attachments"
        ];
    }

    /**
     * @return array
     */
    protected function additionalVisualFields(): array
    {
        return [];
    }

    /**
     * @return array
     */
    protected function additionalAttachmentItemFields(): array
    {
        return [];
    }

    /**
     * @param $field
     * @return bool
     */
    private function hasField($field): bool
    {
        return in_array($field, $this->newsFields());
    }
}