<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE proposals MODIFY COLUMN status ENUM('draft','sent','approved','cancelled') NOT NULL DEFAULT 'draft'");
        DB::statement("ALTER TABLE projects MODIFY COLUMN status ENUM('draft','pending','approved','ongoing','completed','cancelled') NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE proposals MODIFY COLUMN status ENUM('draft','sent','approved') NOT NULL DEFAULT 'draft'");
        DB::statement("ALTER TABLE projects MODIFY COLUMN status ENUM('draft','pending','approved','ongoing','completed') NOT NULL DEFAULT 'draft'");
    }
};
