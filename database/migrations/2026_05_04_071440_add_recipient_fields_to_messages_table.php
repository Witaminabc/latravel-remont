<?php
// database/migrations/xxxx_add_recipient_fields_to_messages_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRecipientFieldsToMessagesTable extends Migration
{
    public function up()
    {
        Schema::table('messages', function (Blueprint $table) {
            // Добавляем колонку для получателя
            if (!Schema::hasColumn('messages', 'to_user')) {
                $table->unsignedBigInteger('to_user')->nullable()->after('user_id');
                $table->foreign('to_user')->references('id')->on('users')->onDelete('cascade');
            }

            // Добавляем колонку для статуса прочтения
            if (!Schema::hasColumn('messages', 'is_read')) {
                $table->boolean('is_read')->default(false)->after('message');
            }

            // Добавляем колонку для отправителя (если используете from_user)
            if (!Schema::hasColumn('messages', 'from_user')) {
                $table->unsignedBigInteger('from_user')->nullable()->after('id');
                $table->foreign('from_user')->references('id')->on('users')->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['to_user']);
            $table->dropColumn('to_user');

            $table->dropColumn('is_read');

            $table->dropForeign(['from_user']);
            $table->dropColumn('from_user');
        });
    }
}
