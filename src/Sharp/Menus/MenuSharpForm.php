<?php

namespace Code16\Gum\Sharp\Menus;

use Code16\Gum\Models\Page;
use Code16\Gum\Models\Tile;
use Code16\Gum\Models\Tileblock;
use Code16\Sharp\Form\Eloquent\WithSharpFormEloquentUpdater;
use Code16\Sharp\Form\Fields\SharpFormAutocompleteField;
use Code16\Sharp\Form\Fields\SharpFormListField;
use Code16\Sharp\Form\Fields\SharpFormSelectField;
use Code16\Sharp\Form\Fields\SharpFormTextField;
use Code16\Sharp\Form\Layout\FormLayoutColumn;
use Code16\Sharp\Form\SharpForm;
use Illuminate\Support\Str;

class MenuSharpForm extends SharpForm
{
    use WithSharpFormEloquentUpdater;

    function buildFormFields(): void
    {
        $this
            ->addField(
                SharpFormTextField::make("layout_variant")
                    ->setReadOnly(currentSharpRequest()->isUpdate())
                    ->setLabel("Menu")
            )
            ->addField(
                $this->createItemsListField()
            );
    }

    function buildFormLayout(): void
    {
        $this
            ->addColumn(4, function (FormLayoutColumn $column) {
                $column->withSingleField("layout_variant");
            })
            ->addColumn(8, function (FormLayoutColumn $column) {
                $column
                    ->withSingleField("tiles", function (FormLayoutColumn $item) {
                        $item->withSingleField("title")
                            ->withFields("link_type|4", "free_link_url|8", "page_id|8", "orphan_page_id|8", "new_page_title|8");
                    });
            });
    }

    function find($id): array
    {
        return $this
            ->setCustomTransformer('tiles[link_type]', function($value, Tile $tile) {
                return $tile->page_id ? "page": "free";
            })
            ->transform(
                Tileblock::with("tiles", "page")->findOrFail($id)
            );
    }

    function update($id, array $data)
    {
        $menu = $id 
            ? Tileblock::findOrFail($id) 
            : new Tileblock([
                "layout" => "_menu",
                "page_id" => Page::home(gum_sharp_current_domain())->first()->id
            ]);

        $data["tiles"] = collect($data["tiles"] ?? [])
            ->map(function($dataTile) use($menu) {
                $dataTile["visibility"] = "ONLINE";
                $dataTile["published_at"] = null;
                $dataTile["unpublished_at"] = null;
                
                if ($dataTile["link_type"] === "free") {
                    $dataTile["page_id"] = null;
                    
                } else {
                    $dataTile["free_link_url"] = null;
                    
                    if ($dataTile["link_type"] === "orphan") {
                        $dataTile["page_id"] = $dataTile["orphan_page_id"];

                    } elseif (in_array($dataTile["link_type"], ["new", "new_pagegroup"])) {
                        $dataTile["page_id"] = Page::create([
                            "title" => $dataTile["new_page_title"],
                            "slug" => Str::slug($dataTile["new_page_title"]),
                            "is_pagegroup" => $dataTile["link_type"] == "new_pagegroup"
                        ])->id;
                    }
                }

                unset($dataTile["link_type"], $dataTile["orphan_page_id"], $dataTile["new_page_title"]);
                
                return $dataTile;
            })
            ->toArray();

        $this->save($menu, $data);

        return $menu->id;
    }

    function delete($id): void
    {
        Tileblock::findOrFail($id)->delete();
    }

    protected function createItemsListField(): SharpFormListField
    {
        $listField = SharpFormListField::make("tiles")
            ->setLabel("Liens")
            ->setAddable()
            ->setAddText("Ajouter un lien")
            ->setRemovable()
            ->setSortable()
            ->setOrderAttribute("order");

        $listField
            ->addItemField(
                SharpFormTextField::make("title")
                    ->setLabel("Titre")
            )
            ->addItemField(
                SharpFormSelectField::make("link_type", [
                    "free" => "Lien libre",
                    "page" => "Page",
                    "orphan" => "Page orpheline",
                    "new" => "Nouvelle page",
                    "new_pagegroup" => "Nouveau groupe",
                ])
                    ->setDisplayAsDropdown()
                    ->setLabel("Lien")
            )
            ->addItemField(
                SharpFormAutocompleteField::make("page_id", "local")
                    ->setLabel("Page")
                    ->setLocalValues(Page::domain(gum_sharp_current_domain())->notHome()->notOrphan()->notSubpage()->orderBy("title")->get())
                    ->setLocalSearchKeys(["title", "slug"])
                    ->setResultItemInlineTemplate("{{title}} <span class='badge text-white rounded bg-primary' style='font-size:.7em'>{{admin_label}}</span>")
                    ->setListItemInlineTemplate("{{title}} <div><span class='badge text-white rounded bg-primary' style='font-size:.7em'>{{admin_label}}</span> <span class='text-muted'><small>{{slug}}</small></span></div>")
                    ->addConditionalDisplay("link_type", "page")
            )
            ->addItemField(
                SharpFormAutocompleteField::make("orphan_page_id", "local")
                    ->setLabel("Page")
                    ->setLocalValues(Page::domain(gum_sharp_current_domain())->notHome()->orphan()->notSubpage()->orderBy("title")->get())
                    ->setLocalSearchKeys(["title", "slug"])
                    ->setResultItemInlineTemplate("{{title}} <span class='badge text-white rounded bg-primary' style='font-size:.7em'>{{admin_label}}</span>")
                    ->setListItemInlineTemplate("{{title}} <div><span class='badge text-white rounded bg-primary' style='font-size:.7em'>{{admin_label}}</span> <span class='text-muted'><small>{{slug}}</small></span></div>")
                    ->addConditionalDisplay("link_type", "orphan")
            )
            ->addItemField(
                SharpFormTextField::make("new_page_title")
                    ->addConditionalDisplay("link_type", ["new", "new_pagegroup"])
                    ->setLabel("Titre de la page")
            )
            ->addItemField(
                SharpFormTextField::make("free_link_url")
                    ->addConditionalDisplay("link_type", "free")
                    ->setLabel("Lien")
            );

        return $listField;
    }
}