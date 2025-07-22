<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('shops', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

        // 🔥 FIX HERE: make category_id nullable before foreign key is defined
        $table->unsignedBigInteger('category_id')->nullable();

        $table->string('name');
        $table->json('contact_info')->nullable();
        $table->json('location')->nullable();
        $table->timestamps();

        // Add the foreign key after declaring the column
        $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
