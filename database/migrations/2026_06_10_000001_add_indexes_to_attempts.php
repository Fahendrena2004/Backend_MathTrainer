<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToAttempts extends Migration
{
    public function up()
    {
        if (Schema::hasTable('attempts')) {
            Schema::table('attempts', function (Blueprint $table) {
                if (!Schema::hasColumn('attempts', 'user_id')) return;
                $table->index('user_id');
                if (Schema::hasColumn('attempts', 'exercise_id')) {
                    $table->index('exercise_id');
                }
                if (Schema::hasColumn('attempts', 'created_at')) {
                    $table->index('created_at');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('attempts')) {
            Schema::table('attempts', function (Blueprint $table) {
                // safe-drop indexes by name if exist
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = $sm->listTableIndexes('attempts');
                if (array_key_exists('attempts_user_id_index', $indexes)) {
                    $table->dropIndex('attempts_user_id_index');
                }
                if (array_key_exists('attempts_exercise_id_index', $indexes)) {
                    $table->dropIndex('attempts_exercise_id_index');
                }
                if (array_key_exists('attempts_created_at_index', $indexes)) {
                    $table->dropIndex('attempts_created_at_index');
                }
            });
        }
    }
}
