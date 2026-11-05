<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = config('acf.groups_table', 'acf_field_groups');

        if (Schema::hasTable($table)) {
            return;
        }

        Schema::create($table, function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('key')->unique();
            $blueprint->string('title');
            $blueprint->json('location')->nullable();
            $blueprint->json('fields')->nullable();
            // active = false означает «группа скрыта» (используется для tombstone-записей
            // о группах, зарегистрированных в коде)
            $blueprint->boolean('active')->default(true);
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('acf.groups_table', 'acf_field_groups'));
    }
};