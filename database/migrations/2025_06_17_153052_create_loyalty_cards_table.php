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
    Schema::create('loyalty_cards', function (Blueprint $table) {
        $table->id();
        $table->foreignId('shop_id')->constrained('shops')->onDelete('cascade');
        $table->string('logo_url');
        $table->string('color')->default('#FF9900');
        $table->integer('total_stamps')->default(10);
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_cards');
    }
};
