<?php

use Faker\Generator as Faker;

$factory->define(\Code16\Gum\Models\News::class, function (Faker $faker) {
    return [
        'title' => $faker->sentence,
        'surtitle' => $faker->sentence,
        'heading_text' => $faker->text,
        'body_text' => $faker->text,
        'visibility' => 'ONLINE',
        'published_at' => $faker->dateTimeBetween('-1 year', 'now')
    ];
});
