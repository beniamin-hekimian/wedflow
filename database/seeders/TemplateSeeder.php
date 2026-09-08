<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Template;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Template::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'Classic',
                'slug' => 'classic',
                'description' => 'An elegant classic wedding invitation template',
                'thumbnail_path' => 'templates-assets/classic/thumbnail.jpeg',
                'intro_video_path' => 'templates-assets/classic/intro.mp4',
            ]
        );

        Template::updateOrCreate(
            ['id' => 2],
            [
                'name' => 'Royal',
                'slug' => 'royal',
                'description' => 'A luxurious royal wedding invitation template',
                'thumbnail_path' => 'templates-assets/royal/thumbnail.jpeg',
                'intro_video_path' => 'templates-assets/royal/intro.mp4',
            ]
        );
    }
}
