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
    Schema::create('stamps', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_loyalty_card_id')->constrained('user_loyalty_cards')->onDelete('cascade');
        $table->foreignId('added_by')->constrained('users')->onDelete('cascade'); // must be shop_owner
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stamps');
    }
};
