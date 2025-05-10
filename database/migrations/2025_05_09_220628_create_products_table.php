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
        if (Schema::hasTable('products'))
        {
            dump('products table exists, continuing ...');
            return;
        }

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            
            $table->string('unique_key')->unique();
            $table->string('product_title')->nullable();
            $table->text('product_description')->nullable();
            $table->string('style')->nullable();
            // $table->string('available_sizes')->nullable();
            // $table->string('brand_logo_image')->nullable();
            // $table->string('thumbnail_image')->nullable();
            // $table->string('color_swatch_image')->nullable();
            // $table->string('product_image')->nullable();
            // $table->string('spec_sheet')->nullable();
            // $table->string('price_text')->nullable();
            // $table->string('suggested_price')->nullable();
            // $table->string('category_name')->nullable();
            // $table->string('subcategory_name')->nullable();
            $table->string('color_name')->nullable();
            // $table->string('color_square_image')->nullable();
            // $table->string('color_product_image_thumbnail')->nullable();
            $table->string('size')->nullable();
            // $table->string('qty')->nullable();
            // $table->string('piece_weight')->nullable();
            $table->string('piece_price')->nullable();
            // $table->string('dozens_price')->nullable();
            // $table->string('case_price')->nullable();
            // $table->string('price_group')->nullable();
            // $table->string('case_size')->nullable();
            // $table->string('inventory_key')->nullable();
            // $table->string('size_index')->nullable();
            $table->string('sanmar_mainframe_color')->nullable();
            // $table->string('mill')->nullable();
            // $table->string('product_status')->nullable();
            // $table->string('companion_styles')->nullable();
            // $table->string('msrp')->nullable();
            // $table->string('map_pricing')->nullable();
            // $table->string('front_model_image_url')->nullable();
            // $table->string('back_model_image')->nullable();
            // $table->string('front_flat_image')->nullable();
            // $table->string('back_flat_image')->nullable();
            // $table->string('product_measurements')->nullable();
            // $table->string('pms_color')->nullable();
            // $table->string('gtin')->nullable();


            /*
            $table->string('UNIQUE_KEY')->nullable();
            $table->string('PRODUCT_TITLE')->nullable();
            $table->text('PRODUCT_DESCRIPTION')->nullable();
            $table->string('STYLE')->nullable();
            // $table->string('AVAILABLE_SIZES')->nullable();
            // $table->string('BRAND_LOGO_IMAGE')->nullable();
            // $table->string('THUMBNAIL_IMAGE')->nullable();
            // $table->string('COLOR_SWATCH_IMAGE')->nullable();
            // $table->string('PRODUCT_IMAGE')->nullable();
            // $table->string('SPEC_SHEET')->nullable();
            // $table->string('PRICE_TEXT')->nullable();
            // $table->string('SUGGESTED_PRICE')->nullable();
            // $table->string('CATEGORY_NAME')->nullable();
            // $table->string('SUBCATEGORY_NAME')->nullable();
            $table->string('COLOR_NAME')->nullable();
            // $table->string('COLOR_SQUARE_IMAGE')->nullable();
            // $table->string('COLOR_PRODUCT_IMAGE_THUMBNAIL')->nullable();
            $table->string('SIZE')->nullable();
            // $table->string('QTY')->nullable();
            // $table->string('PIECE_WEIGHT')->nullable();
            $table->string('PIECE_PRICE')->nullable();
            // $table->string('DOZENS_PRICE')->nullable();
            // $table->string('CASE_PRICE')->nullable();
            // $table->string('PRICE_GROUP')->nullable();
            // $table->string('CASE_SIZE')->nullable();
            // $table->string('INVENTORY_KEY')->nullable();
            // $table->string('SIZE_INDEX')->nullable();
            $table->string('SANMAR_MAINFRAME_COLOR')->nullable();
            // $table->string('MILL')->nullable();
            // $table->string('PRODUCT_STATUS')->nullable();
            // $table->string('COMPANION_STYLES')->nullable();
            // $table->string('MSRP')->nullable();
            // $table->string('MAP_PRICING')->nullable();
            // $table->string('FRONT_MODEL_IMAGE_URL')->nullable();
            // $table->string('BACK_MODEL_IMAGE')->nullable();
            // $table->string('FRONT_FLAT_IMAGE')->nullable();
            // $table->string('BACK_FLAT_IMAGE')->nullable();
            // $table->string('PRODUCT_MEASUREMENTS')->nullable();
            // $table->string('PMS_COLOR')->nullable();
            // $table->string('GTIN')->nullable();
            */

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
