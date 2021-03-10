<?php

namespace Code16\Gum\Models;

use Code16\Gum\Models\Utils\WithMenuTitle;
use Code16\Gum\Models\Utils\WithUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Laravel\Scout\Searchable;

class Page extends Model
{
    use WithUuid, WithMenuTitle, Searchable;

    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    public function visual(): MorphOne
    {
        return $this->morphOne(Media::class, "model")
            ->where("model_key", "visual");
    }

    public function pagegroup(): BelongsTo
    {
        return $this->belongsTo(Pagegroup::class);
    }

    public function urls(): MorphMany
    {
        return $this->morphMany(ContentUrl::class, "content");
    }

    public function sidepanels(): MorphMany
    {
        return $this->morphMany(Sidepanel::class, "container")
            ->orderBy("order");
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, "taggable");
    }

    public function getDefaultAttributesFor(string $attribute): array
    {
        return in_array($attribute, ["visual"])
            ? ["model_key" => $attribute]
            : [];
    }

    public function searchableAs(): string
    {
        return env('SCOUT_PREFIX') . 'content';
    }

    public function toSearchableArray(): array
    {
        return [
            "id" => $this->id,
            "type" => Page::class,
            "domain" => $this->urls()->visible()->published()->first()->domain,
            "updated_at" => $this->updated_at->timestamp,
            "title" => strip_tags($this->title),
            "text" => strip_tags($this->heading_text . " " . $this->body_text),
        ];
    }

    public function shouldBeSearchable(): bool
    {
        if(!config("gum.scout_enabled")){
            return false;
        }
        
        return $this->urls()->visible()->published()->count() > 0;
    }
}
