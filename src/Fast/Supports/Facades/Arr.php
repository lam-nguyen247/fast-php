<?php

namespace Fast\Supports\Facades;

use Fast\Supports\Facade;
use Fast\Supports\Arr as SupportArr;

class Arr extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'arr';
    }

    /**
     * Call static function
     * 
     * @param string $method
     * @param array $arguments
     * 
     * @return mixed
     */
    public static function __callStatic(string $method, array $arguments)
    {
        return (new SupportArr)->$method(...$arguments);
    }

    /**
     * Call function
     * 
     * @param string $method
     * @param array $arguments
     * 
     * @return mixed
     */
    public function __call(string $method, array $arguments)
    {
        return (new SupportArr)->$method(...$arguments);
    }
}
