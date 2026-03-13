<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_line_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
            $table->string('cost_code', 50);
            $table->string('description', 255);
            $table->decimal('original_amount', 15, 2)->default(0);
            $table->decimal('approved_changes', 15, 2)->default(0);
            $table->decimal('current_amount', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['project_id', 'cost_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_line_items');
    }
};
