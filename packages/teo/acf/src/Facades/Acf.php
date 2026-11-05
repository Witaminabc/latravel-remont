<?php

namespace Teo\Acf\Facades;

use Illuminate\Support\Facades\Facade;
use Teo\Acf\Support\FieldGroupRegistry;

/**
 * @method static void addGroup(array $group)
 * @method static array groups()
 * @method static array|null group(string $key)
 * @method static array forLocation(?string $postType, ?string $slug = null, ?string $template = null)
 *
 * @see \Teo\Acf\Support\FieldGroupRegistry
 */
class Acf extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return FieldGroupRegistry::class;
    }
}
