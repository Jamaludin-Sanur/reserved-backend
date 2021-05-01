<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\News;

class NewsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Let's truncate our existing records to start from scratch.
        News::truncate();

        $faker = \Faker\Factory::create();

        // And now, let's create a few articles in our database:
        for ($i = 0; $i < 50; $i++) {
            News::create([
                'user_id' => rand(1, 50),
                'title' => $faker->sentence,
                'description' => $faker->paragraph,
                'urlImage' => $faker->imageUrl($width = 640, $height = 480)
            ]);
        }
    }
}
