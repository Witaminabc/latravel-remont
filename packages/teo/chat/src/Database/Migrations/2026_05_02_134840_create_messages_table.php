<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            // legacy: автор (старая схема), nullable — см. миграцию make_user_id_nullable
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('from_user')->nullable();
            $table->unsignedBigInteger('to_user')->nullable();
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index('from_user');
            $table->index('to_user');
            $table->index(['from_user', 'to_user']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};