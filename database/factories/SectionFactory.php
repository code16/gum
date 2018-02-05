<?php

use Faker\Generator as Faker;

$factory->define(\Code16\Gum\Models\Section::class, function (Faker $faker) {
    return [
        'slug' => $faker->unique()->slug(2),
        'title' => $faker->sentence,
        'heading_text' => $faker->paragraphs(3, true),
        'style_key' => $faker->word,
        'has_news' => false,
    ];
});
