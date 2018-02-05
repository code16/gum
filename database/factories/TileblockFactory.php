<?php

use Faker\Generator as Faker;

$factory->define(\Code16\Gum\Models\Tileblock::class, function (Faker $faker) {
    return [
        'layout' => "checkerboard",
        'style_key' => $faker->word,
    ];
});
