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
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('external_id')->nullable()->unique();
            $table->string('status')->default('new');
            $table->string('external_status')->nullable();
            $table->unsignedBigInteger('amocrm_lead_id')->nullable();
            $table->string('guest_name')->nullable();
            $table->string('guest_phone')->nullable();
            $table->string('guest_email')->nullable();
            $table->string('hotel_name')->nullable();
            $table->string('check_in_date')->nullable();
            $table->integer('number_of_guests')->nullable();
            $table->decimal('budget', 12, 2)->nullable();
            $table->text('guest_preferences')->nullable();
            $table->json('ai_analysis')->nullable();
            $table->string('source')->default('webform');
            $table->text('chat_history')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
