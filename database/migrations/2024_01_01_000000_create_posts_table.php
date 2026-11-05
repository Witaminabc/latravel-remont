<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title')->comment('Заголовок');
            $table->string('slug')->unique()->comment('URL ссылка');
            $table->text('content')->nullable()->comment('Контент');
            $table->string('excerpt')->nullable()->comment('Краткое описание');
            $table->string('type')->default('page')->index()->comment('Тип: page, post, product, etc');
            $table->string('status')->default('draft')->index()->comment('draft, published, private, trash');
            $table->unsignedBigInteger('author_id')->comment('ID автора');
            $table->string('featured_image')->nullable()->comment('Изображение');
            $table->json('meta')->nullable()->comment('Meta поля JSON');
            $table->integer('order')->default(0)->comment('Порядок сортировки');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('Для иерархии');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('author_id')->references('id')->on('users');
            $table->foreign('parent_id')->references('id')->on('posts');
            $table->index(['type', 'status', 'published_at']);
        });

        // Таблица для таксономии (категории, теги как в WP)
        Schema::create('taxonomies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->index(); // category, tag, post_format
            $table->text('description')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamps();
        });

        // Связь постов с таксономиями
        Schema::create('post_taxonomy', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('post_id');
            $table->unsignedBigInteger('taxonomy_id');
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
            $table->foreign('taxonomy_id')->references('id')->on('taxonomies')->onDelete('cascade');
            $table->unique(['post_id', 'taxonomy_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('post_taxonomy');
        Schema::dropIfExists('taxonomies');
        Schema::dropIfExists('posts');
    }
};
