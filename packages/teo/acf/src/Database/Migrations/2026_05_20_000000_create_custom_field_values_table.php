<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $table = config('acf.table', 'custom_field_values');

        if (Schema::hasTable($table)) {
            return;
        }

        Schema::create($table, function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('model_type')->index();
            $blueprint->unsignedBigInteger('model_id')->index();
            $blueprint->string('field_name')->index();
            $blueprint->string('field_key')->nullable();
            $blueprint->json('value')->nullable();
            $blueprint->timestamps();

            $blueprint->unique(['model_type', 'model_id', 'field_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('acf.table', 'custom_field_values'));
    }
};
