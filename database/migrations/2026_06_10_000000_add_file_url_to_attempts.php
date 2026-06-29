<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attempts', function (Blueprint $table) {
            if (! Schema::hasColumn('attempts', 'file_url')) {
                $table->string('file_url')->nullable()->after('time_spent');
            }
        });
    }

    public function down(): void
    {
        Schema::table('attempts', function (Blueprint $table) {
            if (Schema::hasColumn('attempts', 'file_url')) {
                $table->dropColumn('file_url');
            }
        });
    }
};
