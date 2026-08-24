<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        Artisan::call('events:download-covers', [
            'count' => 80,
        ], $this->command->getOutput());

        Event::factory()
            ->count(50)
            ->withRandomAttendees(0, 20)
            ->create();
    }
}