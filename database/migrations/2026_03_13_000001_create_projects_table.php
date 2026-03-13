<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->decimal('original_budget', 15, 2)->default(0);
            $table->decimal('approved_changes_total', 15, 2)->default(0);
            $table->decimal('current_budget', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
