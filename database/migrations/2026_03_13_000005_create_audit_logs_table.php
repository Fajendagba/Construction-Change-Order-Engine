<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('change_order_id');
            $table->foreign('change_order_id')->references('id')->on('change_orders')->cascadeOnDelete();
            $table->ulid('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('action', 50);
            $table->string('from_state', 50)->nullable();
            $table->string('to_state', 50);
            $table->jsonb('metadata')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('change_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
