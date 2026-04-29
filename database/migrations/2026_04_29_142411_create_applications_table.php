<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('domain')->unique();
            $table->string('folder_path');
            $table->string('git_repo')->nullable();
            $table->string('branch')->default('main');
            $table->string('db_name')->nullable();
            $table->string('nginx_config')->nullable();
            $table->enum('status', ['live', 'stopped', 'deploying', 'error'])->default('live');
            $table->string('php_version')->default('8.3');
            $table->timestamp('last_pull_at')->nullable();
            $table->timestamp('last_deployed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
