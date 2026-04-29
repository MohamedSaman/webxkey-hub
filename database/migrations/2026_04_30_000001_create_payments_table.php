<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->integer('year');
            $table->integer('month'); // 1–12
            $table->integer('amount')->default(2000);
            $table->enum('status', ['paid', 'due', 'free'])->default('due');
            $table->timestamp('paid_at')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['application_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
