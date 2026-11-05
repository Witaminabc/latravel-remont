<?php

namespace Teo\Chat\Commands;

use Illuminate\Console\Command;

class InstallChatCommand extends Command
{
    protected $signature = 'chat:install
        {--force : Перезаписать уже опубликованные файлы}
        {--only= : Публиковать только указанный ресурс: config|migrations|views|js|styles}';

    protected $description = 'Публикация ресурсов пакета teo/chat (config, миграции, views, JS)';

    public function handle(): int
    {
        $only = $this->option('only');
        $force = (bool) $this->option('force');

        $map = [
            'config' => 'chat-config',
            'migrations' => 'chat-migrations',
            'views' => 'chat-views',
            'js' => 'chat-js',
            'styles' => 'chat-styles',
        ];

        foreach ($map as $key => $tag) {
            if ($only !== null && $only !== '' && $only !== $key) {
                continue;
            }

            $this->call('vendor:publish', [
                '--tag' => $tag,
                '--force' => $force,
            ]);
        }

        $this->newLine();
        $this->components->info('teo/chat: ресурсы опубликованы.');

        return self::SUCCESS;
    }
}