<?php

namespace Code16\Gum\Sharp\Utils;

use Illuminate\Contracts\Validation\Rule;

class SlugRule implements Rule
{
    public function passes($attribute, $value)
    {
        return !trim($value) || preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value);
    }

    public function message()
    {
        return 'Ne doit contenir que des lettres, des tirets et des chiffres.';
    }
}
