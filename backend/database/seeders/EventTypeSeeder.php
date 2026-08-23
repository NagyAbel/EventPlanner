<?php

namespace Database\Seeders;

use App\Models\EventType;
use Illuminate\Database\Seeder;

class EventTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            'Conference',
            'Meetup',
            'Workshop',
            'Seminar',
            'Networking',
            'Party',
            'Concert',
            'Exhibition',
            'Webinar',
            'Other',
        ];

        foreach ($types as $type) {
            EventType::firstOrCreate(['name' => $type]);
        }
    }
}