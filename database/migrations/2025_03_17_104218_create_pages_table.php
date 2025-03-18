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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('page_name');
            $table->text('description');
            $table->string('image', 255);
            $table->string('banner', 255);
            $table->unsignedBigInteger('owner_id');
            $table->unsignedBigInteger('categry_id');
            $table->foreign('owner_id')->references('id')->on('persons')->onDelete('cascade');
            $table->foreign('categry_id')->references('id')->on('categories')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
