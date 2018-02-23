<?php

namespace Code16\Gum\Sharp\News;

use Carbon\Carbon;
use Code16\Gum\Models\News;
use Code16\Gum\Models\Tag;
use Code16\Sharp\Form\Eloquent\Transformers\FormUploadModelTransformer;
use Code16\Sharp\Form\Eloquent\WithSharpFormEloquentUpdater;
use Code16\Sharp\Form\Fields\SharpFormDateField;
use Code16\Sharp\Form\Fields\SharpFormListField;
use Code16\Sharp\Form\Fields\SharpFormSelectField;
use Code16\Sharp\Form\Fields\SharpFormTagsField;
use Code16\Sharp\Form\Fields\SharpFormTextareaField;
use Code16\Sharp\Form\Fields\SharpFormTextField;
use Code16\Sharp\Form\Fields\SharpFormUploadField;
use Code16\Sharp\Form\Fields\SharpFormWysiwygField;
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
                SharpFormWysiwygField::make("heading_text")
                    ->setLabel("Chapeau")
                    ->setToolbar([
                        SharpFormWysiwygField::H1,
                        SharpFormWysiwygField::SEPARATOR,
                        SharpFormWysiwygField::B, SharpFormWysiwygField::I,
                        SharpFormWysiwygField::SEPARATOR,
                        SharpFormWysiwygField::UL, SharpFormWysiwygField::A,
                        SharpFormWysiwygField::SEPARATOR,
                        SharpFormWysiwygField::UNDO
                    ])
                    ->setHeight(200)
            );
        }

        if($this->hasField("body_text")) {
            $this->addField(
                SharpFormWysiwygField::make("body_text")
                    ->setToolbar([
                        SharpFormWysiwygField::H1,
                        SharpFormWysiwygField::SEPARATOR,
                        SharpFormWysiwygField::B, SharpFormWysiwygField::I,
                        SharpFormWysiwygField::SEPARATOR,
                        SharpFormWysiwygField::UL, SharpFormWysiwygField::A,
                        SharpFormWysiwygField::SEPARATOR,
                        SharpFormWysiwygField::UNDO
                    ])
                    ->setLabel("Texte")
            );
        }

        if($this->hasField("category")) {
            if($this->categoryLabels()) {
                $this->addField(
                    SharpFormSelectField::make("category_label", $this->categoryLabels())
                        ->setLabel("Catégorie")
                        ->setDisplayAsDropdown()
                        ->setClearable()
                );

            } else {
                $this->addField(
                    SharpFormTextField::make("category_label")
                        ->setLabel("Catégorie")
                );
            }
        }

        if($this->hasField("tags")) {
            $this->addField(
                SharpFormTagsField::make("tags", Tag::orderBy("name")->pluck("name", "id")->toArray())
                    ->setCreatable()
                    ->setCreateAttribute("name")
                    ->setCreateText("Nouveau :")
                    ->setLabel("Tags")
            );
        }

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

            if($this->hasField("tags")) {
                $column->withSingleField("tags");
            }

            if($this->hasField("category")) {
                $column->withSingleField("category_label");
            }

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

        })->addColumn(6, function (FormLayoutColumn $column) {
            if($this->hasField("heading_text")) {
                $column->withSingleField("heading_text");
            }

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
            ->transform(News::with("visual", "attachments", "tags")->findOrFail($id));
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

        $this->deleteOrphanTags();

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
            "visual", "legend", "title", "surtitle", "tags",
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
     * @return array
     */
    protected function categoryLabels(): array
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

    protected function deleteOrphanTags()
    {
        $tags = Tag::whereNotIn("id", function($query) {
            return $query->select("tag_id")
                ->from("taggables");
        });

        if(config('gum.domains')) {
            $tags->whereNotIn("name", array_keys(config('gum.domains')));
        }

        $tags->delete();
    }
}