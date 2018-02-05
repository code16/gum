<?php

namespace Code16\Gum\Tests\Feature\Utils;

use Illuminate\Foundation\Auth\User as Authenticatable;

class UserModel extends Authenticatable
{
    protected $guarded = [];
    protected $table = "users";
}
