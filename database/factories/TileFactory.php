<?php

use Faker\Generator as Faker;

$factory->define(\Code16\Gum\Models\Tile::class, function (Faker $faker) {
    return [
        'title' => $faker->sentence,
        'surtitle' => $faker->sentence,
        'body_text' => $faker->text,
        'order' => 1,
        'visibility' => 'ONLINE',
    ];
});
