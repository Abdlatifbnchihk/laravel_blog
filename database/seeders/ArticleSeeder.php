<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Str;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table("articles")->insert([
            'user_id' => 1,
            'category_id' => 1,
            'title' => 'Development',
            'slug' => Str::slug('lorem hejdhh hhsjshw kejduejjeje '),
            'content' => fake()->paragraphs(3, true),
            'status' => fake()->randomElement(['draft', 'published']),
            'published_at' => now(),
        ]);
    }
}
