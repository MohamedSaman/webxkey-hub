<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proposal_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('proposal_modules')->cascadeOnDelete();
            $table->string('feature_text');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proposal_features');
    }
};
