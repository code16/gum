<?php

use Faker\Generator as Faker;

$factory->define(\Code16\Gum\Models\Page::class, function (Faker $faker) {
    return [
        'slug' => $faker->unique()->slug(2),
        'title' => $faker->sentence,
        'body_text' => $faker->paragraphs(6, true),
        'is_standalone' => false,
        'has_news' => false,
    ];
});
