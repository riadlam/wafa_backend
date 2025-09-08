<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advertisement_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('advertisement_id');
            $table->string('action', 32); // created, approved, rejected, dispatched, delivered, failed
            $table->unsignedBigInteger('performed_by')->nullable(); // null for system jobs
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['advertisement_id']);
            $table->index(['performed_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisement_audits');
    }
};


