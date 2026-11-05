<?php

namespace Teo\Acf\Commands;

use Illuminate\Console\Command;

class AcfMakeGroupCommand extends Command
{
    protected $signature = 'acf:group {key : Field group key (e.g. group_home)}';

    protected $description = 'Print a boilerplate for a new ACF field group';

    public function handle(): int
    {
        $key = $this->argument('key');

        $this->line("Acf::addGroup([");
        $this->line("    'key' => '{$key}',");
        $this->line("    'title' => 'Новая группа',");
        $this->line("    'location' => [['type' => 'post_type', 'value' => 'page']],");
        $this->line("    'fields' => [");
        $this->line("        ['key' => 'field_title', 'name' => 'title', 'label' => 'Заголовок', 'type' => 'text'],");
        $this->line("    ],");
        $this->line("]);");

        return self::SUCCESS;
    }
}
