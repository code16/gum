<?php

use Faker\Generator as Faker;

$factory->define(\Code16\Gum\Models\Page::class, function (Faker $faker) {
    return [
        'slug' => $faker->unique()->slug(2),
        'title' => $faker->sentence,
        'body_text' => $faker->paragraphs(6, true),
        'heading_text' => $faker->paragraphs(1, true),
        'style_key' => $faker->word,
        'has_news' => false,
    ];
});
