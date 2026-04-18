<?php

use App\Models\VendorCartItem;
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
        Schema::table('vendor_cart_items', function (Blueprint $table) {
            $table->string('price_type', 30)->default('per_day')->after('quantity');
        });

        VendorCartItem::query()->with('item')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                if ($row->item) {
                    $row->update(['price_type' => $row->item->price_type]);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vendor_cart_items', function (Blueprint $table) {
            $table->dropColumn('price_type');
        });
    }
};
