<?php

namespace Code16\Gum\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tileblock extends Model
{
    protected $guarded = [];

    protected $dates = ["created_at", "updated_at", "published_at", "unpublished_at"];

    protected $touches = ['tiles'];

    public function tiles(): HasMany
    {
        return $this->hasMany(Tile::class)
            ->orderBy("order");
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
