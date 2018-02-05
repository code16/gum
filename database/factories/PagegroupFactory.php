<?php

use Faker\Generator as Faker;

$factory->define(\Code16\Gum\Models\Pagegroup::class, function (Faker $faker) {
    return [
        'slug' => $faker->unique()->slug(2),
        'title' => $faker->sentence,
    ];
});
