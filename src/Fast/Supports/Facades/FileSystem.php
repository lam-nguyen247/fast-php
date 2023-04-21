<?php

namespace Fast\Supports\Facades;

use Fast\Supports\Facade;

class FileSystem extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'fileSystem';
    }
}
