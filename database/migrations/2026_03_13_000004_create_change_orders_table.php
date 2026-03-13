<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('change_orders', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('project_id');
            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
            $table->ulid('submitted_by');
            $table->foreign('submitted_by')->references('id')->on('users');
            $table->ulid('reviewed_by')->nullable();
            $table->foreign('reviewed_by')->references('id')->on('users');
            $table->integer('number');
            $table->string('title', 255);
            $table->text('description');
            $table->string('reason', 255);
            $table->string('cost_code', 50);
            $table->decimal('labor_cost', 12, 2)->default(0);
            $table->decimal('material_cost', 12, 2)->default(0);
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->string('state', 50)->default('draft');
            $table->timestamp('state_changed_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index('project_id');
            $table->index('state');
            $table->index('submitted_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('change_orders');
    }
};
