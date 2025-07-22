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
        Schema::table('redemption_statistics', function (Blueprint $table) {
            $table->boolean('is_payed')->default(0)->after('loyalty_card_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('redemption_statistics', function (Blueprint $table) {
            $table->dropColumn('is_payed');
        });
    }
};
