<?php

namespace Code16\Gum\Models;

use Code16\Sharp\Form\Eloquent\Uploads\SharpUploadModel;

class Media extends SharpUploadModel
{
    protected $guarded = [];
    protected $table = "medias";
}
