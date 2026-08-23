<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); 
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('event_type_id')
                ->constrained()
                ->restrictOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->dateTime('date');
            $table->string('city');
            $table->string('location');
            $table->boolean('public');            
            $table->string('cover_image')->nullable();
            $table->unsignedInteger('attendee_count')->default(0);            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
        Schema::dropIfExists('event_types');
    }
};