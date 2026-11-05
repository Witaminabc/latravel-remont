<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('user');
            }
            if (! Schema::hasColumn('users', 'is_online')) {
                $table->boolean('is_online')->default(false);
            }
            if (! Schema::hasColumn('users', 'last_seen')) {
                $table->timestamp('last_seen')->nullable();
            }
            if (! Schema::hasColumn('users', 'is_blocked')) {
                $table->boolean('is_blocked')->default(false);
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['role', 'is_online', 'last_seen', 'is_blocked'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
