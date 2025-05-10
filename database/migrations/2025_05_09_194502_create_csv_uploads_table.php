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
        if (Schema::hasTable('csv_uploads'))
        {
            dump('csv_uploads table exists, continuing ...');
            return;
        }

        Schema::create('csv_uploads', function (Blueprint $table) {
            $table->id();
            $table->string('uploaded_filename')->nullable();
            $table->string('status')->nullable();
            $table->string('file_driver')->nullable();
            $table->string('filepath')->nullable();
            $table->string('file_hash')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('csv_uploads');
    }
};
