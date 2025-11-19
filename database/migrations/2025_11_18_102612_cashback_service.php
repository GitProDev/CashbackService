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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->integer('total_pool');
            $table->integer('per_day_pool');
            $table->decimal('reward_amount', 10, 2); //->default(10.00);
            $table->timestamps();
        });

        Schema::create('daily_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();
            $table->date('day');
            $table->integer('remaining');
            $table->unique(['campaign_id', 'day']);
            $table->timestamps();
        });

        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->timestamp('awarded_at');
            $table->timestamps();
        });

        Schema::create('merchant_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('locale')->default('en');
            $table->unique(['user_id', 'locale', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchant_translations');
        Schema::dropIfExists('rewards');
        Schema::dropIfExists('daily_limits');
        Schema::dropIfExists('campaigns');
    }
};
