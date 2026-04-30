<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::table('applications', function (Blueprint $table) {
            $table->foreignId('billing_plan_id')->nullable()->after('status')->constrained('billing_plans')->nullOnDelete();
        });

        // Seed default plans
        DB::table('billing_plans')->insert([
            ['name' => 'Cloud Hosting', 'price' => 2000, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Web Hosting',   'price' => 500,  'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Free / Trial',  'price' => 0,    'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('billing_plan_id');
        });
        Schema::dropIfExists('billing_plans');
    }
};
