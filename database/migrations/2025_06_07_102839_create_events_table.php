<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->text('short_description')->nullable();
            $table->datetime('start_date');
            $table->datetime('end_date');
            $table->datetime('registration_start')->nullable();
            $table->datetime('registration_end')->nullable();
            $table->integer('max_spots')->nullable();
            $table->integer('current_registrations')->default(0);
            $table->json('form_fields')->nullable(); // Dynamic form structure
            $table->string('location')->nullable();
            $table->decimal('price', 8, 2)->default(0);
            $table->string('image_url')->nullable();
            $table->enum('status', ['draft', 'published', 'cancelled', 'completed'])->default('draft');
            $table->boolean('requires_approval')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
