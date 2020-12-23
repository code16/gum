<?php

namespace Code16\Gum\Sharp\Utils;

use Illuminate\Contracts\Validation\Rule;

class FreeLinkRule implements Rule
{
    public function passes($attribute, $value)
    {
        return !trim($value)
            || preg_match('/^(\/([\&\=\#\?\da-zA-Z_\.-]+))*$/', $value)
            || preg_match('/^(https?:\/\/)([\da-zA-Z_\.-]+)\.([a-z\.]{2,6})(\/[\da-zA-Z_\.-]*)*\/?([\&\=\#\?\da-zA-Z_\.-]*)*\/?$/', $value);
    }

    public function message()
    {
        return 'Doit être une adresse complète (http://...) ou relative au site (débutant pas un /).';
    }
}
