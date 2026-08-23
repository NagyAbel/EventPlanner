<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        Event::factory()
            ->count(40)
            ->withRandomAttendees(0, 20)
            ->create();
    }
}