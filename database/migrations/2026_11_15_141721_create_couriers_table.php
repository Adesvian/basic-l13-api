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
        if (!Schema::hasTable('couriers')) {
            Schema::create('couriers', function (Blueprint $table) {
                $table->id();
                $table->string('first_name', 150);
                $table->string('last_name', 150);
                $table->string('phone', 20)->nullable();
                $table->string('email', 150)->nullable();
                $table->text('address')->nullable();
                $table->foreignId('level_id')
                        ->default(1)
                        ->nullable()
                        ->constrained('levels')
                        ->restrictOnDelete();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $table->index('level_id');
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('couriers');
    }
};
