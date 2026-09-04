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
                'name' => 'Classic',
                'file_path' => 'melodies-assets/classic.mp3',
            ]
        );
    }
}
