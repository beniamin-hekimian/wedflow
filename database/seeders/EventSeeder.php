<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Seed the event catalog.
     */
    public function run(): void
    {
        Event::query()->delete();

        $events = [
            'Welcome Drinks',
            'Wedding Ceremony',
            'Family Photos',
            'Cocktail Hour',
            'Wedding Reception',
            'Toasts & Speeches',
            'Cake Cutting',
            'First Dance',
            'Dance Party',
            'Sparkler Send-Off',
        ];

        foreach ($events as $index => $name) {
            Event::create([
                'name' => $name,
                'sort_order' => $index,
            ]);
        }
    }
}