<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');
            $table->unsignedBigInteger('owner_user_id');
            $table->string('title', 120);
            $table->string('description', 500);
            $table->enum('status', ['pending', 'approved', 'rejected', 'sent', 'failed'])->default('pending');
            $table->string('rejection_reason', 255)->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->integer('target_count')->default(0);
            $table->integer('delivered_count')->default(0);
            $table->timestamps();

            $table->index(['owner_user_id', 'status']);
            $table->index(['shop_id', 'status']);
            $table->index(['sent_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};


