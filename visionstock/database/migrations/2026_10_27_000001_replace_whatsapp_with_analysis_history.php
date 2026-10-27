<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_number', 'whatsapp_connected_at']);
        });

        Schema::create('analysis_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->dateTime('analyzed_at');
            $table->unsignedSmallInteger('period_days');
            $table->unsignedSmallInteger('coverage_days');
            $table->timestamps();
            $table->index(['user_id', 'analyzed_at']);
        });

        Schema::create('analysis_history_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analysis_history_id')
                ->constrained('analysis_histories')
                ->cascadeOnDelete();
            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();
            $table->string('product_name');
            $table->unsignedInteger('stock');
            $table->unsignedInteger('recommended_stock');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analysis_history_items');
        Schema::dropIfExists('analysis_histories');

        Schema::table('users', function (Blueprint $table) {
            $table->string('whatsapp_number', 16)->nullable();
            $table->timestamp('whatsapp_connected_at')->nullable();
        });
    }
};
