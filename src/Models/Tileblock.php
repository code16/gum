<?php

namespace Code16\Gum\Models;

use Illuminate\Database\Eloquent\Model;

class Tileblock extends Model
{
    protected $guarded = [];

    protected $dates = ["created_at", "updated_at", "published_at", "unpublished_at"];

    public function tiles()
    {
        return $this->hasMany(Tile::class)
            ->orderBy("order");
    }

    public function section()
    {
        return $this->belongsTo(Section::class);
    }
}
