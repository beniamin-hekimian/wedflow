<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Melody;

class MelodySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Melody::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'Love Story',
                'file_path' => 'melodies-assets/love-story.mp3',
            ]
        );

        Melody::updateOrCreate(
            ['id' => 2],
            [
                'name' => 'Divenire',
                'file_path' => 'melodies-assets/divenire.mp3',
            ]
        );

        Melody::updateOrCreate(
            ['id' => 3],
            [
                'name' => 'Outro',
                'file_path' => 'melodies-assets/outro.mp3',
            ]
        );
    }
}
