<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->date('date');
            $table->string('subject');
            $table->text('intro_text')->nullable();
            $table->enum('template_type', ['system', 'website'])->default('system');
            $table->boolean('hosting_enabled')->default(false);
            $table->decimal('hosting_price', 10, 2)->default(400);
            $table->integer('hosting_months')->default(12);
            $table->integer('payment_advance_pct')->default(30);
            $table->integer('payment_middle_pct')->default(50);
            $table->integer('payment_final_pct')->default(20);
            $table->decimal('monthly_support_fee', 10, 2)->default(2000);
            $table->decimal('additional_feature_rate', 10, 2)->default(4000);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total_system_cost', 10, 2)->default(0);
            $table->enum('status', ['draft', 'sent', 'approved'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
