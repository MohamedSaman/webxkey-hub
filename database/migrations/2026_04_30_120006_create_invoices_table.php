<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('proposal_id')->nullable()->constrained('proposals')->nullOnDelete();
            $table->string('invoice_number')->unique();
            $table->date('date');
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('balance_due', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->enum('status', ['draft', 'sent', 'partial', 'paid'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
