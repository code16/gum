<?php

use Faker\Generator as Faker;

$factory->define(\Code16\Gum\Models\Sidepanel::class, function (Faker $faker) {
    return [
        'layout' => "visual",
    ];
});
