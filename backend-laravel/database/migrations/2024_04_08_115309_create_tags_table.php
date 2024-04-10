<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('uuid');
            $table->string('slug')->nullable();
            $table->string('name');
            $table->softDeletes();
            $table->timestamps();
        });

        /* Polymorphic Many to Many */
        Schema::create('taggables', function (Blueprint $table) {
            $table->string('tag_id');
            $table->string('taggable_id');
            $table->string('taggable_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taggables');
        Schema::dropIfExists('tags');
    }
};
