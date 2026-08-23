<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition(): array
    {
        return [
            'user_id' => User::query()->inRandomOrder()->value('id'),
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'type' => fake()->randomElement([
                'concert',
                'conference',
                'party',
                'sports',
                'workshop',
            ]),
            'date' => fake()->dateTimeBetween('now', '+6 months'),
            'city' => fake()->city(),
            'location' => fake()->address(),
            'cover_image' => $this->copyRandomCoverImage(),
            'public' => fake()->boolean(),
        ];
    }

    public function withRandomAttendees(int $min = 0, int $max = 20): static
    {
        return $this->afterCreating(function (Event $event) use ($min, $max) {
            $count = fake()->numberBetween($min, $max);

            $users = User::query()
                ->inRandomOrder()
                ->limit($count)
                ->pluck('id');

            $event->attendees()->attach($users);

            $event->attendee_count = $users->count();
            $event->save();
        });
    }

    private function copyRandomCoverImage(): string
    {
        static $covers;

        $sourceDirectory = storage_path('app/public/test_event_covers');
        $destinationDirectory = storage_path('app/public/events');

        if (!File::isDirectory($sourceDirectory)) {
            throw new \RuntimeException(
                "Source directory does not exist: {$sourceDirectory}"
            );
        }

        $covers ??= collect(
            File::files($sourceDirectory)
        )->filter(
            fn ($file) => strtolower($file->getExtension()) === 'webp'
        )->values();

        if ($covers->isEmpty()) {
            throw new \RuntimeException(
                "No WebP images found in {$sourceDirectory}"
            );
        }

        File::ensureDirectoryExists($destinationDirectory);

        $source = fake()->randomElement($covers);

        $filename = Str::uuid() . '.webp';

        File::copy(
            $source->getPathname(),
            $destinationDirectory . DIRECTORY_SEPARATOR . $filename
        );

        return 'events/' . $filename;
    }
}