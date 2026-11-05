<?php

namespace Teo\Acf\Support;

use Teo\Acf\Fields\BooleanField;
use Teo\Acf\Fields\FieldType;
use Teo\Acf\Fields\ImageField;
use Teo\Acf\Fields\NumberField;
use Teo\Acf\Fields\RepeaterField;
use Teo\Acf\Fields\SelectField;
use Teo\Acf\Fields\TextField;
use Teo\Acf\Fields\TextareaField;
use Teo\Acf\Fields\WysiwygField;

class FieldTypeRegistry
{
    /** @var array<string, class-string<FieldType>> */
    protected array $types = [];

    public function __construct()
    {
        $this->registerDefaults();
    }

    protected function registerDefaults(): void
    {
        foreach ([
            TextField::class,
            TextareaField::class,
            WysiwygField::class,
            NumberField::class,
            SelectField::class,
            BooleanField::class,
            ImageField::class,
        ] as $class) {
            /** @var FieldType $instance */
            $instance = new $class();
            $this->types[$instance->name()] = $class;
        }

        // Репитер создаётся с внедрением реестра (для санитайза подполей)
        $this->types['repeater'] = RepeaterField::class;
    }

    public function extend(string $name, string $class): void
    {
        $this->types[$name] = $class;
    }

    public function has(string $name): bool
    {
        return isset($this->types[$name]);
    }

    /** @return array<int, string> */
    public function names(): array
    {
        return array_keys($this->types);
    }

    public function make(string $name): FieldType
    {
        $class = $this->types[$name] ?? TextField::class;

        if ($class === RepeaterField::class) {
            return new RepeaterField($this);
        }

        return new $class();
    }
}
